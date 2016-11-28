<?php

require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormMapBounds extends Zend_View_Helper_FormTextarea
{

    public function formMapBounds($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {   
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
        $boundableMap = new Ia_View_Helper_BoundableMap;
        if(isset($attribs['latitude'])){
            $boundableMap->latitude = $attribs['latitude'];
            unset($attribs['latitude']);
        }
        if(isset($attribs['longitude'])){
            $boundableMap->longitude = $attribs['longitude'];
            unset($attribs['longitude']);
        }
        $boundableMap->enableDrawingManager = true;
        $boundableMap->encodeOverlayJsonTargetId = $this->view->escape($id);
        $boundableMap->setView($this->view);
        $xhtml = parent::formTextarea($name,$value,$attribs,$options,$listsep);
        $xhtml .= '<div style="clear:both;"></div>'.$boundableMap->boundableMap();

        $this->view->headStyle()->captureStart();
        ?>
        #<?=$this->view->escape($id)?> {
            display:none;
        }
        <?php
        $this->view->headStyle()->captureEnd();


		return $xhtml;
    }
    
}