<?php

class Ia_Form_Decorator_Bootstrap_Submit extends Ia_Form_Decorator_Bootstrap_Abstract
{

    public function getBootstrapElementClasses()
    {
        return array('btn btn-default');
    }

    public function render($content)
    {
        $element = $this->getElement();
        $attribs = $element->getAttribs();
        $class = ($attribs['class']) ? $element->getAttrib('class') : 'btn btn-primary';
        unset($attribs['class']);
        $attrib_string = '';
        foreach($attribs as $key=>$attrib){
            $attrib_string .= $key.'="'.$attrib.'" ';
        }
        $value = ($element->getValue()) ? $element->getValue() : $element->getLabel();
        if($this->getOption('layout')=='horizontal'){
			return 
			'<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button value="'.$value.'" '.$attrib_string.' name="'.$element->getName().'" type="submit" class="'.$class.'">'.$element->getLabel().'</button>
				</div>
			</div>';
    	} else {
	        return '<button value="'.$value.'" '.$attrib_string.' name="'.$element->getName().'" type="submit" class="'.$class.'">'.$element->getLabel().'</button>';
    	}
        
    }

}