<?php
class Form_Login extends \Ia\Form
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
        
        $email_address      = new Ia_Form_Element_Email('email_address');
        $password           = new Zend_Form_Element_Password('password');
        $submit             = new Zend_Form_Element_Submit('submit');
        
        $email_address->setLabel('Email address')
            ->setRequired(true)
            ->addValidator('emailAddress');

        $password->setLabel('Password:')
            ->setRequired(true);
            
                        
        $submit->setLabel('Login');

        if(\Ia\Config::get('rememberMeNamespace')){
            $rememberMe         = new Zend_Form_Element_Checkbox('rememberMe');
            $rememberMe->setLabel('Remember Me');
            // add elements
            $this->addElements(array(
                $email_address,$password,$rememberMe,$submit
            ));            
            $this->addPopovers(
                array('rememberMe'=>'Will keep your session active even after you have closed your browser.  Cookies must be fully enabled.')
            );
        } else {
            // add elements
            $this->addElements(array(
                $email_address,$password,$submit
            ));
        }
        
    }

}
