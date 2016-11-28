<?php

class LogController extends \Ia_Controller_Action_Abstract
{

    protected $_order = 'id DESC';

    public function init()
    {
        $this->view->singular = 'log';
        $this->view->plural = 'logs';    
        $this->view->columns = array('id'=>'Id','created'=>'Date/Time','message'=>'Message','u.first_name'=>'First Name','u.last_name'=>'Last Name','u.email_address'=>'Email',);
        $this->view->relations = array('u'=>'user');
        $this->view->detailColumns = $this->view->columns;
        $this->view->actions = array(
            'view'=>array('label'=>'View','url'=>array('action'=>'view','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-eye-open'),        
        );        
        $this->entity = new Ia\Entity\Log;
        parent::init();
    }  

    public function viewAction()
    {
        $this->useScaffolding = false;
        return parent::viewAction();
    }
    
}