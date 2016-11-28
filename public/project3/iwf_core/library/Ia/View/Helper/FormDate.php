<?php

require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormDate extends Zend_View_Helper_FormText
{

    public function formDate($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        if($value instanceOf DateTime){
            $value = $value->format('m/d/Y');
        }
        $xhtml = parent::formText($name,$value,$attribs,$options,$listsep);
        if(strpos($xhtml,'class="')!==false){
            $xhtml = str_replace('class="','class="datepicker ',$xhtml);
        } else {
            $xhtml = str_replace('type="text"','type="text" class="datepicker"',$xhtml);
        }
		return $xhtml;
    }
    
}