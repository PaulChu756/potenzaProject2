<?php

require_once 'Zend/Form/Element/Text.php';

class Ia_Form_Element_Date extends Zend_Form_Element_Text
{
    public $helper = 'formDate';
	
}