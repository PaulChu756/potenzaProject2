<?php
class Ia_View_Helper_FormEditableMultiSelect extends Zend_View_Helper_FormSelect
{
    /**
     * Generates 'select' list of options.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The option value to mark as 'selected'; if an
     * array, will mark all values in the array as 'selected' (used for
     * multiple-select elements).
     *
     * @param array|string $attribs Attributes added to the 'select' tag.
     *
     * @param array $options An array of key-value pairs where the array
     * key is the radio value, and the array value is the radio text.
     *
     * @param string $listsep When disabled, use this list separator string
     * between list values.
     *
     * @return string The select tag and options XHTML.
     */
    public function formEditableMultiSelect($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
    
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable
    
        $attribs['multiple'] = true;
        $xhtml = parent::formSelect($name, $value, $attribs,
        $options, $listsep);
        $xhtml .= '<p><i class="icon-star"></i> Don\'t see what you are looking for? [ <a class="pointer add_new_option" id="add_new_option_'.$this->view->escape($id).'">Add new option</a> ]</p>';
        
        $this->view->headScript()->captureStart();
        ?>
        $(document).ready(function(){
            $("#add_new_option_<?=$this->view->escape($id)?>").click(function(){
                var new_option = prompt('Enter additional option:');
                if(new_option){
                    $("#<?=$this->view->escape($id)?>").prepend('<option selected="selected" value="' + new_option + '">' + new_option + '</option>');
                }
            });
        });
        <?php
        $this->view->headScript()->captureEnd();        
        
        
        
        
        return $xhtml;
    }    
    
    

}