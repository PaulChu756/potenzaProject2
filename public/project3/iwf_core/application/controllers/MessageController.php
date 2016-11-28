<?php

class MessageController extends \Ia_Controller_Action_Abstract
{

    public function init()
    {
        $this->view->singular = 'message';
        $this->view->plural = 'messages';    
        $this->view->columns = array('id'=>'Id','subject'=>'Subject','o.first_name'=>'From: First Name','o.last_name'=>'From: Last Name','message'=>'Message');
        $this->view->relations = array('o'=>'origin_user','r'=>'recipient_user');
        $this->view->detailColumns = $this->view->columns;
        $this->view->actions = array(
            'link'=>array('label'=>'Link','url'=>'eval:$item->link','icon'=>'glyphicon glyphicon-flag'),        
            'view'=>array('label'=>'View','url'=>array('action'=>'view','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-eye-open'),        
            'delete'=>array('label'=>'Delete','url'=>array('action'=>'delete','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-trash','onclick'=>'return confirm(\'Are you sure you want to permanently delete this record?\');'),        
        );        
        $this->view->bulkActions = array(
            'archive'=>$this->actions('archive'),        
            'unarchive'=>$this->actions('unarchive'),        
            'delete'=>$this->actions('delete'),        
        );        
        $this->entity = new Ia\Entity\Message;
        //$this->createForm = new Form_MessageCreateUpdate;
        //$this->updateForm = new Form_MessageCreateUpdate;   
        if(Zend_Registry::isRegistered('auth'))
            $user = Zend_Registry::get('auth');
        parent::init();
        if($user)
            $this->_filters['r.id'] = $user->id;
    }

    public function actions($name){
        switch($name){
            case 'archive':
                return array('label'=>'Archive','url'=>array('action'=>'archive','active'=>0,'id'=>'{ids}'),'icon'=>'glyphicon glyphicon-folder-close');        
                break;
            case 'unarchive':
                return array('label'=>'Return To Inbox','url'=>array('action'=>'archive','active'=>1,'id'=>'{ids}'),'icon'=>'glyphicon glyphicon-folder-open');        
                break; 
            case 'delete':
                return array('label'=>'Delete','url'=>array('action'=>'delete','ids'=>'{ids}'),'icon'=>'glyphicon glyphicon-trash','onclick'=>'return confirm(\'Are you sure you want to permanently delete this record?\');');        
                break;                                
            default:
                return parent::actions($name);
                break;
        }
    }       

    public function emailPreferencesAction()
    {
        $error = false;
        $form = new Form_MessageEmailPreferences;
        $user = false;
        if($this->getRequest()->getParam('user') && $this->getRequest()->getParam('token')) {
            //this is not the same token as the one used to reset passwords
            $token = $this->getRequest()->getParam('token');
            $user = $this->em->getRepository('\Ia\Entity\User')->find($this->getRequest()->getParam('user'));
            if(!$user || $token != $user->getEmailPreferencesToken()){
                $user = false;
            }
        } elseif (\Zend_Registry::isRegistered('auth')){
            $user = \Zend_Registry::get('auth');
        }
        if(!$user){
            Ia_View_Helper_Alert::addAlert('You must login to continue.', 'error');
            $this->_helper->redirector->gotoUrl($this->view->url(array('module'=>'default','controller'=>'user','action'=>'login')));
        }

        $emailPreferences = $user->getEmailPreferences();
        $options = array();
        $values = array();
        foreach($emailPreferences as $key=>$data){
            $options[$key] = $data['title'];
            if($data['optin']){
                $values[] = $key;
            }
        }

        $element = new Ia_Form_Element_DynamicMultiCheckbox('email_preferences');
        $element->setLabel('Email Preferences');
        $element->setMultiOptions($options);
        $element->setOrder(10);
        $element->setDescription('Uncheck any of the above if you no longer wish to receive emails of this type. Note: some email types cannot be disabled.');
        $form->addElement($element);
        $form->setDefaults(array('email_preferences'=>$values));

        if($this->getRequest()->isPost()){
            $values = $this->getRequest()->getPost();
            if ($form->isValid($values)) {
                $userEmailPreferences = array();
                foreach(\Ia\Entity\Message::staticGetAllTypeOptions() as $key=>$data){
                    $userEmailPreferences[$key] = array('title'=>$data['title']);
                    if(!$data['optout'] || in_array($key, $values['email_preferences']))
                        $userEmailPreferences[$key]['optin'] = true;
                    else
                        $userEmailPreferences[$key]['optin'] = false;
                }
                try{
                    $user->email_preferences = serialize($userEmailPreferences);
                    $this->em->persist($user);
                    $this->em->flush();
                    Ia_View_Helper_Alert::addAlert('Email preferences updated.','success');
                    $this->_helper->redirector->gotoUrl($this->view->url(array()));
                } catch (Exception $e){
                    Ia_View_Helper_Alert::addAlert($e->getMessage(),'error');
                }
            }
        }

        /* Disable types you cannot opt out of */
        foreach(\Ia\Entity\Message::staticGetAllTypeOptions() as $key=>$data){
            if($data['optout']==false){
                $form = str_replace('id="email_preferences-'.$key.'"','id="email_preferences-'.$key.'" disabled ',$form);
            }
        }
        $this->view->form = $form;
        $this->view->user = $user;
    }
        
    public function createAction()
    {
        $this->view->form = $form = $this->createForm;
        if($this->getRequest()->isPost()){
            $values = $this->getRequest()->getPost();
          
            if ($form->isValid($values)) {
                try{
                    $recipient_user_ids = $values['recipient_user_id'];
                    unset($values['recipient_user_id']);
                    $num = 0;
                    foreach($recipient_user_ids as $recipient_user_id){
                        $newEntity = $this->entity->createEntity($values + array('recipient_user_id'=>$recipient_user_id));
                        $this->em->persist($newEntity);
                        $this->em->flush();
                        $num++;
                    }
                    Ia_View_Helper_Alert::addAlert('Message successfully delivered to '.$num.' recipients.','success');
                    $this->postCreate($newEntity);
                    $this->returnHome();
                } catch (Exception $e){
                    Ia_View_Helper_Alert::addAlert($e->getMessage(),'error');
                }
            }
        }
        $this->_handleScaffolds();            
    }
    
    public function viewMessagesAction(){
        $this->_helper->layout()->disableLayout();
        $view_message_type = $this->getRequest()->getParam('view_message_type');
        $auth = Zend_Registry::get('auth');
        $this->view->view_type = $view_message_type;
        $this->view->messages = $this->em->getRepository('\Ia\Entity\Message')->getMessagesQueryByFolder($auth, $view_message_type)
                                    ->setMaxResults(10)->getResult();
    }

    public function dismissAllAction()
    {
        try{
            $auth = Zend_Registry::get('auth');
            $dql = 'SELECT e FROM \Ia\Entity\Message e INNER JOIN e.recipient_user u WHERE u.id = :userId AND e.dismissed = :notDismissed';
            $query = $this->em->createQuery($dql);
            $query->setParameters(array('userId'=>$auth->id,'notDismissed'=>false));
            $adapter =  new Ia_Doctrine_Paginator_Adapter($query);
            $moreRecords = true;
            $perPage = 500;
            $page = 1;
            $dismissed = 0;
            while($moreRecords){
                $zend_paginator = new \Zend_Paginator($adapter);
                $zend_paginator->setItemCountPerPage($perPage)
                    ->setCurrentPageNumber($page);
                $paginator = $zend_paginator; 
                if($paginator->getCurrentItemCount()<$perPage){
                    $moreRecords = false;
                }
                foreach($paginator as $message){
                    $dismissed++;
                    $message->dismissed = true;
                    $this->em->persist($message);
                }
                $this->em->flush();
                $this->em->clear();
            }
            Ia_View_Helper_Alert::addAlert('Dismissed '.$dismissed.' unread messages.','success');
        } catch(\Exception $e) {
            Ia_View_Helper_Alert::addAlert($e->getMessage(),'error');
        }
        $this->returnReferer();
    }

    public function archiveAllAction()
    {
        try{
            $auth = Zend_Registry::get('auth');
            $dql = 'SELECT e FROM \Ia\Entity\Message e INNER JOIN e.recipient_user u WHERE u.id = :userId AND e.active = :isActive';
            $query = $this->em->createQuery($dql);
            $query->setParameters(array('userId'=>$auth->id,'isActive'=>true));
            $adapter =  new Ia_Doctrine_Paginator_Adapter($query);
            $moreRecords = true;
            $perPage = 500;
            $page = 1;
            $archived = 0;
            while($moreRecords){
                $zend_paginator = new \Zend_Paginator($adapter);
                $zend_paginator->setItemCountPerPage($perPage)
                    ->setCurrentPageNumber($page);
                $paginator = $zend_paginator; 
                if($paginator->getCurrentItemCount()<$perPage){
                    $moreRecords = false;
                }
                foreach($paginator as $message){
                    $archived++;
                    $message->active = false;
                    $this->em->persist($message);
                }
                $this->em->flush();
                $this->em->clear();
            }
            Ia_View_Helper_Alert::addAlert('Archived '.$archived.' messages.','success');
        } catch(\Exception $e) {
            Ia_View_Helper_Alert::addAlert($e->getMessage(),'error');
        }
        $this->returnReferer();
    }
    
    public function dismissMessageAction(){
        try {
            $auth = Zend_Registry::get('auth');
            $record = $this->retrieveRecord();
            $record->dismissed = 1;
            $this->em->persist($record);
            $this->em->flush();
            Ia_View_Helper_Alert::addAlert('Message dismissed', 'success');
        } catch (\Exception $e) {
            Ia_View_Helper_Alert::addAlert($e->getMessage(), 'error');
        }
        $this->_redirect($_SERVER['HTTP_REFERER']);
    }

    public function followLinkAction()
    {
        if(Zend_Registry::isRegistered('auth')){
            $record = $this->retrieveRecord();
            if($record->recipient_user->id==Zend_Registry::get('auth')->id && !$record->dismissed){
                $record->dismissed = true;
                $this->em->persist($record);
                $this->em->flush();
            }
        }
        $this->_helper->redirector->gotoUrl($record->link);
    }

    public function viewAction()
    {
        if(Zend_Registry::isRegistered('auth')){
            $record = $this->retrieveRecord();
            if($record->recipient_user->id==Zend_Registry::get('auth')->id && !$record->dismissed){
                $record->dismissed = true;
                $this->em->persist($record);
                $this->em->flush();
            }
        }
        $this->view->record = $record;
    }

    public function archiveAction()
    {
        $num = 0;
        if($this->getRequest()->getParam('id')){
            if(strpos($this->getRequest()->getParam('id'),' ')!==false){
                $ids = explode(' ',$this->getRequest()->getParam('id'));
            } else {
                $ids = array($this->getRequest()->getParam('id'));
            }
            foreach($ids as $id){
                $record = $this->view->record = $this->retrieveRecord($id,false,false);
                $record = $this->entity->updateEntity($record,array('dismissed'=>true,'active'=>$this->getRequest()->getParam('active')));
                $this->em->persist($record);
                $num++;
            }
        }
        if($num>0){
            $this->em->flush();
            switch($this->getRequest()->getParam('active')){
                case 1:
                    Ia_View_Helper_Alert::addAlert($num .' message(s) have been returned to inbox.','success');
                    break;
                default:
                    Ia_View_Helper_Alert::addAlert($num .' message(s) have been archived.','success');
                    break;
            }
        } else {
            Ia_View_Helper_Alert::addAlert('Nothing has been done.','error');
        }
        $this->_helper->redirector->gotoUrl($this->view->url(array('active'=>null,'action'=>'index','id'=>null)));        
    }

    public function indexAction()
    {
        $auth = Zend_Registry::get('auth');
        $this->useScaffolding = false;
        $paginators = array();
        foreach(\Ia\Entity\Message::getFolders() as $folder){
            $query = $this->em->getRepository('\Ia\Entity\Message')->getMessagesQueryByFolder($auth,$folder);
            $adapter =  new Ia_Doctrine_Paginator_Adapter($query);
            $zend_paginator = new \Zend_Paginator($adapter);          
            $zend_paginator->setItemCountPerPage($this->getPerPage())
                ->setCurrentPageNumber($this->getPage());
            $paginators[$folder] = clone $zend_paginator;
        }
        $this->view->paginators = $paginators;
    }    
    
}
