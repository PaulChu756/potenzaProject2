<?php
class Ia_Widget_Abstract {

    public $width = 3;
    
    public function __construct(Zend_View $view){
        $this->view = $view;
        $this->dc = \Zend_Registry::get('doctrine');
        $this->em = $this->dc->getEntityManager();
        $this->init();
    }
    
    public function init(){
    
    }
    
    public function setWidth($width){
        $this->width = $width;
    }
    
    public function getWidth(){
        return $this->width;
    }
    
    public function render() {
        return '<div class="widget span'.$this->getWidth().'"><div class="well"><h3>'.$this->getName().'</h3>'.$this->getOutput().'</div></div>';
    }

}