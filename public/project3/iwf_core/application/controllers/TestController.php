<?php

class TestController extends Ia_Controller_Action_Abstract
{
    public function beginAction(){
        //clear cache
        $cache = Zend_Registry::get('cache');
        $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
    
        $sql_file = realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'selenium' . DIRECTORY_SEPARATOR . 'db.sql');
        if(!$sql_file){
            throw new Zend_Exception('Could not locate sql file to begin tests');
        }
        $sql = file_get_contents($sql_file);
        $this->em->getConnection()->exec( $sql );
        Ia_View_Helper_Alert::addAlert('Database has been regenerated.  Ready for testing.','success');
        $this->_helper->redirector->gotoRoute(array('controller'=>'user','action'=>'login'));    
    }

    public function testAction()
    {

    }

    public function testTwoAction()
    {
        
    }


}