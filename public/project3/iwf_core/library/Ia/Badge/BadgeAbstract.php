<?php
namespace Ia\Badge;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
abstract class BadgeAbstract
{

    protected $_em = null;

    protected $_dc = null;

    protected $_view = null;

    protected $_request = null;

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
            $this->_view = \Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');    
        }
        return $this->_view;
    }    

    public function setRequest($request)
    {
        $this->_request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function getData($active=false)
    {
        try{
            $cache = new \Zend_Session_Namespace('badge_data');
            $cache_id = 'badge_'.md5(get_class($this));
            if($active){
                unset($cache->{$cache_id});
                return $this->getRawData();
            } else {
                if (!isset($cache->{$cache_id})){
                    $data = array('data'=>$this->getRawData(),'set'=>time());
                    $cache->{$cache_id} = $data;
                } else {
                    $data = $cache->{$cache_id};
                    if(time() - $data['set'] > $this->getExpires()){
                        $data = array('data'=>$this->getRawData(),'set'=>time());
                        $cache->{$cache_id} = $data;
                    }
                }
                return $data['data'];
            }
        } catch (\Exception $e){
            return 99999; //indicate there is a problem?
        }
    }

    public function getExpires()
    {
        return 60; //eg 60 seconds
    }

}