<?php

class Ia_View_Helper_DateTimeFormat extends Zend_View_Helper_Abstract
{
    public $dateStr = 'm/d/Y h:i a';

    public function dateTimeFormat($val,$dateStr=null)
    {
        if($dateStr===null)
            $dateStr = $this->dateStr;
        if(!$val){
            return 'N/A';
        } elseif(is_object($val) && $val instanceOf \DateTime) {
            return $val->format($dateStr);
        } elseif (strtotime($val)) {
            return date($dateStr,strtotime($val));
        } else {
            return 'N/A';
        }
    }

}