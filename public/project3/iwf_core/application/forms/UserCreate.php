<?php
class Form_UserCreate extends \Ia\Form
{
    /**
     * Configure user form.
     *
     * @return void
     */
    public function init()
    {        
                
        // create elements
        
        $first_name         = new Zend_Form_Element_Text('first_name');
        $last_name          = new Zend_Form_Element_Text('last_name');
        $email_address      = new Ia_Form_Element_Email('email_address');
        $password           = new Zend_Form_Element_Password('password');
        $password_repeat    = new Zend_Form_Element_Password('password_repeat');
        $role               = new Zend_Form_Element_Select('role');
        $welcome             = new Zend_Form_Element_Checkbox('welcome');
        $submit             = new Zend_Form_Element_Submit('submit');
        
        $first_name->setLabel('First name')
            ->setRequired(true);
            
        $last_name->setLabel('Last name')
            ->setRequired(true);
            
        $email_address->setLabel('Email address')
            ->setRequired(true)
            ->addValidator('emailAddress'); 
            
        $email_address->setLabel('Email address')
            ->setRequired(true)
            ->addValidator('emailAddress');
            
        $stringLength = new Zend_Validate_StringLength(array('min'=>7));    
        $password->setLabel('Password:')
            ->addValidator($stringLength);
            
        $match = new Ia_Validate_Match('password');
          
        $password_repeat->setLabel('Password (repeat):')
            ->addValidator($match);

        $roles = array();
        foreach(Ia\Config::get('acl/roles') as $acl_role=>$parents){
            $roles[$acl_role] = $acl_role;
        }
        $role->setLabel('Role')->setMultiOptions($roles);
        
        $welcome->setLabel('Send Welcome E-mail');
                        
        $submit->setLabel('Submit');

        // add elements
        $this->addElements(array(
            $first_name,$last_name,$email_address,$password,$password_repeat,$role,$welcome,$submit
        ));

        // set decorators
        //EasyBib_Form_Decorator::setFormDecorator($this, EasyBib_Form_Decorator::BOOTSTRAP, 'submit');
        
    }

}
