<?php
/**
 * Information ArchiTECH, LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@informationarchitech.com so we can send you a copy immediately.
 *
 *
 * @copyright  Copyright (c) 2014 Information ArchiTECH, LLC (http://www.informationarchitech.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Information ArchiTECH <contact@informationarchitech.com>
 */

class Ia_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        //if we are clearing the cache on this request we skip the acl check entirely (so it can actually complete its action)
        if($request->getParam('controller')=='settings' && $request->getParam('action')=='clear-cache')
            return;

        $cache = Zend_Registry::get('cache');
        if(($acl=$cache->load('acl'))===false) {
            $acl = new Ia\Acl;
            $acl->populate();
            $cache->save($acl,'acl');            
        }
        Zend_Registry::set('acl',$acl);
        
        $acl->setRequest($request->getParams());
        $auth = Zend_Auth::getInstance();
        
        /* Set to true for blacklist-style ACL, false for whitelist-style */
        $allowed = false;
        
        if ($auth->hasIdentity()){
            if(!$user = Zend_Registry::get('auth')){
                throw new Zend_Exception('Fatal error - no valid record for authenticated user (should have been set in Auth plugin)'); 
            }
            $role = $user->role;
        } else {
            $role = 'guest';
        }

        $module = ($request->getParam('module')!=='default') ? $request->getParam('module').'_' : '';
        $resource = $module.$request->getParam('controller').'_'.$request->getParam('action');
        
        $validResource = true;
        if(!$acl->has($resource)){
            throw new \Ia_Exception_NotFound('Invalid Resource.');
        }
        
        if($acl->isAllowed($role,$resource)){
            $allowed = true;
        }                   
        
        if(!$allowed){
            if ($validResource && !$auth->hasIdentity()){
                
                // Standard behavior for dealing with APIs
                if($request->getParam('module') === 'api') {
                    // Redirect 401 instead
                    throw new Exception('You are not logged in', 401);
                }
                
                $redirect = new Zend_Session_Namespace('auth_login_redirect');
                if($validResource && !isset($redirect->to))
                    $redirect->to = serialize($request->getParams());
                
                Ia_View_Helper_Alert::addAlert('You must login to continue.','error');
                $redirector = new Zend_Controller_Action_Helper_Redirector;
                if($request->getParam('default')=='register'){
                    $redirector->gotoRoute(array('module'=>'default','controller'=>'user','action'=>'register'));
                } else {
                    $redirector->gotoRoute(array('module'=>'default','controller'=>'user','action'=>'login'));
                }
            } else {
                throw new Ia_Exception_Forbidden('You do not have permission to access the requested resource.');
            }
        }
                    
    }
                
}
