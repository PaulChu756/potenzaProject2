<?php
class Ia_View_Helper_InlineEditable extends Zend_View_Helper_Abstract
{

    public function inlineEditable($entity,$column,\Zend_Form_Element $element,$updateRequest=array(),$relations=array(),$filters=array(),$rawValue=null,$displayValue=null)
    {
        try{
            if(sizeof($updateRequest)==0){
                $updateRequest = array('action'=>'update','id'=>$entity->id);
            }
            $updateRequest['id'] = ($updateRequest['id']) ? $updateRequest['id'] : $entity->id;
            $updateRequest['no-form'] = true;
            $updateRequest['format'] = 'json';
            $xhtml = '';
            $rand_id = 'inline_'.uniqid();
            $displayValue = ($displayValue===null) ? $this->view->getDoctrineEntityValue($entity,$column,$relations,$filters) : $displayValue;
            $rawValue = ($rawValue===null) ? $this->view->getDoctrineEntityValue($entity,$column,$relations,$filters) : $rawValue;
            $form = new \Ia\Form;
            $form->setAttribs(array('class'=>'form-inline'));
            $element->setAttribs(array('id'=>$rand_id.'_element'))->setValue($rawValue);
            $form->AddElement($element);
            $submit = new \Zend_Form_Element_Button('submit');
            $submit->setLabel('&#10003;');
            $form->addElement($submit);
            $cancel = new \Zend_Form_Element_Button('cancel');
            $cancel->setLabel('x');
            $form->addElement($cancel);
            $xhtml = '<div class="inlineEditable" data-column="'.$column.'" data-url="'.$this->view->url($updateRequest).'">
                        <span class="inline-editable">'.(($displayValue) ? $displayValue : 'n/a').'</span>
                        <div class="hide inline-input">
                            '.$form.'
                        </div>
                        <div class="please-wait hide">
                            <img src="/img/loading.gif" />
                        </div>
                    </div>';
            $initiated = \Zend_Registry::isRegistered(get_class($this));
            if(!$initiated):
                $this->_initiate();
            endif;
            return $xhtml;
        } catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function _initiate()
    {
        $this->view->headScript()->captureStart();
        ?>
        $(document).ready(function(){
			var oldInputVal;
            $('.inlineEditable span.inline-editable').click(function(){
                var $parentEl = $(this).parents('.inlineEditable').eq(0);
                $(this).addClass('hide');
                $parentEl.find('div.inline-input').removeClass('hide');
                $parentEl.find('div.inline-input input,div.inline-input select').focus();
				var objecttochange = $parentEl.find('input,select,textarea');
				$(objecttochange).attr("data-submit","submit");
				oldInputVal = $(objecttochange).val();
            });

            $('.inlineEditable button[name="cancel"]').mousedown(function(){
                var $parentEl = $(this).parents('.inlineEditable').eq(0);
                $parentEl.find('div.inline-input').addClass('hide');
                $parentEl.find('span.inline-editable').removeClass('hide');
				var objecttochange = $parentEl.find('input,select,textarea');
				$(objecttochange).attr("data-submit","no-submit");
				$(objecttochange).val(oldInputVal);
                return false;
            });  

            $('.inlineEditable button[name="submit"]').click(function(){
                var $parentEl = $(this).parents('.inlineEditable').eq(0);
				var objecttochange = $parentEl.find('input,select,textarea');
				$(objecttochange).attr("data-submit","submit");
                $parentEl.find('input,select,textarea').trigger('change');
                return false;
            }); 

            function inlineEditableError($parentEl,resp){
                alert(resp);
                $parentEl.find('div.please-wait').addClass('hide');
                $parentEl.find('span.inline-editable').removeClass('hide').css('outline','1px solid red');
            }

            $('.inlineEditable div.inline-input').find('input,select,textarea').change(function(){
                var $parentEl = $(this).parents('.inlineEditable').eq(0);
				var objecttochange = $parentEl.find('input,select,textarea');
				if($(objecttochange).attr("data-submit") == "submit") {
                var inlineEditableUrl = $parentEl.data('url');
                var inlineEditableColumn = $parentEl.data('column');
                $parentEl.find('div.inline-input').addClass('hide');
                $parentEl.find('div.please-wait').removeClass('hide');
                var thisVal = ($(this).find('option:selected').length>0) ? $(this).find('option:selected').text() : $(this).val();
                var dataObj = {};
                dataObj[inlineEditableColumn] = $(this).val();
                $.ajax({
                    type: "POST",
                    url : inlineEditableUrl,
                    data : dataObj,
                    success : function(resp){ 
                        for(i in resp){
                            if(typeof resp[i]['type'] != 'undefined' && resp[i]['type']=='danger'){
                                inlineEditableError($parentEl,resp[i]['message']);
                                return;
                            }
                        }
                        $parentEl.find('div.please-wait').addClass('hide');
                        $parentEl.find('span.inline-editable').html(thisVal).removeClass('hide');
                    },
                    error : function(resp){
                        inlineEditableError($parentEl,resp);
                    }
                });
				}
            });

        });
        <?php
        $this->view->headScript()->captureEnd();
        $this->view->headStyle()->captureStart();
        ?>
            div.inlineEditable dt { display:none; }
            div.inlineEditable span.inline-editable { cursor:pointer; border-bottom: 1px dotted #ccc; }

        <?php
        $this->view->headStyle()->captureEnd();
        \Zend_Registry::set(get_class($this),true);
    }

}