<?php
/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';

class Ia_Filter_DateTime implements Zend_Filter_Interface
{
    protected $_dateFormat = '';

    public function __construct($dateFormat='Y-m-d')
    {
        $this->setDateFormat($dateFormat);
    }
    
    public function setDateFormat($dateFormat){
        $this->_dateFormat = $dateFormat;
    }
    
    public function getDateFormat(){
        return $this->_dateFormat;
    }

    public function filter($value)
    {
        return $value->format($this->getDateFormat());
    }
}
