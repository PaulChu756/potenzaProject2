<?php

require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormDateOfBirth extends Zend_View_Helper_FormElement
{

    public function getMinimumAge()
    {
        return 0;
    }

    public function getMaximumAge()
    {
        return 130;
    }

    public function formDateOfBirth($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable

	if(!isset($attribs['class']))
	    $attribs['class'] = array();
        $attribs['class'][] = 'form-date-of-birth';
        
        $value = (is_array($value)) ? $value : explode('/',$value);
        
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

        if(strlen($value[0])==0)
            $value[0] = '01';

        if(strlen($value[1])==0)
            $value[1] = '01';

        if(strlen($value[2])==0)
            $value[2] = date('Y') - $this->getMinimumAge();

        $month = array();
        for($i = 1; $i<= 12; $i++){
            $i = sprintf("%02s", $i);
            $selected = ($value[0]==$i);
            $month[] = $this->_build($i, $i.' - '.date('M',strtotime(date('Y-'.$i.'-01 H:i:s'))), $selected);
        }

        $day = array();
        for($i = 1; $i<= 31; $i++){
            $i = sprintf("%02s", $i);
            $selected = ($value[1]==$i);
            $day[] = $this->_build($i, $i, $selected);
        }        

        $year = array();
        for($i = (date('Y')  - $this->getMinimumAge()) ; $i >= (date('Y') - $this->getMaximumAge()); $i--){
            $selected = ($value[2]==$i);
            $year[] = $this->_build($i, $i, $selected);
        }        
		
        // add the options to the xhtml and close the select
        $day = $xhtml.implode("\n    ", $day) . "\n</select>";
        $month = $xhtml.implode("\n    ", $month) . "\n</select>";
        $year = $xhtml.implode("\n    ", $year) . "\n</select>";

        return '<div class="clear"></div><div class="row">
        <div class="col-sm-4">'.$month.'</div>
        <div class="col-sm-4">'.$day.'</div>
        <div class="col-sm-4">'.$year.'</div></div>
        <div class="clear"></div>';
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
