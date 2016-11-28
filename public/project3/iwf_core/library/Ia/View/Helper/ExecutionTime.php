<?php
/**
*
*/
class Ia_View_Helper_ExecutionTime extends Zend_View_Helper_Abstract
{
    
    public function executionTime($key='time_start')
    {
        if(\Zend_Registry::isRegistered($key)){
            return number_format((microtime(true) - \Zend_Registry::get($key)),3).(($suffix) ? 's' : '');
        }
	}    

}