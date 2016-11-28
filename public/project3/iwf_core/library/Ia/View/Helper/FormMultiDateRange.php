<?php
class Ia_View_Helper_FormMultiDateRange extends Zend_View_Helper_FormElement
{

    public function formMultiDateRange($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
    
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable
        
        // Build the hidden input.
        $xhtml = '<input'
                . ' type="hidden"'
                . ' name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . $multiple
                . $disabled
                . $this->_htmlAttribs($attribs)
                . " /> \n    ";
        
        if(!\Zend_Registry::isRegistered($id.'_js')):
            Zend_Registry::set($id.'_js',true);
            $this->view->headScript()->captureStart();
            ?>
            $(document).ready(function(){
                function <?=$id;?>_add_date_range_line(values)
                {
                    var start = $('<input class="form-control datepicker startInput" type="text" />'); 
                    var end = $('<input class="form-control datepicker endInput" type="text" />');
                    if(values){
                        start.val(values.start);
                        end.val(values.end);
                    }
                    var start1 = start.datepicker({autoclose: true});
                    var end2 = end.datepicker({autoclose: true, minDate: 0});
                    start.change(function(){
                        var date1 = start.datepicker('getDate');           
                        var date = new Date( Date.parse( date1 ) ); 
                        date.setDate( date.getDate() + 1 );        
                        var newDate = date.toDateString(); 
                        newDate = new Date( Date.parse( newDate ) );     
                        end2.datepicker('setStartDate',newDate);
                        
                    });
                    var row = $('<tr><td class="start"></td><td class="end"></td><td><span class="deleteDateRange"><i class="glyphicon glyphicon-remove pointer"></i></span></td></tr>');
                    row.find('.start').append(start);
                    row.find('.end').append(end);
                    $('#<?=$id;?>_table tbody').append(row);
                    $('#<?=$id;?>_table').removeClass('hide');
                }
                function <?=$id;?>_persist_values(){
                    var values = [];
                    $('#<?=$id;?>_table tbody tr').each(function(){
                        values.push({start : $(this).find('input.startInput').val(), end : $(this).find('input.endInput').val()});
                    });
                    $('#<?=$this->view->escape($id);?>').val(JSON.stringify(values));
                }
                $('#<?=$id;?>_container .addDateRange').click(function(){
                    <?=$id;?>_add_date_range_line(false);
                });
                $('#<?=$id;?>_container').on('click','.deleteDateRange', function(){
                    if(confirm('Are you sure you want to delete this date range?')){
                        var tr = $(this).parents('tr').eq(0);
                        tr.remove();
                        <?=$id;?>_persist_values();
                        if($('#<?=$id;?>_table tbody tr').length==0){
                            $('#<?=$id;?>_table').addClass('hide');
                        }
                    }
                });
                $('#<?=$id;?>_container').on('change','input', function(){
                    <?=$id;?>_persist_values();
                    return true;
                });
                var <?=$id;?>_values = <?=((strlen($value)>1) ? $value : '[]');?>;
                if(<?=$id;?>_values.length>0){
                    for(i in <?=$id;?>_values){
                        <?=$id;?>_add_date_range_line(<?=$id;?>_values[i]);
                    }
                    <?=$id;?>_persist_values();
                }
            });
            <?php
            $this->view->headScript()->captureEnd();
        endif;

        $xhtml .= '<div id="'.$id.'_container"><span class="buttonLink addDateRange pointer"><i class="glyphicon glyphicon-plus"></i> Add Date Range</span><table class="hide table table-striped table-condensed" id="'.$id.'_table"><thead><th>Start Date</th><th>End Date</th><th></th><tbody></tbody></table></div>';
        
        return $xhtml;
    }
    
}
