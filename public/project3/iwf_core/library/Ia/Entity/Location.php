<?php

namespace Ia\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 *
 * @ORM\Table(name="locations")
 * @ORM\Entity(repositoryClass="LocationRepository")
 * @ORM\HasLifecycleCallbacks 
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Location
{
    
    /**
     * Criteria ID per Google AdWords API
     *
     * @var integer $criteria_id
     * @ORM\Column(name="criteria_id", type="integer", nullable=false)
     * @ORM\Id
     */
    protected $criteria_id;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=60,nullable=true)
     */
    protected $name;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    protected $canonical_name;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ia\Entity\Location")
     * @ORM\JoinColumn(name="parent_criteria_id", referencedColumnName="criteria_id")
     **/
    protected $parent;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=60,nullable=true)
     */
    protected $country_code;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=60,nullable=true)
     */
    protected $target_type;
    
    /**
     *
     * @var string
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $active = true;

    /**
    * @var datetime $created_at
    *
    * @ORM\Column(type="datetime", nullable=true)
    */
    private $created_at; 

    /**
     * @var datetime $updated_at
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at; 
    
     /**
     *
     * \Doctrine\Entity\Manager
     */
    public $em = null;

    /**
     * Get Doctrine Entity Manager
     * @return \Doctrine\Entity\Manager
     */    
    public function getEntityManager() {
        if($this->em===null){
            $dc = \Zend_Registry::get('doctrine');
            $this->em = $dc->getEntityManager();        
        }
        return $this->em;        
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
        $partner = get_object_vars($this);
        return $partner;
    } 
        
    /**
     * Create an entity with the given data
     *
     * @param array $data
     * @return object 
     */
    public function createEntity(array $data)
    {    
        $metadata = $this->getEntityManager()->getClassMetadata(get_class($this));
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
        $metadata = $this->getEntityManager()->getClassMetadata(get_class($this));
        foreach($data as $property => $value){
            if(!$metadata->reflClass->hasProperty($property))
                continue;
            $metadata->setFieldValue($entity, $property, $value);
        }
        return $entity;
    }

    /**
     *
     * @ORM\PrePersist 
     */
    public function prePersist()
    {
        $this->created_at = new \DateTime;
        $this->updated_at = new \DateTime;
    }
    
    /**
     *
     * @ORM\PreUpdate 
     */
    public function preUpdate()
    {
        $this->updated_at = new \DateTime;
    }    
      
}
