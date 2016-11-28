<?php

class Ia_View_Helper_DaysOfTheWeek extends Zend_View_Helper_Abstract
{
    
    public function daysOfTheWeek($days_string,$del=', ')
    {
        if(strlen($days_string)==0){
            return 'N/A';
        } else {
            $daysArray = array();
            for($i=0;$i<strlen($days_string);$i++){
                $daysArray[] = $this->getDayNameByNumber($days_string[$i]);
            }
        }
        return implode($del,$daysArray);
    }

	public function getDayNameByNumber($n=null)
	{
	    $days = array(
	            0 => 'Sunday',
	            1 => 'Monday',
	            2 => 'Tuesday',
	            3 => 'Wednesday',
	            4 => 'Thursday',
	            5 => 'Friday',
	            6 => 'Saturday',
	        );
	    if($n===null)
	        return $days;
	    return (isset($days[$n])) ? $days[$n] : false;
	}    

}