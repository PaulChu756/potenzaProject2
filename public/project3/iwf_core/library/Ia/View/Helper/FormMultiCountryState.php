<?php
class Ia_View_Helper_FormMultiCountryState extends Zend_View_Helper_FormElement
{

    public function formMultiCountryState($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
    
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable

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

        // disabled by default b/c we will rely upon ajax to populate
        $disabled = ' disabled="disabled"';
        
        // Build the surrounding select element first.
        $xhtml = '<select'
                . ' name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . $multiple
                . $disabled
                . $this->_htmlAttribs($attribs)
                . "> \n    ";

        // add the options to the xhtml and close the select
        $xhtml .= "\n</select>";
        
        if(!\Zend_Registry::isRegistered($id.'_js')):
            Zend_Registry::set($id.'_js',true);
            $this->view->headScript()->captureStart();
            ?>
            if(typeof stateData == 'undefined'){
                var stateData = {
                        "Countries": [
                            {
                                "n":"Canada",
                                "v":"CA",
                                "s": [
                                    {
                                        "n":"Alberta",
                                        "v":"AB"
                                    },
                                    {
                                        "n":"British Columbia",
                                        "v":"BC"
                                    },
                                    {
                                        "n":"Manitoba", 
                                        "v":"MB"
                                    },
                                    {
                                        "n":"New Brunswick",
                                        "v":"NB"
                                    },
                                    {
                                        "n":"Newfoundland",
                                        "v":"NL"
                                    },
                                    {
                                        "n":"Northwest Territories",
                                        "v":"NT"
                                    },
                                    {
                                        "n":"Nova Scotia",
                                        "v":"NS"
                                    },
                                    {
                                        "n":"Nunavut",
                                        "v":"NU"
                                    },
                                    {
                                        "n":"Ontario",
                                        "v":"ON"
                                    },
                                    {
                                        "n":"Prince Edward Island",
                                        "v":"PE"
                                    },
                                    {
                                        "n":"Quebec",
                                        "v":"QC"
                                    },
                                    {
                                        "n":"Saskatchewan",
                                        "v":"SK"
                                    },
                                    {
                                        "n":"Yukon Territory",
                                        "v":"YT"
                                    }
                                ]
                            },
                            {
                                "n":"UnitedStates",
                                "v":"US",
                                "s": [
                                    {
                                        "n":"Alabama",
                                        "v":"AL"
                                    },
                                    {
                                        "n":"Alaska",
                                        "v":"AK"
                                    },
                                    {
                                        "n":"Arizona",
                                        "v":"AZ"
                                    },
                                    {
                                        "n":"Arkansas",
                                        "v":"AR"
                                    },
                                    {
                                        "n":"California",
                                        "v":"CA"
                                    },
                                    {
                                        "n":"Colorado",
                                        "v":"CO"
                                    },
                                    {
                                        "n":"Connecticut",
                                        "v":"CT"
                                    },
                                    {
                                        "n":"Delaware",
                                        "v":"DE"
                                    },
                                    {
                                        "n":"District of Columbia",
                                        "v":"DC"
                                    },
                                    {
                                        "n":"Florida",
                                        "v":"FL"
                                    },
                                    {
                                        "n":"Georgia",
                                        "v":"GA"
                                    },
                                    {
                                        "n":"Hawaii",
                                        "v":"HI"
                                    },
                                    {
                                        "n":"Idaho",
                                        "v":"ID"
                                    },
                                    {
                                        "n":"Illinois",
                                        "v":"IL"
                                    },
                                    {
                                        "n":"Indiana",
                                        "v":"IN"
                                    },
                                    {
                                        "n":"Iowa",
                                        "v":"IA"
                                    },
                                    {
                                        "n":"Kansas",
                                        "v":"KS"
                                    },
                                    {
                                        "n":"Kentucky",
                                        "v":"KY"
                                    },
                                    {
                                        "n":"Louisiana",
                                        "v":"LA"
                                    },
                                    {
                                        "n":"Maine",
                                        "v":"ME"
                                    },
                                    {
                                        "n":"Maryland",
                                        "v":"MD"
                                    },
                                    {
                                        "n":"Massachusetts",
                                        "v":"MA"
                                    },
                                    {
                                        "n":"Michigan",
                                        "v":"MI"
                                    },
                                    {
                                        "n":"Minnesota",
                                        "v":"Mn"
                                    },
                                    {
                                        "n":"Mississippi",
                                        "v":"MS"
                                    },
                                    {
                                        "n":"Missouri",
                                        "v":"MO"
                                    },
                                    {
                                        "n":"Montana",
                                        "v":"MT"
                                    },
                                    {
                                        "n":"Nebraska",
                                        "v":"NE"
                                    },
                                    {
                                        "n":"Nevada",
                                        "v":"NV"
                                    },
                                    {
                                        "n":"New Hampshire",
                                        "v":"NH"
                                    },
                                    {
                                        "n":"New Jersey",
                                        "v":"NJ"
                                    },
                                    {
                                        "n":"New Mexico",
                                        "v":"NM"
                                    },
                                    {
                                        "n":"New York",
                                        "v":"NY"
                                    },
                                    {
                                        "n":"North Carolina",
                                        "v":"NC"
                                    },
                                    {
                                        "n":"North Dakota",
                                        "v":"ND"
                                    },
                                    {
                                        "n":"Ohio",
                                        "v":"OH"
                                    },
                                    {
                                        "n":"Oklahoma",
                                        "v":"OK"
                                    },
                                    {
                                        "n":"Oregon",
                                        "v":"OR"
                                    },
                                    {
                                        "n":"Pennsylvania",
                                        "v":"PA"
                                    },
                                    {
                                        "n":"Rhode Island",
                                        "v":"RI"
                                    },
                                    {
                                        "n":"South Carolina",
                                        "v":"SC"
                                    },
                                    {
                                        "n":"South Dakota",
                                        "v":"SD"
                                    },
                                    {
                                        "n":"Tennessee",
                                        "v":"TN"
                                    },
                                    {
                                        "n":"Texas",
                                        "v":"TX"
                                    },
                                    {
                                        "n":"Utah",
                                        "v":"UT"
                                    },
                                    {
                                        "n":"Vermont",
                                        "v":"VT"
                                    },
                                    {
                                        "n":"Virginia",
                                        "v":"VA"
                                    },
                                    {
                                        "n":"Washington",
                                        "v":"WA"
                                    },
                                    {
                                        "n":"West Virginia",
                                        "v":"WV"
                                    },
                                    {
                                        "n":"Wisconsin",
                                        "v":"WI"
                                    },
                                    {
                                        "n":"Wyoming",
                                        "v":"WY"
                                    }
                                ]
                            }
                        ]
                    };       
            }
            var initialValue = '<?php echo $value; ?>';
            
            function populateStates()
            {
                $('#<?=$this->view->escape($id); ?>').attr('disabled',true);
                $('#<?=$this->view->escape($id); ?>').children('option').remove();
                var selectedCountry = ($('#<?=$this->view->escape($attribs['ref']); ?>').val());
                for(i in stateData.Countries){
                    if(stateData.Countries[i]['v']==selectedCountry){
                        for(j in stateData.Countries[i]['s']){
                            $option = $('<option value="' + stateData.Countries[i]['s'][j]['v'] + '">' + stateData.Countries[i]['s'][j]['n'] + '</option>');
                            if(initialValue.length > 0 && initialValue == stateData.Countries[i]['s'][j]['v']){
                                $option.attr('selected','selected');
                                initialValue = '';
                            }
                            $('#<?=$this->view->escape($id); ?>').append($option);
                        }
                    }
                }
                $('#<?=$this->view->escape($id); ?>').attr('disabled',false);
            }
            $(document).ready(function(){
                $('#<?=$this->view->escape($attribs['ref']); ?>').change(function(){
                    populateStates();
                });
                populateStates();
            });
            <?php
            $this->view->headScript()->captureEnd();
        endif;

        return $xhtml;
    }
    
}
