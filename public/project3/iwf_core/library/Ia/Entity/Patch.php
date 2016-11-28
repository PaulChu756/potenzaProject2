<?php
namespace Ia\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="patches")
 * @ORM\Entity
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Patch extends \Ia\Model
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
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $patch_name;
    
    /**
    * @var datetime $ran_on
    *
    * @ORM\Column(type="datetime")
    */
    private $ran_on;      
    
    
}