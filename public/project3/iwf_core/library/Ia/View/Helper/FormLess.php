<?php

/** Zend_View_Helper_FormElement */
require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormLess extends Zend_View_Helper_FormElement 
{
    /**
     * Render HTML form
     *
     * @param  string $name Form name
     * @param  null|array $attribs HTML form attributes
     * @param  false|string $content Form content
     * @return string
     */
    public function formLess($name, $attribs = null, $content = false)
    {
        $info = $this->_getInfo($name, $content, $attribs);
        extract($info);

        if (!empty($id)) {
            $id = ' id="' . $this->view->escape($id) . '"';
        } else {
            $id = '';
        }

        if (array_key_exists('id', $attribs) && empty($attribs['id'])) {
            unset($attribs['id']);
        }
        
        if (!empty($name) && !($this->_isXhtml() && $this->_isStrictDoctype())) {
            $name = ' name="' . $this->view->escape($name) . '"';
        } else {
            $name = '';
        }
        
        if ( array_key_exists('name', $attribs) && empty($attribs['id'])) {
            unset($attribs['id']);
        }

        if(!isset($attribs['class'])){
            $attribs['class'] = '';
        }

        $attribs['class'] .= ' pseudo-form';

        $xhtml = '<div'
               . $id
               . $name
               . $this->_htmlAttribs($attribs)
               . '>';

        if (false !== $content) {
            $xhtml .= $content
                   .  '</div>';
        }

        return $xhtml;
    }
}
