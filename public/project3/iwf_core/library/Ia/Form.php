<?php
namespace Ia;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Form extends \Zend_Form
{
    /**
     *
     * \Doctrine\Entity\Manager
     */
    protected $_em = null;

    /**
     * Get Doctrine Entity Manager
     * @return \Doctrine\Entity\Manager
     */    
    public function getEntityManager() {
        if($this->_em===null){
            $dc = \Zend_Registry::get('doctrine');
            $this->_em = $dc->getEntityManager();        
        }
        return $this->_em;        
    }
    
    protected $_bootstrapLayout = 'default'; //default, horizontal

    protected $_decoratorOptions = array();

    public function addDecoratorOptions(\Zend_Validate_Interface $element,$values){
        $element_id = $element->getId();
        $this->_decoratorOptions[$element_id] = $values;
        return $this;    
    }

    public function getDecoratorOptions(\Zend_Validate_Interface $element){
        $element_id = $element->getId();
        return (isset($this->_decoratorOptions[$element_id])) ? $this->_decoratorOptions[$element_id] : false;
    }

    public function setBootstrapLayout($layout){
        $this->_bootstrapLayout = $layout;
    }

    public function getBootstrapLayout(){
        return $this->_bootstrapLayout;
    }

    public function applyAllDecorators()
    {
        $this->setAttrib('role','form');

        if($this->getBootstrapLayout()=='horizontal'){
            $this->setAttrib('class', 'form-horizontal');
        }

        // for all elements:
        $this->addElementPrefixPath('Ia_Form_Decorator',
                                    'Ia/Form/Decorator/',
                                    'decorator');

        foreach($this->getElements() as $element){

            $decorator_options = array('layout'=>$this->getBootstrapLayout());
            if($this->getDecoratorOptions($element)){
                $decorator_options = array_merge($decorator_options,$this->getDecoratorOptions($element));
            }
            /* important to go from more specific to less specific */
            switch(true)
            {
                case ($element instanceof \Ia_Form_Element_Number):
                    $element->clearDecorators();
                    $element->addDecorator('Bootstrap_Number',$decorator_options);
                    break;                                
                case ($element instanceof \Ia_Form_Element_Tel):
                    $element->clearDecorators();
                    $element->addDecorator('Bootstrap_Tel',$decorator_options);
                    break;                
                case ($element instanceof \Ia_Form_Element_Email):
                    $element->clearDecorators();
                    $element->addDecorator('Bootstrap_Email',$decorator_options);
                    break;     
                case ($element instanceof \Zend_Form_Element_Checkbox):
                case ($element instanceof \Zend_Form_Element_MultiCheckbox):   
                case ($element instanceof \Ia_Form_Element_DynamicMultiCheckbox):  
                    $element->clearDecorators();
                    $element->addDecorator('Bootstrap_Checkbox',$decorator_options);
                    break;
                case ($element instanceof \Zend_Form_Element_Radio):
                    $element->clearDecorators();
                    $element->addDecorator('Bootstrap_Radio',$decorator_options);
                    break; 
                case ($element instanceof \Zend_Form_Element_Submit):
                    $element->clearDecorators();
                    $element->addDecorator('Bootstrap_Submit',$decorator_options);
                    break;
                case ($element instanceof \Zend_Form_Element_File):
                    $element->clearDecorators();
                    $element->addDecorator('Bootstrap_File',$decorator_options);
                    break;
                case ($element instanceof \Zend_Form_Element_Hidden):
                    break;                    
                default:
                    $element->clearDecorators();
                    $element->addDecorator('Bootstrap_Text',$decorator_options);
                    break;
            }

        }
    }

    public function __tostring()
    {
        
        $this->applyAllDecorators();
        
        $xhtml = parent::__tostring();
        
        //Add Required Fields Key
        if(strpos($xhtml, '<span class="red">*</span>')!==false)
            $xhtml .= '<p class="required-fields"><span class="red">*</span> - Required Fields.</p>';

        //because this annoys me
        $xhtml = str_replace('<dl class="zend_form">','',$xhtml);
        $xhtml = str_replace('</dl>','',$xhtml);

        //Add Popovers
        foreach($this->_popovers as $popover=>$desc){
            if($this->getElement($popover)){
                $elHtml = (string) $this->getElement($popover);
                $elHtmlpopover = str_replace('</label>',' <span class="smaller">[<a class="input-popover" title="'.$this->getElement($popover)->getLabel().'" href="#" rel="popover" data-content="'.$desc.'">?</a>]</span></label>',$elHtml);
                $xhtml = str_replace($elHtml,$elHtmlpopover,$xhtml);
            }
        }
        
        return $xhtml;
    }
    
    protected $_popovers = array();
    
    public function addPopovers($popovers){
        $this->_popovers += $popovers;
        return $this;
    }

}