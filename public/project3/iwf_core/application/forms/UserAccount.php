<?php
class Form_UserAccount extends Form_UserRegister
{
    /**
     * Configure user form.
     *
     * @return void
     */
    public function init()
    {        
        parent::init();
        $this->removeElement('password');
        $this->removeElement('password_repeat');
        $this->removeElement('agree_to_terms');
        $this->removeElement('email_address');
        $this->addElement(new Zend_Form_Element_Hidden('email_address'));
    }

}