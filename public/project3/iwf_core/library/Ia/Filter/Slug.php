<?php
/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';

class Ia_Filter_Slug implements Zend_Filter_Interface
{
    public function filter($str,$addRandom=true)
    {
        $replace = array();
        $delimiter ='-';
        if(!empty($replace) ) {
            $str = str_replace((array)$replace, ' ', $str);
        }
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
        if($addRandom)
            $clean .= '-'.rand(0,999);
        return $clean;
    }
}
