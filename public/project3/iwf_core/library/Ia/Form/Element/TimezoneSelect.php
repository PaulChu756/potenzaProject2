<?php

class Ia_Form_Element_TimezoneSelect extends Zend_Form_Element_Select
{
    
    public function init()
    {
        $this->setMultiOptions($this->getTimezones());    
        return parent::init();
    }
    
    public function getTimezones()
    {
        return timezone_identifiers_list();           
    }
	
}