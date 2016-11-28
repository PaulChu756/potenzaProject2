<?php

require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormMonthDay extends Zend_View_Helper_FormElement
{

    public $abbreviated = true;

    public function formMonthDay($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable

        if(!isset($attribs['class']))
            $attribs['class'] = array();
        $attribs['class'][] = 'form-month-day';
        
        $value = explode('/',$value);
        if($value[0]==0){
            $sel_month = 1;
            $sel_day = 1;
        } else {
            $sel_month = $value[0];
            $sel_day = $value[1];
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
		$months = array();
				
		for($i = 1; $i<= 12; $i++){
			$selected = ($sel_month==$i);
			$months[] = $this->_build($i, date("F", mktime(0, 0, 0, $i, 10)), $selected);
		}
		
		$days = array();

        if($this->abbreviated){
                $selected = ($sel_day==1);
                $days[] = $this->_build(1, '1st', $selected);
                $selected = ($sel_day==15);
                $days[] = $this->_build(15, '15th', $selected);
        } else {
            for($i = 1; $i<= 31; $i++){
                $selected = ($sel_day==$i);
                $days[] = $this->_build($i, $i, $selected);
            }            
        }
		
        // add the options to the xhtml and close the select
        $month = $xhtml.implode("\n    ", $months) . "\n</select>";
        $day = $xhtml.implode("\n    ", $days) . "\n</select>";

        return '<div class="clear"></div>'.$month.$day.'<div class="clear"></div>';
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
