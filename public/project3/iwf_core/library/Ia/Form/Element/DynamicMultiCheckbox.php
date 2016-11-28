<?php

class Ia_Form_Element_DynamicMultiCheckbox extends Ia_Form_Element_Poly
{
    /**
     * Use formMultiCheckbox view helper by default
     * @var string
     */
    public $helper = 'formMultiCheckboxBlankOption';

    /**
     * MultiCheckbox is an array of values by default
     * @var bool
     */
    protected $_isArray = true;

    /**
     * Load default decorators
     *
     * @return Zend_Form_Element_MultiCheckbox
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        parent::loadDefaultDecorators();

        // Disable 'for' attribute
        if (false !== $decorator = $this->getDecorator('label')) {
            $decorator->setOption('disableFor', true);
        }

        return $this;
    }
}