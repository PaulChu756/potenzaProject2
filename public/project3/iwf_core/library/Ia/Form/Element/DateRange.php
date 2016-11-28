<?php

require_once 'Zend/Form/Element/Text.php';

class Ia_Form_Element_DateRange extends Zend_Form_Element_Text
{
    public $helper = 'formDateRange';
    
    public function getValue()
    {
        $value = $this->_value;
        if(is_array($value)){
            foreach($value as $key=>$singleValue){
                if($singleValue instanceOf DateTime){
                    $value[$key] = $singleValue->format('m/d/Y');
                }            
            }
        } 
        return $value;

    }
	
}
