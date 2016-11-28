<?php

require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormWysiwyg extends Zend_View_Helper_FormTextarea
{
    public function formWysiwyg($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable
        $xhtml = $this->formTextarea($name, $value, $attribs);
        $this->view->headScript()->appendFile('/vendor/trumbowyg/trumbowyg.js');
        $this->view->headLink()->appendStylesheet('/vendor/trumbowyg/ui/trumbowyg.min.css');
        $this->view->headScript()->captureStart();
        ?>
        $(document).ready(function(){
            $('textarea#<?=$this->view->escape($id);?>').trumbowyg();
        });
        <?php
        $this->view->headScript()->captureEnd();
        return $xhtml;
    }

}
