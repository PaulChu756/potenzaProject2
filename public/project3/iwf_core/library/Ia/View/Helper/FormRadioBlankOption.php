<?php

class Ia_View_Helper_FormRadioBlankOption extends Zend_View_Helper_FormElement
{
    /**
     * Input type to use
     * @var string
     */
    protected $_inputType = 'radio';

    /**
     * Whether or not this element represents an array collection by default
     * @var bool
     */
    protected $_isArray = false;

    /**
     * Generates a set of radio button elements.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The radio value to mark as 'checked'.
     *
     * @param array $options An array of key-value pairs where the array
     * key is the radio value, and the array value is the radio text.
     *
     * @param array|string $attribs Attributes added to each radio.
     *
     * @return string The radio buttons XHTML.
     */
    public function formRadioBlankOption($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {

        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, value, attribs, options, listsep, disable

        // retrieve attributes for labels (prefixed with 'label_' or 'label')
        $label_attribs = array();
        foreach ($attribs as $key => $val) {
            $tmp    = false;
            $keyLen = strlen($key);
            if ((6 < $keyLen) && (substr($key, 0, 6) == 'label_')) {
                $tmp = substr($key, 6);
            } elseif ((5 < $keyLen) && (substr($key, 0, 5) == 'label')) {
                $tmp = substr($key, 5);
            }

            if ($tmp) {
                // make sure first char is lowercase
                $tmp[0] = strtolower($tmp[0]);
                $label_attribs[$tmp] = $val;
                unset($attribs[$key]);
            }
        }

        $labelPlacement = 'append';
        foreach ($label_attribs as $key => $val) {
            switch (strtolower($key)) {
                case 'placement':
                    unset($label_attribs[$key]);
                    $val = strtolower($val);
                    if (in_array($val, array('prepend', 'append'))) {
                        $labelPlacement = $val;
                    }
                    break;
            }
        }

        // the radio button values and labels
        $options = (array) $options;

        // build the element
        $xhtml = '';
        $list  = array();
        $panels  = array();

        // should the name affect an array collection?
        $name = $this->view->escape($name);
        if ($this->_isArray && ('[]' != substr($name, -2))) {
            $name .= '[]';
            $xhtml .= '<input type="hidden" name="'.$name.'" />';
        }

        // ensure value is an array to allow matching multiple times
        $value = (array) $value;

        // Set up the filter - Alnum + hyphen + underscore
        require_once 'Zend/Filter/PregReplace.php';
        $pattern = @preg_match('/\pL/u', 'a') 
            ? '/[^\p{L}\p{N}\-\_]/u'    // Unicode
            : '/[^a-zA-Z0-9\-\_]/';     // No Unicode
        $filter = new Zend_Filter_PregReplace($pattern, "");

        $panels = 0;
        $accordion = true; /* maybe an option later on */
        $oldListsep = $listsep;
        $openPanel = false;

        // add radio buttons to the list.
        foreach ($options as $opt_value => $opt_label) {

            if(is_array($opt_label)){
                $panels++;
                $listsep = '';
                $subList = array();
                $this_count = 0;
                /* We use Bootstrap panels */

                foreach($opt_label as $opt_value_1 => $opt_label_1){
                    $sel = $this->_addSelection($opt_value_1,$opt_label_1,$value,$filter,$escape,$disable,$id,$name,$attribs,$labelPlacement);
                    if(strpos($sel, 'checked'))
                        $this_count++;
                    $subList[] = $sel;
                }

                if($accordion)
                    $list[] = '<div class="panel panel-default"><div class="panel-heading" role="tab">' .
                            '<h4 class="panel-title" id="'.$id.'-panel-'.$panels.'-heading">' .
                            '<a data-toggle="collapse" data-parent="#'.$id.'-accordion" href="#'.$id.'-panel-'.$panels.'" ' .
                            'aria-expanded="true" aria-controls="'.$id.'-panel-'.$panels.'">'.$opt_value.'</a>'.(($this_count>0) ? ' <span class="badge">'.$this_count.'</span>' : '').'</h4></div> ' .
                            '<div id="'.$id.'-panel-'.$panels.'" class="panel-collapse collapse'.((($this_count>0 || $value == null) && !$openPanel) ? ' in' : '').'" role="tabpanel" ' .
                            'aria-labelledby="'.$id.'-panel-'.$panels.'-heading">' . 
                            '<div class="panel-body">';
                else
                    $list[] = '<div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title">'.$opt_value.'</h3></div><div class="panel-body">';

                if($this_count>0 || $value == null){
                    $openPanel = true;
                }

                $list[] = implode($oldListsep,$subList);

                if($accordion)
                    $list[] = '</div></div></div>';
                else
                    $list[] = '</div></div>';

            } else {
                $list[] = $this->_addSelection($opt_value,$opt_label,$value,$filter,$escape,$disable,$id,$name,$attribs,$labelPlacement);
            }

        }
        
        // XHTML or HTML for standard list separator?
        if (!$this->_isXhtml() && false !== strpos($listsep, '<br />')) {
            $listsep = str_replace('<br />', '<br>', $listsep);
        }

        // done!
        if($accordion && $panels){
            $xhtml .= '<div class="panel-group" id="'.$id.'-accordion" role="tablist" aria-multiselectable="false">'.implode($listsep, $list).'</div>';
        } else {
            $xhtml .= implode($listsep, $list);
        }

        return $xhtml;
    }

    protected function _addSelection($opt_value,$opt_label,$value,$filter,$escape,$disable,$id,$name,$attribs,$labelPlacement){
        // Should the label be escaped?
        if ($escape) {
            $opt_label = $this->view->escape($opt_label);
        }

        // is it disabled?
        $disabled = '';
        if (true === $disable) {
            $disabled = ' disabled="disabled"';
        } elseif (is_array($disable) && in_array($opt_value, $disable)) {
            $disabled = ' disabled="disabled"';
        }

        // is it checked?
        $checked = '';
        if (in_array($opt_value, $value)) {
            $checked = ' checked="checked"';
        }

        // generate ID
        $optId = $id . '-' . $filter->filter($opt_value);

        // Wrap the radios in labels
        $radio = '<label'
                . $this->_htmlAttribs($label_attribs) . '>'
                . (('prepend' == $labelPlacement) ? $opt_label : '')
                . '<input type="' . $this->_inputType . '"'
                . ' name="' . $name . '"'
                . ' id="' . $optId . '"'
                . ' value="' . $this->view->escape($opt_value) . '"'
                . $checked
                . $disabled
                . $this->_htmlAttribs($attribs)
                . $this->getClosingBracket()
                . (('append' == $labelPlacement) ? $opt_label : '')
                . '</label>';

        // add to the array of radio buttons
        return $radio;
    }
}
