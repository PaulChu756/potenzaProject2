<?php
namespace Ia\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="gearman_tasks")
 * @ORM\Entity(repositoryClass="GearmanTaskRepository")
 * @ORM\HasLifecycleCallbacks
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class GearmanTask
{
    
    const RESULT_SUCCESS = 'success';
    
    const RESULT_EXCEPTION = 'exception';
    
    const RESULT_FAIL = 'fail';
    
    const RESULT_STALLED = 'stalled';
    
    const STATE_WAITING = 'waiting';
    
    const STATE_SUBMITTED = 'submitted';
    
    const STATE_QUEUED = 'queued';
    
    const STATE_RUNNING = 'running';
    
    const STATE_COMPLETE = 'complete';
    
    const TYPE_SCHEDULED = 'scheduled';
    
    const TYPE_ONETIME = 'onetime';
    
    const PRIORITY_LOW = 'low';
    
    const PRIORITY_NORMAL = 'normal';
    
    const PRIORITY_HIGH = 'high';
    
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
     * @var integer $stall_flag
     * @ORM\Column(name="stall_flag", type="integer", nullable=true)
     */
    private $stall_flag = 0;    
    
    /**
     *
     * @var integer $percent_complete
     * @ORM\Column(name="percent_complete", type="integer", nullable=false)
     */
    private $percent_complete = 0;    
    
    /**
     * @var string $status
     * 
     * The realtime status message sent by the running job
     *
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $status_message;    
    
    /**
     * @var string $type
     *
     * @ORM\Column(type="string",length=11,nullable=true)
     */
    private $type = self::TYPE_SCHEDULED;  
    
    /**
     * @var string $type
     *
     * @ORM\Column(type="string",length=11,nullable=true)
     */
    private $priority = self::PRIORITY_NORMAL;
    
    /**
     * @var string $next_run
     *
     * @ORM\Column(type="string",length=11,nullable=true)
     */
    private $next_run;
    
    /**
     * @var string $next_run
     *
     * @ORM\Column(type="string",length=25,nullable=true)
     */
    private $schedule;    
    
    /**
     * @ORM\Column(name="registered_on", type="datetime", nullable=true)
     */
    private $registered_on;
    
    /**
     * @ORM\Column(name="current_start", type="datetime", nullable=true)
     */
    private $current_start;
    
    /**
     * @ORM\Column(name="current_finish", type="datetime", nullable=true)
     */
    private $current_finish;
    
    /**
     * @ORM\Column(name="last_success_finish", type="datetime", nullable=true)
     */
    private $last_success_finish;
    
    /**
     * @var string $last_result
     *
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $last_result;    
    
    /**
     * @var string $state
     *
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $state = self::STATE_WAITING;  
    
    /**
     * @var string $status
     *
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $status;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $resource;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $job_name;    
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $job_url;    
    
    /**
     * @var string $params
     *
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $params;
    
    /**
     * @var string $params
     *
     * @ORM\Column(type="text",nullable=true)
     */
    private $job_handle;    

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="recipient_user_id", referencedColumnName="id", nullable=true)
     */
    private $request_user;    

    /**
     * Magic getter
     */
    public function __get($property)
    {
        return $this->$property;
    }
    
    /**
     * @param $task \GearmanTask | false
     * @param $result
     * @return \Ia\Entity\GearmanTask
     */
    public function setComplete($task = false, $result)
    {
        $this->job_handle = null;
        $this->stall_flag = 0;
        $this->setCurrentFinish(new \DateTime,$result);
        
        if($task)
            $this->status = $task->data();
        else
            $this->status = $result;

        if($this->type==\Ia\Entity\GearmanTask::TYPE_SCHEDULED)
            $this->next_run = \Ia\Observer\tdCron::getNextOccurrence($this->schedule,(time() + 60));
        
        if($result==\Ia\Entity\GearmanTask::RESULT_SUCCESS){
            $this->percent_complete = 100;
        }
        switch($this->type){
            case self::TYPE_ONETIME:
                $this->setState(\Ia\Entity\GearmanTask::STATE_COMPLETE);
                break;
            case self::TYPE_SCHEDULED:
                $this->setState(\Ia\Entity\GearmanTask::STATE_WAITING);
                break;
        }
        return $this;
    }
    
    /**
     * @return \Ia\Entity\GearmanTask
     */
    public function createTask($type = self::TYPE_ONETIME, 
                               $priority = self::PRIORITY_NORMAL, 
                               $schedule=null, 
                               \Ia\Observer\ObserverInterface $resource, 
                               $params=array(),
                               $user=null,
                               $job_name=null,
                               $job_url=null){
        if($this->id){
            throw new \Exception('An active record already exists for this object.');
        }
        if($schedule!==null && $type!==self::TYPE_SCHEDULED){
            throw new \Exception('A schedule was provided but type is not `'.self::TYPE_SCHEDULED.'`.');
        }
        $this->job_name = $job_name;
        $this->job_url = null;
        $this->setType($type);
        $this->setPriority($priority);
        if($schedule !== null){
            $this->schedule = $schedule;
            $this->next_run = \Ia\Observer\tdCron::getNextOccurrence($schedule);
        }
        $this->resource = get_class($resource);
        $this->params = serialize($params);       
        $this->registered_on = new \DateTime;
        if($user!==null && $user instanceof \Ia\Entity\User){
            $this->request_user = $user;
        }
        return $this;        
    }
    
    public function __set($property,$value)
    {
        if($property=='state')
            throw new \Exception('State cannot be set directly. Use setState()');
        $this->$property = $value;
    }  
    
    public function setType($type)
    {
        if(!in_array($type,array(
            self::TYPE_SCHEDULED,self::TYPE_ONETIME))){
            throw new \Exception('Disallowed type: `'.$type.'`.');
        }
        $this->type = $type;
        return $this;
    }
    
    public function setPriority($priority)
    {
        if(!in_array($priority,array(
            self::PRIORITY_LOW,self::PRIORITY_NORMAL,self::PRIORITY_HIGH))){
            throw new \Exception('Disallowed priority: `'.$priority.'`.');
        }
        $this->priority = $priority;
        return $this;
    }
    
    public function setState($state)
    {
        if(!in_array($state,array(
            self::STATE_WAITING,self::STATE_QUEUED,self::STATE_RUNNING,self::STATE_SUBMITTED,self::STATE_COMPLETE))){
            throw new \Exception('Disallowed state: `'.$state.'`.');
        }
        //when submitting, clear out last statue message
        if($state==self::STATE_SUBMITTED)
            $this->status_message = 'Submitted '.date('Y-m-d g:ia');
        $this->state = $state;
        return $this;
    }
    
    public function setCurrentStart(\DateTime $dateTime){
        $this->current_start = $dateTime;
        $this->current_finish = null;
        return $this;
    }
    
    public function setCurrentFinish(\DateTime $dateTime, $result){
        $this->current_finish = $dateTime;
        $this->last_result = $result;
        //last finish is last successful run
        if($result==self::RESULT_SUCCESS)
            $this->last_success_finish = $dateTime;
        return $this;
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