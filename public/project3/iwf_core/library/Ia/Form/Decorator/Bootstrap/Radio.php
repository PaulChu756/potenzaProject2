<?php

class Ia_Form_Decorator_Bootstrap_Radio extends Ia_Form_Decorator_Bootstrap_Abstract
{

    public function getBootstrapElementClasses()
    {
        return false;
    }

    //protected $_bootstrapFormGroupClasses = array('radio'); 

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

        if($this->getOption('layout')=='horizontal'){

            $output = 
                '<div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <div class="radio">'
                        . '<label>'
                        . $input
                        . $element->getLabel()
                        . '</label>'
                        . $desc
                        . '</div>'
                . '</div>
                </div>';


        } else {

            $output = $divOpen
                . '<label>'.$element->getLabel().'</label><div style="margin-top:0px;padding-top:0px;" class="radio">'
                . ''
                . $input
                . ''
                . $desc
                . '</div>'.$divClose;           

        }        
 
        switch ($placement) {
            case (self::PREPEND):
                return $output . $separator . $content;
            case (self::APPEND):
            default:
                return $content . $separator . $output;
        }
    }
}