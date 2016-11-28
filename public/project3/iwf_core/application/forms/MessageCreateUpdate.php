<?php
class Form_MessageCreateUpdate extends \Ia\Form
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
        
        $origin_user_id         = new Zend_Form_Element_Hidden('origin_user_id');
        $recipient_user_id     = new Zend_Form_Element_MultiCheckbox('recipient_user_id');
        $type                   = new Zend_Form_Element_Select('type');
        $subject                = new Zend_Form_Element_Text('subject');
        $message                = new Zend_Form_Element_Textarea('message');
        $submit                 = new Zend_Form_Element_Submit('submit');
        
        if(Zend_Registry::isRegistered('auth')){
            $auth = Zend_Registry::get('auth');
            $origin_user_id->setValue($auth->id);
        }
        
        $UserModel = new Ia\Entity\User;
        $users = $UserModel->getAllOptions();
        unset($users[$auth->id]);
        
        $recipient_user_id->setLabel('Recipients')
            ->addMultiOptions($users)
            ->setRequired(true);
        
        $MessageModel = new Ia\Entity\Message;
        $type->setLabel('Type')
            ->addMultiOptions(array(''=>'Choose One') + $MessageModel->getAllTypeOptions())
            ->setRequired(true);
            
        $subject->setLabel('Subject')->setRequired(true);
        
        $message
        ->setLabel('Message')
        ->setAttribs(array('rows'=>5,'cols'=>80))
        ->setRequired(true);            
                                
        $submit->setLabel('Submit');

        // add elements
        $this->addElements(array(
            $origin_user_id,$recipient_user_id,$type,$subject,$message,$submit
        ));
        
    }

}
