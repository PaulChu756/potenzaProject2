<?php
/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';

class Ia_Filter_Money implements Zend_Filter_Interface
{
    public function filter($value)
    {
        return number_format((float) $value,2,'.','');
    }
}
