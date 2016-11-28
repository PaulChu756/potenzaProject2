<?php

abstract class Ia_Form_Decorator_Bootstrap_Abstract extends Zend_Form_Decorator_Abstract
{

    public function getHtml5Type()
    {
        return false;
    }

    public function getBootstrapElementClasses()
    {
        return array('form-control');
    }

    protected $_bootstrapFormGroupClasses = array('form-group');

    public function getBootstrapFormGroupClasses()
    {
        $element = $this->getElement();
        $form_group_classes = array_merge($this->_bootstrapFormGroupClasses,array($element->getId()));
        $options = $this->getOptions();
        if(isset($options['form_group_classes'])){
            $form_group_classes = array_merge($form_group_classes,$options['form_group_classes']);
        }
        return $form_group_classes;
    }

    public function buildLabel()
    {
        $element = $this->getElement();
        $label = $element->getLabel();
        if ($translator = $element->getTranslator()) {
            $label = $translator->translate($label);
        }
        if ($element->isRequired()) {
            $label .= ' <span class="red">*</span>';
        }

        $classes = array();
        switch($this->getOption('layout')){
            case 'horizontal':
                $classes[] = 'control-label';
                $classes[] = 'col-sm-2';
                break;
        }

        return $element->getView()
                       ->formLabel($element->getName(), $label, array('escape'=>false,'class'=>implode(' ',$classes)));
    }
 
    public function buildInput()
    {
        $element = $this->getElement();

        if($this->getBootstrapElementClasses()){
            $classes = ($element->getAttrib('class')) ? $element->getAttrib('class') : array();
            $classes = array_merge($classes,$this->getBootstrapElementClasses());
            $element->setAttrib('class',$classes);
        }

        $helper  = $element->helper;
        $xhtml = $element->getView()->$helper(
            $element->getName(),
            $element->getValue(),
            $element->getAttribs(),
            $element->options
        );

        if($this->getHtml5Type()){
            $pattern = '/(?<=type\=")[^]]+?(?=")/';
            $xhtml = preg_replace($pattern,$this->getHtml5Type(),$xhtml);
        }

        return $xhtml;
    }
 
    public function buildErrors()
    {
        $element  = $this->getElement();
        $messages = $element->getMessages();
        if (empty($messages)) {
            return '';
        }
        $this->_bootstrapFormGroupClasses[] = 'has-error';
        return '<span class="help-block">' .
               $element->getView()->formErrors($messages) . '</span>';
    }
 
    public function buildDescription()
    {
        $element = $this->getElement();
        $desc    = $element->getDescription();
        if (empty($desc)) {
            return '';
        }
        return '<span class="help-block">' . $desc . '</span>';
    }
 
    public function render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }
        if (null === $element->getView()) {
            return $content;
        }
 
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $label     = $this->buildLabel();
        $input     = $this->buildInput();
        $errors    = $this->buildErrors();
        $desc      = $this->buildDescription();

        $class = ($this->getBootstrapFormGroupClasses()) ? ' class="'.implode(' ',$this->getBootstrapFormGroupClasses()).'"' : '';
        $divOpen = '<div'.$class.'>';
        $divClose = '</div>';
        $postLabel = '';

        switch($this->getOption('layout')){
            case 'horizontal':
                $postLabel = '<div class="col-sm-10">';
                $divClose = '</div>'.$divClose;
                break;
        }
 
        $output = $divOpen
                . $label
                . $postLabel
                . $input
                . $errors
                . $desc
                . $divClose;
 
        switch ($placement) {
            case (self::PREPEND):
                return $output . $separator . $content;
            case (self::APPEND):
            default:
                return $content . $separator . $output;
        }
    }
}