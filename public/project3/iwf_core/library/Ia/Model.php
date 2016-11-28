<?php
namespace Ia;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Model
{
    public $em;
    public $dc;
    public $st;

    public function __construct(){
        $this->dc = \Zend_Registry::get('doctrine');
        $this->em = $this->dc->getEntityManager();
        $this->st = new \Doctrine\ORM\Tools\SchemaTool($this->em);    
    }
    
    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        $this->$property = $value;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function toArray() {
        return get_object_vars($this);
    }
    
    /**
     * Create an entity with the given data
     *
     * @param array $data
     * @return object 
     */
    public function createEntity(array $data)
    {    
        $metadata = $this->em->getClassMetadata(get_class($this));
        if(isset($data['id'])){
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        }
        $entity = $metadata->newInstance();
        return $this->updateEntity($entity,$data);
    } 

    /**
     * Update an entity with the given data
     *
     * @param array $data
     * @return object 
     */
    public function updateEntity($entity, array $data)
    {    
        $metadata = $this->em->getClassMetadata(get_class($this));

        $ignore = array('submit');
        foreach($data as $property => $value){
            if(in_array($property,$ignore))
                continue;
        
            if(!$metadata->reflClass->hasProperty($property)){
                throw new \Zend_Exception("'$property' doesn't exist on '$class'");
            }

            $metadata->setFieldValue($entity, $property, $value);
        }

        return $entity;
    }  
    
}