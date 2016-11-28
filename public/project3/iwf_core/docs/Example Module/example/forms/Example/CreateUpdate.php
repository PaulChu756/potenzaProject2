<?php
class Example_Form_Example_CreateUpdate extends \Ia\Form
{
    /**
     * Configure user form.
     *
     * @return void
     */
    public function init()
    {        

        $this->setBootstrapLayout('horizontal');      
        
        $title                          = new Zend_Form_Element_Text('title');
        $active                         = new Zend_Form_Element_Checkbox('active');
        $submit                         = new Zend_Form_Element_Submit('submit');
        
        $title->setLabel('Title')
            ->setRequired(true);

        $active->setLabel('Active');
                        
        $submit->setLabel('Submit');

        // add elements
        $this->addElements(array(
            $title,$active,$submit
        ));

        $this->setDefaults(array(
            'active'=>1  
        ));

    }

}
