<?php

class Ia_Controller_Plugin_Maintenance extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if(MAINTENANCE){
            if(!($request->getParam('controller')=='settings' && $request->getParam('action')=='maintenance')){
                $auth = Zend_Auth::getInstance();
                if ($auth->hasIdentity()){
                    if(!$user = Zend_Registry::get('auth')){
                        throw new Zend_Exception('Fatal error - no valid record for authenticated user (should have been set in Auth plugin)'); 
                    }
                    $role = $user->role;
                    if($role!='administrator'){
                        header("Location: /settings/maintenance"); 
                        exit;
                    } else {
                        Ia_View_Helper_Alert::addAlert('Application is in maintenance mode.','info');
                    }
                }
            }
        }
    }
        
}