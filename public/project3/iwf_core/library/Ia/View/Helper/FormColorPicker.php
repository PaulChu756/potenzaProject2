<?php
require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormColorPicker extends Zend_View_Helper_FormText
{

	public function formColorPicker($name, $value = null, $attribs = null,
			$options = null, $listsep = "<br />\n")
	{	
		$xhtml = '<div class="input-group picker-element"><span class="input-group-addon"><i></i></span>' .
				parent::formText($name,$value,$attribs,$options,$listsep) .
				'</div>';
		 
		return $xhtml;
	}

}