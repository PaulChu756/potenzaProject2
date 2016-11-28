<?php
class Ia_View_Helper_FormDynamicMultiText extends Zend_View_Helper_FormText
{

    /**
     * incomplete, but the goal would be the ability to dynamically add/remove single lines
     */
    public function formDynamicMultiText($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {

        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable
    
        $template = parent::formText($name, $value, $attribs, $options, $listsep);
        $template = str_replace('name="'.$name.'"','name="'.$name.'[]"',$template);
        $template = str_replace('id="'.$id.'"','id="'.$id.'_0"',$template);

        $xhtml = $template;
        
        $this->view->headScript()->captureStart();
        ?>
        $(document).ready(function(){
            //alert('ready');
        });
        <?php
        $this->view->headScript()->captureEnd();        
        
        return $xhtml;
    }    
    
}