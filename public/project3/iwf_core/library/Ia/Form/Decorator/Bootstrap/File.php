<?php

class Ia_Form_Decorator_Bootstrap_File extends Ia_Form_Decorator_Bootstrap_Text implements Zend_Form_Decorator_Marker_File_Interface
{

    /**
     * Attributes that should not be passed to helper
     * @var array
     */
    protected $_attribBlacklist = array('helper', 'placement', 'separator', 'value');

    /**
     * Default placement: append
     * @var string
     */
    protected $_placement = 'APPEND';

    /**
     * Get attributes to pass to file helper
     *
     * @return array
     */
    public function getAttribs()
    {
        $attribs   = $this->getOptions();

        if (null !== ($element = $this->getElement())) {
            $attribs = array_merge($attribs, $element->getAttribs());
        }

        foreach ($this->_attribBlacklist as $key) {
            if (array_key_exists($key, $attribs)) {
                unset($attribs[$key]);
            }
        }

        return $attribs;
    }

    /**
     * Render a form file
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }

        $view = $element->getView();
        if (!$view instanceof Zend_View_Interface) {
            return $content;
        }

        $name      = $element->getName();
        $attribs   = $this->getAttribs();
        if (!array_key_exists('id', $attribs)) {
            $attribs['id'] = $name;
        }

        if($element->getValidator('Extension') instanceof Zend_Validate_File_Extension){
            $accepts = array();
            foreach($element->getValidator('Extension')->getExtension() as $extension){
                $accepts[] = '.'.$extension;
            }
            if($accepts)
                $attribs['accepts'] = implode(',',$accepts);
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $markup    = array();
        $size      = $element->getMaxFileSize();
        if ($size > 0) {
            $element->setMaxFileSize(0);
            $markup[] = $view->formHidden('MAX_FILE_SIZE', $size);
        }

        if (Zend_File_Transfer_Adapter_Http::isApcAvailable()) {
            $markup[] = $view->formHidden(ini_get('apc.rfc1867_name'), uniqid(), array('id' => 'progress_key'));
        } else if (Zend_File_Transfer_Adapter_Http::isUploadProgressAvailable()) {
            $markup[] = $view->formHidden('UPLOAD_IDENTIFIER', uniqid(), array('id' => 'progress_key'));
        }

        if ($element->isArray()) {
            $name .= "[]";
            $count = $element->getMultiFile();
            for ($i = 0; $i < $count; ++$i) {
                $htmlAttribs        = $attribs;
                $htmlAttribs['id'] .= '-' . $i;
                $markup[] = $view->{$element->helper}($name, $htmlAttribs);
            }
        } else {
            $markup[] = $view->{$element->helper}($name, $attribs);
        }

        $markup = implode($separator, $markup);

        switch ($placement) {
            case self::PREPEND:
                $markup = $markup . $separator . $content;
            case self::APPEND:
            default:
                $markup = $content . $separator . $markup;
        }

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
                . $markup
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