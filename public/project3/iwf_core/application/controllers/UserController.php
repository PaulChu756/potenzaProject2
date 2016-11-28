<?php

class UserController extends Ia_Controller_Action_Abstract
{
    
    public function init()
    {
        $this->view->singular = 'user';
        $this->view->plural = 'users';    
        $this->view->columns = array('id'=>'Id','first_name'=>'First Name','last_name'=>'Last Name','email_address'=>'Email','role'=>'Role');
        $this->view->detailColumns = $this->view->columns + array(
                'address_line_1' => 'Address',
                'address_line_2' => 'Address (Line 2)',
                'city' => 'City',
                'state' => 'State',
                'zip' => 'Zip',
                'phone' => 'Phone Number',
            );
        $this->view->actions = array(
            'view'=>$this->actions('view'),
            'edit'=>$this->actions('edit'),
            'activate'=>$this->actions('activate'),
            'impersonate'=>$this->actions('impersonate'),
        ); 
        $this->entity = new Ia\Entity\User;
        $this->createForm = new Form_UserCreate;
        $this->updateForm = new Form_UserCreate;    
        parent::init();
        $this->addFilterWidget('activeInactive','e.active',1);
    }

    public function actions($name){
        $actions = array(
            'activate'=>array(
            'condition'=>'eval:($item->active==0)','label'=>'Active',
                'true'=>array(
                    'label'=>'Activate','url'=>array('action'=>'activate','active'=>1,'id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-play'
                ),'false'=>array(
                    'label'=>'Deactivate','url'=>array('action'=>'activate','active'=>0,'id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-pause'
                )
            ),
            'view'=>array('label'=>'View','url'=>array('action'=>'view','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-eye-open'),        
            'edit'=>array('label'=>'Edit','url'=>array('action'=>'update','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-pencil'),           
            'impersonate'=>array('label'=>'Impersonate','url'=>array('action'=>'impersonate','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-user'),        
        );
        return $actions[$name];
    }
    public function impersonateAction()
    {
        $auth = Zend_Auth::getInstance();
        $record = $this->retrieveRecord();
        $authAdapter = new Ia\Auth\Adapter();
        $authAdapter->setEntity($record)
            ->setIdentityVar('email_address')
            ->setCredentialVar('password');    

        // Set the input credential values
        $authAdapter->setIdentity($record->email_address);
        $authAdapter->setCredential($record->password);            
        $result = $auth->authenticate($authAdapter);            
        if (!$result->isValid()) {
            foreach ($result->getMessages() as $message) {
                Ia_View_Helper_Alert::addAlert($message,'error');
            }
        } else {
            $logged_in_user = new \Zend_Session_Namespace('logged_in_user');
            $logged_in_user->id = $record->id;
            Ia_View_Helper_Alert::addAlert('You are now impersonating '.$record->first_name.' '.$record->last_name,'success');
            $this->_helper->redirector->gotoRoute(array('controller'=>'index','action'=>'index'),null,true);
        }
    } 

    public function updateAction(){
        $this->updateForm->getElement('password')->setDescription('If left blank, password will be unchanged');
        $record = $this->view->record = $this->retrieveRecord();
        $this->view->form = $form = $this->updateForm;
        $form->getElement('welcome')->setDescription('If checked, a new random password will be generated for the user');
        $form->setDefaults($record->toArray());
        if($this->getRequest()->isPost()){
            $values = $this->getRequest()->getPost();
            if ($form->isValid($values)) {
                try{
                    $welcome = (isset($values['welcome']) && $values['welcome']==1) ? true : false;
                    if(isset($values['welcome'])) unset($values['welcome']);
                    if($welcome){
                        $password = substr(md5(rand(100000,999999)),rand(0,25),7);
                        $values['password'] = md5($password);
                        $values['pw_reset_required'] = 1;
                    } else {
                        if(strlen($values['password'])==0)
                            unset($values['password']);
                        else
                            $values['password'] = md5($values['password']);
                    }
                    unset($values['password_repeat']);
                    $record = $this->entity->updateEntity($record,$values);
                    $this->em->persist($record);
                    $this->em->flush();
                    if($welcome){
                        $values['password'] = $password;
                        $this->_userCreatedEmail($values);
                    }
                    Ia_View_Helper_Alert::addAlert('Record has been successfully updated.','success');
                    $this->returnHome();
                } catch (Exception $e){
                    Ia_View_Helper_Alert::addAlert($e->getMessage(),'error');
                }
            }
        }
        $this->_handleScaffolds();        
    }
    
    public function loginAction()
    {    
        $this->view->plural = '';
        $auth = Zend_Auth::getInstance();    
        if(\Ia\Config::get('rememberMeNamespace')){
            $rememberMe = new Ia_Cookie_Namespace(\Ia\Config::get('rememberMeNamespace'));
            if(isset($rememberMe->rememberMe) && $rememberMe->rememberMe==true){
                $auth->setStorage(new Ia_Auth_Storage_Cookie());
            }
        }
        
        if ($auth->hasIdentity()) {
            $this->_helper->redirector->gotoRoute(array('controller'=>'index','action'=>'index'));
        }        
    
        $this->view->form = $form = new Form_Login;
        if($this->getRequest()->isPost()){
            $values = $this->getRequest()->getPost();
            if ($form->isValid($values)) {
            
                if(\Ia\Config::get('rememberMeNamespace') && $values['rememberMe']){
                    $rememberMe->rememberMe = true;
                    $auth->setStorage(new Ia_Auth_Storage_Cookie());
                }
                $user = new Ia\Entity\User();
                $this->_handleLogin($user,$values);
            }
        } else {
            $form->setDefaults($this->getRequest()->getPost());
            if(\Ia\Config::get('rememberMeNamespace')){
                if(isset($rememberMe->rememberMe) && $rememberMe->rememberMe==true){
                    $form->removeElement('rememberMe');
                } else {
                    $form->setDefaults(array('rememberMe'=>true));
                }
            }
        }
    }

    protected function _handleLogin($user,$values){
        \Ia\Entity\User::handleLogin($user,$values);
    }
    
    public function logoutAction()
    {
        \Ia\Log::write('User logged out',null,null,'INFORMATION');
        $auth = Zend_Auth::getInstance();        
        $auth->clearIdentity();
        Zend_Session::destroy();
        $this->_helper->redirector->gotoRoute(array('controller'=>'user','action'=>'login'));    
    }
    
    public function myAccountAction()
    {
        $user = Zend_Registry::get('auth');
        $this->view->form = $form = new Form_UserAccount;
        $form->setDefaults($user->toArray());
        $this->view->form = $form;
        if($this->getRequest()->isPost()){
            $values = $this->getRequest()->getPost();
            if ($form->isValid($values)) {
                $user = $user->updateEntity($user,$values);                    
                $this->em->persist($user);
                $this->em->flush();
                Ia_View_Helper_Alert::addAlert('Your account has been updated.','success');
            }
        }
    }
    
    
    public function passwordAssistanceAction()
    {
        $this->view->form = $form = new Form_PasswordAssistance;
        if($this->getRequest()->isPost()){
            $values = $this->getRequest()->getPost();
            if ($form->isValid($values)) {
                // Check to see if there is an active user record
                $modelUser = new \Ia\Entity\User;
                if($activeUser = $modelUser->getUserByEmail($values['email_address'])){
                    $activeUser = $modelUser->regenerateToken($activeUser->id);
                    $this->_passwordResetEmail($activeUser);
                    Ia_View_Helper_Alert::addAlert('A password reset link has been E-mailed to '.$values['email_address'],'success');
                    $this->_helper->redirector->gotoRoute(array('module'=>'default','controller'=>'user','action'=>'login','token'=>null));            
                } else {
                    Ia_View_Helper_Alert::addAlert('No active user account exists for the address '.$values['email_address'],'error');
                }
            }
        }
    }
    
    public function passwordResetAction()
    {
        $error = false;
        $this->view->form = $form = null;
                
        if(!Zend_Registry::isRegistered('auth') || !$user = Zend_Registry::get('auth')){
            if(!$token=$this->getRequest()->getParam('token')){
                Ia_View_Helper_Alert::addAlert('No token was provided','error');
                $error = true;
            }
            $user = $this->em->getRepository('Ia\Entity\User')->findOneBy(array('token' => $token));
        }
        if(!$user){
            Ia_View_Helper_Alert::addAlert('This password reset link is invalid or has expired.  Please try again','error');
            $error = true;
        } 
        if(!$error){
            $this->view->user = $user;
            $this->view->form = $form = new Form_PasswordReset;
            if($this->getRequest()->isPost()){
                $values = $this->getRequest()->getPost();
                if ($form->isValid($values)) {
                    $modelUser = new Ia\Entity\User;
                    $modelUser->updatePassword($user->id,$values['password']);
                    $auth = Zend_Auth::getInstance();
                    if (!$auth->hasIdentity()){
                        Ia_View_Helper_Alert::addAlert('Your password has been updated.  Please login.','success');
                        $this->_helper->redirector->gotoRoute(array('controller'=>'user','action'=>'login','token'=>null));
                    } else {
                        Ia_View_Helper_Alert::addAlert('Your password has been successfully updated.','success');
                        $this->_helper->redirector->gotoRoute(array('module'=>'default','controller'=>'index','action'=>'index','token'=>null));
                    }
                }
            }
        }   

        $this->view->error = $error;
    } 
    
    public function confirmEmailAction()
    {
        $error = false;
        $this->view->form = $form = null;

        $logged_in_user = false;
        if(Zend_Registry::isRegistered('auth')){
            $logged_in_user = Zend_Registry::get('auth');
            if($logged_in_user->email_confirmed == 1){
                Ia_View_Helper_Alert::addAlert('Your email has already been confirmed.','information');
                $this->_helper->redirector->gotoRoute(array('module'=>'default','controller'=>'index','action'=>'index'));
            }
        }
        $this->view->logged_in_user = $logged_in_user;
                
        //if(!Zend_Registry::isRegistered('auth') || !$user = Zend_Registry::get('auth')){
        if($token=$this->getRequest()->getParam('token')){
                //Ia_View_Helper_Alert::addAlert('No token was provided','error');
                //$error = true;
            //}
            $user = $this->em->getRepository('Ia\Entity\User')->findOneBy(array('token' => $token));
            //}
            if(!$user){
                Ia_View_Helper_Alert::addAlert('This confirmation link is invalid or has expired.  Please try again','error');
                $error = true;
            } 
            if(!$error){
                $user->active = 1;
                $user->email_confirmed = 1;
                $this->em->persist($user);
                $this->em->flush();
                if(\Ia\Config::get('allow_login_with_unconfirmed_email'))
                    Ia_View_Helper_Alert::addAlert('You have successfully confirmed your E-mail.','success');
                else
                    Ia_View_Helper_Alert::addAlert('You have successfully confirmed your E-mail.  You may now login.','success');
                $this->_helper->redirector->gotoRoute(array('module'=>'default','controller'=>'user','action'=>'login','token'=>null));
            }   
            $this->view->error = $error;
        }
    }    
    
    public function forbiddenAction(){}
    
    public function createAction(){
        $this->view->form = $form = new Form_UserCreate;
        $form->getElement('password')->setDescription('If left blank, a temporary password will be sent to the user.');
        if($this->getRequest()->isPost()){
            $values = $this->getRequest()->getPost();
            if ($form->isValid($values)) {
                // Check to see if there is an active user record
                $modelUser = new \Ia\Entity\User;
                $existingUser = $modelUser->getUserByEmail($values['email_address']);
                if($existingUser){
                    Ia_View_Helper_Alert::addAlert('A user account already exists for the E-mail address "'.$values['email_address'].'"','error');
                } else {
                    $welcome = (isset($values['welcome']) && $values['welcome']==1) ? true : false;
                    if(isset($values['welcome'])) unset($values['welcome']);
                    $saveValues = $values;
                    unset($saveValues['password_repeat']);
                    unset($saveValues['submit']);
                    $saveValues['active'] = 1;
                    if(strlen($values['password'])==0){
                        $saveValues['pw_reset_required'] = 1;
                        $saveValues['password'] = $values['password'] = substr(md5(rand(100000,999999)),rand(0,25),7);
                    }
                    $saveValues['password'] = md5($values['password']);
                    $newEntity = $this->entity->createEntity($saveValues);
                    $this->em->persist($newEntity);
                    $this->em->flush();
                    if($welcome){
                        $this->_userCreatedEmail($values);
                        $alertMsg = 'New user has been successfully created and their login information E-mailed to them.';
                    } else {
                        $alertMsg = 'New user has been successfully created (but has not been notified).';
                    }
                    Ia_View_Helper_Alert::addAlert($alertMsg,'success');
                    $this->returnHome();                    
                }
            }
        }
    }
    
    public function registerAction(){
        $this->view->form = $form = new Form_UserRegister;
        if($this->getRequest()->isPost()){
            $values = $this->getRequest()->getPost();
            if ($form->isValid($values)) {
                // Check to see if there is an active user record
                $modelUser = new \Ia\Entity\User;
                $existingUser = $modelUser->getUserByEmail($values['email_address']);
                if($existingUser){
                    Ia_View_Helper_Alert::addAlert('A user account already exists for the E-mail address "'.$values['email_address'].'"','error');
                } else {

                    $values['role'] = \Ia\Config::get('acl/register_default_role');
                    $saveValues = $values;
                    unset($saveValues['password_repeat']);
                    unset($saveValues['submit']);
                    if(\Ia\Config::get('allow_login_with_unconfirmed_email'))
                        $saveValues['active'] = 1;
                    else
                        $saveValues['active'] = 0;
                    if(strlen($values['password'])==0){
                        $saveValues['pw_reset_required'] = 1;
                        $saveValues['password'] = $values['password'] = substr(md5(rand(100000,999999)),rand(0,25),7);
                    }
                    $saveValues['password'] = md5($values['password']);
                    $newEntity = $this->entity->createEntity($saveValues);                    
                    $this->em->persist($newEntity);
                    $this->em->flush();
                    $newEntity = $newEntity->regenerateToken($newEntity->id);
                    $values['token'] = $newEntity->token;
                    $values['active'] = 0;
                    $this->_userRegisteredEmail($values);
                    if(\Ia\Config::get('allow_login_with_unconfirmed_email')){
                        $this->_handleLogin($newEntity,$values);
                    } else {
                        Ia_View_Helper_Alert::addAlert('Your account has been created, but you will not be able to login until you follow the instructions sent to you in a confirmation E-mail','information');
                        $this->_helper->redirector->gotoRoute(array('module'=>'default','controller'=>'user','action'=>'login','token'=>null));
                    }
                }
            }
        }
    }    
    
    
    protected function _userCreatedEmail($values)
    {
        
        $options = Ia\Config::get('resources/mail');
        $mail = new Zend_Mail();
        
        $loginUrl = 'http://'.$this->getRequest()->getHttpHost().'/user/login';
                
        $mail->setBodyHtml(
            '<p>A new user account has been created for you on '.$this->getRequest()->getHttpHost().'!</p>'  .
            '<p>Please use the following information to login:</p>'  .
            '<p><a href="'.$loginUrl.'">'.$loginUrl.'</a><br />' .
            'Email address: '.$values['email_address'].'<br />' .
            'Password: '.$values['password'].'</p>'
        );
        $mail->setFrom($options['defaultFrom']['email'], $options['defaultFrom']['name']);
        $mail->addTo($values['email_address']);
        $mail->setSubject('['.$this->getRequest()->getHttpHost().'] New User Account');
        $mail->send();       
    }

    public function resendEmailConfirmationAction()
    {
        $user = false;
        if($this->getRequest()->getParam('email')){
            $user = $this->em->getRepository('Ia\Entity\User')->findOneBy(array('email_address' => trim($this->getRequest()->getParam('email'))));
        } elseif (Zend_Registry::isRegistered('auth')) {
            $user = Zend_Registry::get('auth');
        }
        if($user){
            if($user->email_confirmed == 1){
                Ia_View_Helper_Alert::addAlert('Your email has already been confirmed.','information');
                $this->_helper->redirector->gotoRoute(array('module'=>'default','controller'=>'index','action'=>'index'));
            }
            $user = $user->regenerateToken($user->id);
            $values = array(
                    'token' => $user->token,
                    'email_address' => $user->email_address
                );
            $this->_userRegisteredEmail($values);
            Ia_View_Helper_Alert::addAlert('A new email confirmation has been sent to '.$user->email_address.'.','success');
        } else {
            Ia_View_Helper_Alert::addAlert('Sorry, we could not find a matching user for the information provided.','error');
        }        
        $this->returnReferer();
    }
    
    protected function _userRegisteredEmail($values)
    {
        
        $options = Ia\Config::get('resources/mail');
        $mail = new Zend_Mail();
        
        $confirmUrl = 'http://'.$this->getRequest()->getHttpHost().'/user/confirm-email/token/'.$values['token'];
                
        $mail->setBodyHtml(
            '<p>A new user account has been created for you on '.$this->getRequest()->getHttpHost().'!</p>'  .
            '<p>Please click the following link to confirm your registration:</p>'  .
            '<p><a href="'.$confirmUrl.'">'.$confirmUrl.'</a></p>'
        );
        $mail->setFrom($options['defaultFrom']['email'], $options['defaultFrom']['name']);
        $mail->addTo($values['email_address']);
        $mail->setSubject('['.$this->getRequest()->getHttpHost().'] New User Account');
        $mail->send();       
    }
    
    protected function _passwordResetEmail(Ia\Entity\User $user)
    {
        $mailOptions = \Ia\Config::get('resources/mail');
        $mail = new Zend_Mail();
        
        $resetUrl = 'http://'.$this->getRequest()->getHttpHost().'/user/password-reset/token/'.$user->token;
        
        $mail->setBodyHtml(
            '<p>Someone (hopefully you) requested a password change on '.$this->getRequest()->getHttpHost().'</p>' .
            '<p>Please click on the following link to reset your password:</p>'  .
            '<p><a href="'.$resetUrl.'">'.$resetUrl.'</a></p>'
        );
        $mail->setFrom($mailOptions['defaultFrom']['email'], $mailOptions['defaultFrom']['name']);
        $mail->addTo($user->email_address);
        $mail->setSubject('['.$this->getRequest()->getHttpHost().'] Password Reset Link');
        $mail->send();            
    }
        
}
