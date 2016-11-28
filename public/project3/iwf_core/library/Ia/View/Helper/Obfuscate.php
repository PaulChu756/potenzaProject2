<?php

class Ia_View_Helper_Obfuscate extends Zend_View_Helper_Abstract
{

    /**
     * Returns an obfuscated string xxx (more options later)
     *
     * @param string $string The string to truncate
     * @return string The obfuscated string
     */
    public function obfuscate($string)
    {
        if (strlen($string)==0)
            return '';

        $result = '';

        $i = 0;
        while($i<=strlen($string)){
            $i++;
            $result .= 'x';
        }

        return $result;

    }
}