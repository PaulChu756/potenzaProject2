<?php

class Ia_View_Helper_BinaryToHuman extends Zend_View_Helper_Abstract
{
    
    public function binaryToHuman($value)
    {
        return ($value) ? 'Yes' : 'No';
    }

}