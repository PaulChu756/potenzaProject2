<?php
class Form_UserRegister extends \Ia\Form
{
    /**
     * Configure user form.
     *
     * @return void
     */
    public function init()
    {        
    
        /**
         * Add class to form for label alignment
         *
         * - Vertical   .form-vertical   (not required)	Stacked, left-aligned labels over controls (default)
         * - Inline     .form-inline     Left-aligned label and inline-block controls for compact style
         * - Search     .form-search     Extra-rounded text input for a typical search aesthetic
         * - Horizontal .form-horizontal
         *
         * Use .form-horizontal to have same experience as with Bootstrap v1!
         */
        
                
        // create elements
        
        $first_name         = new Zend_Form_Element_Text('first_name');
        $last_name          = new Zend_Form_Element_Text('last_name');
        $company_name       = new Zend_Form_Element_Text('company_name');
        $address_line_1     = new Zend_Form_Element_Text('address_line_1');
        $address_line_2     = new Zend_Form_Element_Text('address_line_2');
        $city               = new Zend_Form_Element_Text('city');
        $state              = new Ia_Form_Element_MultiCountryState('state',array('ref'=>'country'));
        $country            = new Zend_Form_Element_Select('country');
        $zip                = new Zend_Form_Element_Text('zip');
        $phone              = new Ia_Form_Element_Tel('phone');
        $email_address      = new Ia_Form_Element_Email('email_address');
        $password           = new Zend_Form_Element_Password('password');
        $password_repeat    = new Zend_Form_Element_Password('password_repeat');
        $agree_to_terms     = new Zend_Form_Element_Checkbox('agree_to_terms');
        $submit             = new Zend_Form_Element_Submit('submit');
        
        $first_name->setLabel('First name')
            ->setRequired(true);
            
        $last_name->setLabel('Last name')
            ->setRequired(true);
            
        $company_name->setLabel('Company');

        $address_line_1->setLabel('Address (Line 1)')->setRequired(true);
        
        $address_line_2->setLabel('Address (Line 2)');
        
        $city->setLabel('City')->setRequired(true);
        
        $state->setLabel('State/Province')->setRequired(true);
        
        $country->setLabel('Country')->addMultiOptions(
            array('US'=>'United States','CA'=>'Canada')
        );
        
        $zip->setLabel('Zip/Postal Code')->setRequired(true);
                        
        $email_address->setLabel('Email address')
            ->setRequired(true)
            ->addValidator('emailAddress');

        $phone->setLabel('Phone')
            ->setRequired(true);            
            
        $stringLength = new Zend_Validate_StringLength(array('min'=>7));    
        $password->setLabel('Password:')
            ->addValidator($stringLength)
            ->setRequired(true);
            
        $match = new Ia_Validate_Match('password');
        $password_repeat->setLabel('Password (repeat):')
            ->addValidator($match)
            ->setRequired(true);
            
        $mustAgree = new Ia_Validate_MustAgree();
        $agree_to_terms->setLabel('I have read and agree to the <a data-toggle="modal" href="#termsConditions">terms/conditions</a>.')
            ->addValidator($mustAgree)
            ->setRequired(true);
            
        $submit->setLabel('Submit');

        // add elements
        $this->addElements(array(
            $first_name,$last_name,$company_name,$country,$address_line_1,$address_line_2,$city,$state,$zip,
            $email_address,$phone,$password,$password_repeat,$agree_to_terms,$submit
        ));
        
        $agree_to_terms->getDecorator('Label')->setOption('escape', false);
                
    }

}
