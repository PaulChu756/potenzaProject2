<?php

class Ia_Form_Element_MonthDay extends Zend_Form_Element_Text
{
    public $helper = 'formMonthDay';
    
    protected $_month;
    protected $_day;

    public function setMonth($value)
    {
        $this->_month = $value;
        return $this;
    }
 
    public function getMonth()
    {
        return $this->_month;
    }    
    
    public function setDay($value)
    {
        $this->_day = $value;
        return $this;
    }
 
    public function getDay()
    {
        return $this->_day;
    }    
	
    public function getValue()
    {
        return $this->getMonth().'/'.$this->getDay();
    }
    
    public function setValue($value)
    {
        if(is_array($value)){
            $this->setMonth($value[0]);
            $this->setDay($value[1]);
        } else {
			$parts = explode('/',$value);
			$this->setMonth($parts[0]);
			$this->setDay($parts[1]);
        }
        return $this;    
    }
	
}