<?php

class Ia_View_Helper_EntityImage extends Zend_View_Helper_Abstract
{

    protected $_column = null;

    protected $_width = 200;

    protected $_fullPath = null;

    public function setColumn($column)
    {
        $this->_column = $column;
        return $this;
    }

    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }    

    public function setFullPath($fullPath)
    {
        $this->_fullPath = $fullPath;
        return $this;
    }
        
    public function entityImage($value,$key,$entity=null,$column=null)
    {
        if($entity!==null){
            $this->setEntity($entity);
        }
        if($column!==null){
            $this->setColumn($column);
        }
        if(method_exists($this->getEntity(), 'photoUrl')){
            return '<img src="'.$this->_fullPath.$this->getEntity()->photoUrl($this->view,$this->_width).'" width="'.$this->_width.'" />';
        } elseif(method_exists($this->getEntity(), 'getPathToImage'))
            return '<img src="'.$this->_fullPath.$this->getEntity()->getPathToImage(null,$this->_column).'" />';
        if($value && $this->_column)
            return '<img src="'.$this->_fullPath.$this->view->fullPathToThumbUrl($this->getEntity()->getAttachmentFilePath().$this->_column.DIRECTORY_SEPARATOR.$value, true, $this->_width).'" />';
        elseif($value){
            return '<img src="'.$this->_fullPath.$this->view->fullPathToThumbUrl($this->getEntity()->getAttachmentFilePath($this->getEntity()->id,$key).DIRECTORY_SEPARATOR.$value, true, $this->_width).'" />';
        } else {
            return null;
        }
    }

    public function getEntity()
    {
        if($this->_entity===null){
            return $this->view->record;
        } else {
            return $this->_entity;
        }
    }

    public function setEntity($entity)
    {
        $this->_entity = $entity;
        return $this;
    }

}