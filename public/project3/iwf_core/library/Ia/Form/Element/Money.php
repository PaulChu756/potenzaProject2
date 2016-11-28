<?php

require_once 'Zend/Form/Element/Text.php';

class Ia_Form_Element_Money extends Zend_Form_Element_Text
{
	public $helper = 'formMoney';

    protected $_value;

    public function __construct($spec, $options = null)
    {
        $this->addFilter(new Ia_Filter_Money);
        return parent::__construct($spec, $options);
    }

    public function getValues()
    {
        return 'tarzan';
    }
}