<?php

require_once 'Zend/View/Helper/FormElement.php';

class Ia_View_Helper_FormTagsInput extends Zend_View_Helper_FormSelect
{
    public function formTagsInput($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // force $value to array so we can compare multiple values to multiple
        // options; also ensure it's a string for comparison purposes.
        $value = array_map('strval', (array) $value);

        //$attribs['value'] = implode(',',$value);

        // check if element may have multiple values
        $multiple = '';

        if (substr($name, -2) == '[]') {
            // multiple implied by the name
            $multiple = ' multiple="multiple"';
        }

        if (isset($attribs['multiple'])) {
            // Attribute set
            if ($attribs['multiple']) {
                // True attribute; set multiple attribute
                $multiple = ' multiple="multiple"';

                // Make sure name indicates multiple values are allowed
                if (!empty($multiple) && (substr($name, -2) != '[]')) {
                    $name .= '[]';
                }
            } else {
                // False attribute; ensure attribute not set
                $multiple = '';
            }
            unset($attribs['multiple']);
        }

        $attribs['autocomplete'] = 'off';

        // handle the options classes
        $optionClasses = array();
        if (isset($attribs['optionClasses'])) {
            $optionClasses = $attribs['optionClasses'];
            unset($attribs['optionClasses']);
        }
        
        // now start building the XHTML.
        $disabled = '';
        if (true === $disable) {
            $disabled = ' disabled="disabled"';
        }

        // Build the surrounding select element first.
        $xhtml = '<input'
                . ' name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . $multiple
                . $disabled
                . $this->_htmlAttribs($attribs)
                . " />\n    ";

        $this->view->headLink()->appendStylesheet('/vendor/bootstrap-tagsinput/bootstrap-tagsinput.css'); 
        $this->view->headScript()->appendFile('/vendor/bootstrap-tagsinput/bootstrap-tagsinput.js');
        $this->view->headScript()->appendFile('/vendor/typeahead/typeahead.js');
        $this->view->headStyle()->captureStart();
        ?>
        div.bootstrap-tagsinput input { width: 100% !important; }
        div.bootstrap-tagsinput { display: block !important; position: relative; }
        <?php
        $this->view->headStyle()->captureEnd();
        $this->view->headScript()->captureStart();
        ?>
        $(document).ready(function(){
            <?php
                $tagsJson = array();
                foreach ((array) $options as $opt_value => $opt_label):
                    $tagsJson[] = array('id' => $opt_value, 'title' => $opt_label);
                endforeach; 
            ?>
            var tags<?=$this->view->escape($id);?> = <?=json_encode($tagsJson);?>;
            $('input#<?=$this->view->escape($id);?>').tagsinput({
                typeahead: {
                    source: tags<?=$this->view->escape($id);?>,
                    afterSelect: function(val) { this.$element.val(""); },
                },
                freeInput: false,
                itemValue: 'id',
                itemText: 'title',
                allowDuplicates: false,
                <? /* =((!$multiple) ? 'maxTags: 1' : ''); */ ?>          
            });
            /** 
             * @thanks http://stackoverflow.com/a/29413469/421726
             */
            $('input#<?=$this->view->escape($id);?>').on('itemAdded', function(event) {
                setTimeout(function(){
                    $(">input[type=text]",".bootstrap-tagsinput").val("");
                }, 1);
            });    
            <?php 
            foreach($value as $subval):
                if(strpos($subval,',')!==false):
                    $subvals = explode(',',$subval);
                else:
                    $subvals = array($subval);
                endif;
                foreach($subvals as $val):
                    if(isset($options[$val])): ?>
                        <?php  $optionsVals = addslashes($options[$val]);  ?>  
                        $('input#<?=$this->view->escape($id);?>').tagsinput('add', { id : '<?=$val;?>', title : '<?=$optionsVals;?>' });
                    <?php endif;
                endforeach; 
            endforeach; 
            ?>              
        });
        <?php
        $this->view->headScript()->captureEnd();
        return $xhtml;

    }

}
