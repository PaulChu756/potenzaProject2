<?php

abstract class Ia_Report_Abstract implements Ia_Report_Interface {

    protected $_em = null;

    protected $_dc = null;

    protected $_view = null;

    public function getEntityManager()
    {
        if($this->_dc === null){
            $this->_dc = \Zend_Registry::get('doctrine');
        }
        if($this->_em == null){
            $this->_em = $this->_dc->getEntityManager();
        }
        if(!$this->_em->isOpen()){
            $this->_em = $this->_dc->resetEntityManager();
        }
        return $this->_em;
    }

    public function getView()
    {
        if($this->_view===null){
            $this->_view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');    
        }
        return $this->_view;
    }    

}