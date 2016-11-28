<?php

require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormTime extends Zend_View_Helper_FormElement
{

    public function formTime($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable

	if(!isset($attribs['class']))
	    $attribs['class'] = array();
        $attribs['class'][] = 'form-time';
        
        $value = explode(':',$value);
        if($value[0]==0){
            $standard_hour = 12;
            $amPm = 'AM';            
        } else {
            $standard_hour = ($value[0] > 12) ? ($value[0] - 12) : $value[0];
            $amPm = ($value[0]>=12) ? 'PM' : 'AM';
        }

        $disabled = '';
        if (true === $disable) {
            $disabled = ' disabled="disabled"';
        }

        // Build the surrounding select element first.
        $xhtml = '<select'
                . ' name="' . $this->view->escape($name) . '[]"'
                . ' id="' . $this->view->escape($id) . '"'
                . $disabled
                . $this->_htmlAttribs($attribs)
                . ">\n    ";

        // build the list of options

		// hour
		$hour = array();
				
		for($i = 1; $i<= 12; $i++){
			$selected = ($standard_hour==$i);
			$hour[] = $this->_build($i, $i, $selected);
		}
		
		$minute = array();
		
		$minute[] = $this->_build('00', '00', ($value[1]=='00'));
		$minute[] = $this->_build('15', '15', ($value[1]=='15'));
		$minute[] = $this->_build('30', '30', ($value[1]=='30'));
		$minute[] = $this->_build('45', '45', ($value[1]=='45'));
		
		$ampm = array();
		
		$ampm[] = $this->_build('AM','AM',($value[0]<12));
		$ampm[] = $this->_build('PM','PM',($value[0]>=12));
		
        // add the options to the xhtml and close the select
        $hour = $xhtml.implode("\n    ", $hour) . "\n</select>";
        $minute = $xhtml.implode("\n    ", $minute) . "\n</select>";
        $ampm = $xhtml.implode("\n    ", $ampm) . "\n</select>";

        return '<div class="clear"></div>'.$hour.$minute.$ampm.'<div class="clear"></div>';
    }

    protected function _build($value, $label, $selected=false)
    {

        $opt = '<option'
             . ' value="' . $this->view->escape($value) . '"'
             . ' label="' . $this->view->escape($label) . '"';
			 
		if($selected)
			$opt .= ' selected="selected"';

        $opt .= '>' . $this->view->escape($label) . "</option>";

        return $opt;
    }

}
