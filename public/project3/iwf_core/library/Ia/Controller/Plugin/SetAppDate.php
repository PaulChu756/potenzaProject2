<?php
/*
 * This allows us to set the app date on any url
 */
 
class Ia_Controller_Plugin_SetAppDate extends Zend_Controller_Plugin_Abstract
{
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if($request->getParam('set-app-date')){
            if(strtotime($request->getParam('set-app-date'))){
                $session = new Zend_Session_Namespace('app_session');
                $session->app_date = date('Y-m-d',strtotime($request->getParam('set-app-date')));
                Zend_Registry::set('app_date',$session->app_date);
            }
        }
    }
        
}