<?php

class Ia_Form_Element_ProjectManagerSelect extends Zend_Form_Element_Select
{
    
    public function init()
    {
        $this->setMultiOptions($this->getProjectManagers());    
        return parent::init();
    }
    
    public function getProjectManagers()
    {
        $userModel = new Ia\Entity\User;
        return $userModel->getProjectManagerOptions();
            
    }
	
}