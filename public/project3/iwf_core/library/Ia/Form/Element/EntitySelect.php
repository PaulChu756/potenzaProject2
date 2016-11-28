<?php

class Ia_Form_Element_EntitySelect extends Zend_Form_Element_Select
{

    /*
     * Ids of entities to exclude (will be overridden if id is the current value)
     */
    protected $_excludeIds = array();
    
    /*
     * Entity used as data source for this element
     */
    protected $_entity = null;

    /*
     * Unique identifier field for entity
     */
    protected $_entityId = 'id';

    /*
     * String identifier field for entity
     */
    protected $_entityTitle = 'title';

    /*
     * String identifier blank option text (not used if empty)
     */
    protected $_blankOptionText = '';

     /**
     *
     * \Doctrine\Entity\Manager
     */
    protected $_em = null;

    /**
     * Get Doctrine Entity Manager
     * @return \Doctrine\Entity\Manager
     */    
    public function getEntityManager() {
        if($this->_em===null){
            $dc = \Zend_Registry::get('doctrine');
            $this->_em = $dc->getEntityManager();        
        }
        return $this->_em;        
    }

    /**
     * Set blank option text
     */
    function setBlankOptionText($text){
        $this->_blankOptionText = $text;
        return $this;
    }

    /*
     * Set ids to exclude
     */
    public function setExcludeIds($ids=array())
    {
        $this->_excludeIds = $ids;
        $newMultiOptions = array();
        $multiOptions = $this->getMultiOptions();
        foreach($multiOptions as $key=>$value){
            if($key==$this->getValue() || !in_array($key, $this->_excludeIds))
                $newMultiOptions[$key] = $value;
        }
        $this->setMultiOptions($newMultiOptions);
        return $this;
    }

    public function setEntityTitle($title)
    {
        $this->_entityTitle = $title;
        return $this;
    }

    /*
     * Set entity for select and populate with initial values
     */
    public function setEntity($_entity,$showInactive=false,$id=false,$title=false)
    {
        if(!is_object($_entity))
            throw new Exception('Non-object passed to setEntity in EntitySelect input');

        if($id)
            $this->_entityId = $id;

        if($title && !is_callable($title))
            $this->_entityTitle = $title;

        $this->_entity = $_entity;

        $options = array();
        if($this->_blankOptionText){
            $options[null] = $this->_blankOptionText;
        }

        $dql = "SELECT e FROM ".get_class($this->_entity)." e";

        if(!$showInactive && property_exists($this->_entity, 'active'))
            $dql .= " WHERE e.active=1";

        if(!is_callable($title))
            $dql .= " ORDER BY e.".$this->_entityTitle;

        $records = $this->getEntityManager()->createQuery($dql)->getResult();
        foreach($records as $record){
            if(!in_array($record->{$this->_entityId}, $this->_excludeIds))
                if(is_callable($title))
                    $options[$record->{$this->_entityId}] = $title($record);
                else
                    $options[$record->{$this->_entityId}] = $record->{$this->_entityTitle};
        }

        $this->setMultiOptions($options);
        return $this;
    }

    /*
     * If set value does not exist in original values, add it dynamically
     */
    public function setValue($value)
    {
        if(!in_array($value, array_keys($this->getMultiOptions()))){
            $currentOptions = $this->getMultiOptions();
            if(is_object($this->_entity)){
                $dql = "SELECT e FROM ".get_class($this->_entity)." e WHERE e.".$this->_entityId."=:id";
                $records = $this->getEntityManager()
                                ->createQuery($dql)
                                ->setParameter('id',$value)
                                ->getResult();
                if(!$records){
                    $currentOptions[$value] = 'DELETED RECORD #'.$value;
                }
                foreach($records as $record){
                    $currentOptions[$record->{$this->_entityId}] = $record->{$this->_entityTitle};
                }
            } else {
                $currentOptions[$value] = 'INACTIVE RECORD #'.$value;
            }
            $this->setMultiOptions($currentOptions);
        }
        return parent::setValue($value);
    }
	
}
