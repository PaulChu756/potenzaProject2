<?php

require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormMoney extends Zend_View_Helper_FormText
{

    public function formMoney($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $xhtml = '<div class="input-group"><span class="input-group-addon">$</span>' .
        			parent::formText($name,$value,$attribs,$options,$listsep) .
        		  '</div>';
		return $xhtml;
    }
    
}