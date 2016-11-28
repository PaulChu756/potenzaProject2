<?php

class Ia_View_Helper_YesNo extends Zend_View_Helper_Abstract
{
    
    public function yesNo($val,$yes='Yes',$no='No')
    {
        return ($val) ? $yes : $no;
    }

}