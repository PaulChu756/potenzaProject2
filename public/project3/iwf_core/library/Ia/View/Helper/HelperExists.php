<?php
/*
 * http://akrabat.com/zend-framework/determining-if-a-zf-view-helper-exists/
 */

class Ia_View_Helper_HelperExists extends Zend_View_Helper_Abstract
{
    
    function helperExists($name) {
        return (bool)$this->view->getPluginLoader('helper')->load($name, false);
    }

}