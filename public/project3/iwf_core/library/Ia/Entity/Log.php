<?php
namespace Ia\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="logs")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Log extends \Ia\Model
{
    
    /**
     *
     * @var integer $id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\PrePersist */
    public function prePersist()
    {
        $this->created = new \DateTime();
    }
    
     /**
     * @var datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    private $created;

     /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;        
    
    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $type;    
    
    /**
     *
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private $message;
    
    /**
     *
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private $data;    
            
    public function __get($property)
    {
        return $this->$property;
    }
    
    public function __set($property,$value)
    {
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
    
    public function getAllTypeOptions()
    {
        return self::staticGetAllTypeOptions();
    }
    
    public static function staticGetAllTypeOptions()
    {
        return array(
            'SUCCESS'=>'Good News',
            'INFORMATION'=>'FYI',
            'WARNING'=>'Warning',
            'IMPORTANT'=>'Critical',
        );
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
        
            if($property=='user_id'){
                $user = $this->em->find('Ia\Entity\User', $value);
                $metadata->setFieldValue($entity, 'user', $user);
                unset($user);
                continue;
            }            

            if(in_array($property,$ignore))
                continue;
        
            if(!$metadata->reflClass->hasProperty($property)){
                throw new \Zend_Exception("'$property' doesn't exist on '$class'");
            }

            $metadata->setFieldValue($entity, $property, $value);
        }

        return $entity;
    }

    /** @ORM\PrePersist */
    public function createdTimestamp()
    {
        $this->created = new \DateTime;
        $this->updated = new \DateTime;
    }
    
    /** @ORM\PreUpdate */
    public function updatedTimestamp()
    {
        $this->updated = new \DateTime;
    }  
    
}
