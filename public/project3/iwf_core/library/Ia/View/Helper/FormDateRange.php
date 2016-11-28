<?php

require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormDateRange extends Zend_View_Helper_FormText
{

    public function formDateRange($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $xhtml = '';
        $part_xhtml = array();
        for($i=1;$i<=2;$i++){
            $part_xhtml[$i] = '';
            $part_name = $name.'['.$i.']';
            $part_id = $name.'_'.$i;
            $attribs['id'] = $part_id;
            $thisValue = (isset($value[$i])) ? $value[$i] : null;
            if($thisValue instanceOf DateTime){
                $thisValue = $thisValue->format('m/d/y');
            }
            $part_xhtml[$i] = parent::formText($part_name,$thisValue,$attribs,$options,$listsep);
            if(strpos($part_xhtml[$i],'class="')!==false){
                $part_xhtml[$i] = str_replace('class="','class="datepicker ',$part_xhtml[$i]);
            } else {
                $part_xhtml[$i] = str_replace('type="text"','type="text" class="datepicker"',$part_xhtml[$i]);
            }
        }
        $xhtml = '<div class="row"><div class="col-md-6">'.$part_xhtml[1].'<span class="help-block">From Date</span></div><div class="col-md-6">'.$part_xhtml[2].'<span class="help-block">To Date</span></div></div>';
		return $xhtml;
    }
    
}