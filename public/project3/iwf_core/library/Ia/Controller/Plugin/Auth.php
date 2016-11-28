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

class Ia_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
        {

            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()){
                //faster than looking up by email address
                $logged_in_user = new \Zend_Session_Namespace('logged_in_user');
                if(isset($logged_in_user->id)){
                    $user = \Zend_Registry::get('doctrine')
                    ->getEntityManager()
                    ->getRepository('\Ia\Entity\User')
                    ->find($logged_in_user->id);
                } else {
                    $modelUser = new Ia\Entity\User;
                    $user = $modelUser->getUserByEmail($auth->getIdentity(),true);
                }
                if(!$user){
                    throw new Zend_Exception('Fatal error - no valid record for authenticated user'); 
                }                
                Zend_Registry::set('auth',$user);   
                if($request->getParam('action')!=='password-reset' && $user->pw_reset_required==1){
                    Ia_View_Helper_Alert::addAlert('You must change your password to continue.','error');
                    $redirector = new Zend_Controller_Action_Helper_Redirector;
                    $redirector->gotoRoute(array('module'=>'default','controller'=>'user','action'=>'password-reset'));
                }
            }
                      
        }
        
}