<?php

class Ia_View_Helper_DateFormat extends Zend_View_Helper_Abstract
{
    public $dateStr = 'm/d/Y';

    public function dateFormat($val,$dateStr=null)
    {
        if($dateStr===null)
            $dateStr = $this->dateStr;
        if(strtotime($val))
            return date($dateStr,strtotime($val));
        else
            return '';
    }

}