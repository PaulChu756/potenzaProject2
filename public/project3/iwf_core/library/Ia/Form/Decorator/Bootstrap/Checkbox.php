<?php

class Ia_Form_Decorator_Bootstrap_Checkbox extends Ia_Form_Decorator_Bootstrap_Abstract
{

    public function getBootstrapElementClasses()
    {
        return false;
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

        $multi = (strpos($input, '<br />')!==false || $element instanceOf Zend_Form_Element_MultiCheckbox || $element instanceOf Ia_Form_Element_DynamicMultiCheckbox);

        if($this->getOption('layout')=='horizontal'){

            if($multi)
                $output = 
                    '<div class="form-group">'
                    . '<div class="col-sm-2 control-label"><label>' . $element->getLabel() . '</label></div>'
                        . '<div class="col-sm-10">
                            <div class="checkbox">'
                            . $input
                            . $desc
                            . '</div>'
                    . '</div>
                    </div>';
            else
                $output = 
                    '<div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <div class="checkbox">'
                            . '<label>'
                            . $input
                            . $element->getLabel()
                            . '</label>'
                            . $desc
                            . '</div>'
                    . '</div>
                    </div>';

        } else {

            if($multi) //does not strictly adhere to bootstrap standards but looks good
                $output = $divOpen
                . '<label>'.$element->getLabel().'</label><div style="margin-top:0px;padding-top:0px;" class="checkbox">'
                . ''
                . $input
                . ''
                . $desc
                . '</div>'.$divClose;
            else  
                $output = $divOpen
                . '<div class="checkbox">'
                . '<label>'
                . $input.' '.$element->getLabel()
                . '</label>'
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