<?php
namespace Ia\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="cron",uniqueConstraints={@ORM\UniqueConstraint(name="resource_idx", columns={"resource"})})
 * @ORM\Entity(repositoryClass="CronRepository")
 * @ORM\HasLifecycleCallbacks
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Cron
{
    
    /**
     *
     * @var integer $id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string $next_run
     *
     * @ORM\Column(type="string",length=11,nullable=true)
     */
    private $next_run;   
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=50,nullable=true)
     */
    private $resource;

    /**
     * Magic getter
     */
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
    
}