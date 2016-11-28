<?php

class Ia_View_Helper_FullPathToUrl extends Zend_View_Helper_Abstract
{
    
    public function fullPathToUrl($fullPath)
    {
        return str_replace(PUBLIC_PATH,'',$fullPath);
	}

}