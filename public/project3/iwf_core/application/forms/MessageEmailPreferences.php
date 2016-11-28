<?php
class Form_MessageEmailPreferences extends \Ia\Form
{
    /**
     * Configure user form.
     *
     * @return void
     */
    public function init()
    {        
    
        $submit                 = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Submit')->setOrder(50);

        // add elements
        $this->addElements(array(
            $submit
        ));
        
    }

}
