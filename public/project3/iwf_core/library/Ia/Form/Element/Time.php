<?php

class Ia_Form_Element_Time extends Zend_Form_Element_Text
{
    public $helper = 'formTime';
    
    protected $_hour;
    protected $_minute;
    protected $_am_pm = 'AM'; 

    public function setHour($value)
    {
        if(strlen($value)==1)
            $value = '0'.$value;
        $this->_hour = $value;
        return $this;
    }
 
    public function getHour()
    {
        return $this->_hour;
    }    
    
    public function setMinute($value)
    {
        if(strlen($value)==1)
            $value = '0'.$value;
        $this->_minute = $value;
        return $this;
    }
 
    public function getMinute()
    {
        return $this->_minute;
    }

    public function setAmPm($value)
    {
        $value = strtoupper($value);
        if($value=='PM')
            $this->_am_pm = 'PM';
        else
            $this->_am_pm = 'AM';
        return $this;
    }
 
    public function getAmPM()
    {
        return $this->_am_pm;
    }    
	
    public function getValue()
    {
		switch($this->getAmPm()){
            case 'PM':
                if($this->getHour()==12){
                    $hour = '12';
                }elseif($this->getHour()<12){
                    $hour = $this->getHour()+12;
                }else{
                    $hour = $this->getHour();
                }
                break;
            default:
                if($this->getHour()==12){
                    $hour = '00';
                }else{
                    $hour = $this->getHour();
                }
                break;
        }
        return $hour.':'.$this->getMinute().':00';
    }
    
    public function setValue($value)
    {
        if(is_array($value)){
            $this->setHour($value[0]);
            $this->setMinute($value[1]);
            $this->setAmPm($value[2]);
        } else {
            $parts = explode(':',$value);
            if($parts[0]>12){
                $this->setHour($parts[0]-12);
                $this->setMinute($parts[1]);
                $this->setAmPm('PM');            
            } else {
                $this->setHour($parts[0]);
                $this->setMinute($parts[1]);
                $this->setAmPm('AM');            
            }
        }
        return $this;    
    }
	
}