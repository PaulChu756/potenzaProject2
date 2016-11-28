<?php
/**
* Adapted from: http://www.mdj.us/web-development/php-programming/another-variation-on-the-time-ago-php-function-use-mysqls-datetime-field-type/
*/
class Ia_View_Helper_TimeAgo extends Zend_View_Helper_Abstract
{
    
    public function timeAgo(\DateTime $dateTimeObj, $prefix = '', $suffix = ' ago', $include=array('year','month','week','day','hour'))
    {
        $date = $dateTimeObj->getTimestamp();
        $difference = time() - $date;
        $periods = array('decade' => 315360000,
            'year' => 31536000,
            'month' => 2628000,
            'week' => 604800, 
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1);

        foreach ($periods as $key => $value) {
            if ($difference >= $value) {
                $time = floor($difference/$value);
                $difference %= $value;
                if(in_array($key, $include)){
                    $retval .= ($retval ? ' ' : '').$time.' ';
                    $retval .= (($time > 1) ? $key.'s' : $key);
                }
                $granularity--;
            }
            if ($granularity == '0') { break; }
        }
        return $prefix.$retval.$suffix;      
	}    

}