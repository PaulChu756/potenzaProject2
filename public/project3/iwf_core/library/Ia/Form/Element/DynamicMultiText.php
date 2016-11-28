<?php

require_once 'Zend/Form/Element/Text.php';

class Ia_Form_Element_DynamicMultiText extends Zend_Form_Element_Text
{

    /**
     * @var string Default view helper
     */
    public $helper = 'formDynamicMultiText';

}