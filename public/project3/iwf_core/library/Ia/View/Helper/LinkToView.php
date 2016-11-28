<?php

class Ia_View_Helper_LinkToView extends Zend_View_Helper_Abstract
{
    
    public function linkToView($val)
    {
        return '<a title="View '.$val.'" href="'.$this->view->url(array('action'=>'view','id'=>$this->view->record->id)).'">'.$val.'</a>';
    }

}