<?php
namespace Ia\Template\Engine;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Mustache implements \Ia\Template\EngineInterface
{

    protected $_engine = null;

    public function getEngine()
    {
        if($this->_engine===null){
            $this->_engine = new \Mustache_Engine;
        }
        return $this->_engine;
    }

    public function render($content='',$vars=array()){
        return $this->getEngine()->render($content,$vars);
    }      

}