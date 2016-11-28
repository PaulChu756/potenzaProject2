<?php

class Ia_View_Helper_YesNoNull extends Zend_View_Helper_Abstract
{
    
    public function yesNoNull($val,$yes='Yes',$no='No')
    {
        if($val===true)
            return 'Yes';
        if($val===false)
            return 'No';
        return 'Unknown';
    }

}