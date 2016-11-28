<?php
class Example_ExampleController extends Ia_Controller_Action_Abstract
{

    public function init()
    {
        $this->view->singular = 'example';
        $this->view->plural = 'examples';    
        $this->view->columns = array('id'=>'Id','title'=>'Title','active'=>'Active');
        $this->view->formats = array(
                'active' => array('YesNo'),
            );
        $this->view->detailColumns = $this->view->columns;
        $this->view->actions = array(
            'view'=>$this->actions('view'),
            'edit'=>$this->actions('edit'),
            'delete'=>$this->actions('delete'),
        );        
        $this->entity = new Example\Entity\Example;
        $this->createForm = new Example_Form_Example_CreateUpdate;
        $this->updateForm = new Example_Form_Example_CreateUpdate;    
        parent::init();
        $this->addFilterWidget('activeInactive','e.active',1);
    }  
    
}