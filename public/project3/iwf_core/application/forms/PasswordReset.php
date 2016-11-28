<?php
class Form_PasswordReset extends \Ia\Form
{
    /**
     * Configure user form.
     *
     * @return void
     */
    public function init()
    {      

        $this->setBootstrapLayout('horizontal');      
        // create elements
        
        $password           = new Zend_Form_Element_Password('password');
        $password_repeat    = new Zend_Form_Element_Password('password_repeat');
        $submit             = new Zend_Form_Element_Submit('submit');
        
        $stringLength = new Zend_Validate_StringLength(array('min'=>7));    
        $password->setLabel('Password:')
            ->addValidator($stringLength)
            ->setRequired(true);
            
        $match = new Ia_Validate_Match('password');
          
        $password_repeat->setLabel('Password (repeat):')
            ->addValidator($match)
            ->setRequired(true);            
                        
        $submit->setLabel('Reset');

        // add elements
        $this->addElements(array(
            $password,$password_repeat,$submit
        ));
        
    }

}
