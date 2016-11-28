<?php

class SettingsController extends Zend_Controller_Action
{
    
    public function init()
    {

    }     

    public function clearCacheAction()
    {
        $cache = Zend_Registry::get('cache');
        $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        Ia_View_Helper_Alert::addAlert('The application cache has been cleared.','success');
        if($_SERVER['HTTP_REFERER']==$_SERVER['REQUEST_URI'])
            $this->_redirect('/');
        else
            $this->_redirect($_SERVER['HTTP_REFERER']);
    }   
    
    public function setAppDateAction()
    {
        if(strtotime($this->getRequest()->getParam('app-date'))){
            $session = new Zend_Session_Namespace('app_session');
            $session->app_date = date('Y-m-d',strtotime($this->getRequest()->getParam('app-date')));
        }
        if($_SERVER['HTTP_REFERER']==$_SERVER['REQUEST_URI'])
            $this->_redirect('/');
        else
            $this->_redirect($_SERVER['HTTP_REFERER']);
    }    
        
}