<?php
class Form_PasswordAssistance extends \Ia\Form
{
    /**
     * Configure user form.
     *
     * @return void
     */
    public function init()
    {        
        
        $this->setBootstrapLayout('horizontal');
        
        $email_address      = new Ia_Form_Element_Email('email_address');
        $submit             = new Zend_Form_Element_Submit('submit');
        
        $email_address->setLabel('Email address')
            ->setRequired(true)
            ->addValidator('emailAddress');
                        
        $submit->setLabel('Reset');

        // add elements
        $this->addElements(array(
            $email_address,$submit
        ));
        
    }

}
