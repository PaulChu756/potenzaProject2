<?php
/*
 * http://docs.doctrine-project.org/en/latest/tutorials/getting-started.html#entity-repositories
 */

namespace Ia\Entity;

use Doctrine\ORM\EntityRepository;

class GearmanTaskRepository extends EntityRepository
{

    public function findOneByResource($resource)
    {
        $resource = str_replace('\\','',$resource);
        $query = $this->getEntityManager()->createQuery('SELECT e FROM \Ia\Entity\GearmanTask e');
        $results = $query->getResult();
        if($results){
            foreach($results as $result){
                if($resource==str_replace('\\','',$result->resource)){
                    return $result;
                }
            }
        }
        return false;
    }
    
    /**
     * Return all one-time waiting tasks or scheduled waiting tasks ready to run
     */
    public function getTasksToRun()
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT g 
                FROM \Ia\Entity\GearmanTask g
                WHERE g.state = :stateWaiting
                AND (g.type = :typeOnetime OR (g.type = :typeScheduled AND g.next_run < :timestamp))');
        $query->setParameters(array(
            'stateWaiting'=>\Ia\Entity\GearmanTask::STATE_WAITING, 
            'typeOnetime'=>\Ia\Entity\GearmanTask::TYPE_ONETIME,
            'typeScheduled'=>\Ia\Entity\GearmanTask::TYPE_SCHEDULED, 
            'timestamp'=>time(), 
        ));
        return $query->getResult();
    }
    
    /**
     * Return all one-time waiting tasks or scheduled waiting tasks ready to run
     */
    public function getPendingByResourceType($resource,$type)
    {
        $completeState = ($type==\Ia\Entity\GearmanTask::TYPE_ONETIME) ? 
                            \Ia\Entity\GearmanTask::STATE_COMPLETE : 
                            \Ia\Entity\GearmanTask::STATE_WAITING;
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT g 
                FROM \Ia\Entity\GearmanTask g
                WHERE g.state != :completeState
                AND g.type = :type
                AND g.resource = :resource');
        $query->setParameters(array(
            'completeState'=>$completeState, 
            'type'=>$type,
            'resource'=>$resource 
        ));
        return $query->getResult();
    }
    
    /**
     * Return all one-time waiting tasks or scheduled waiting tasks ready to run
     */
    public function getAllPending()
    {
        $completeState = \Ia\Entity\GearmanTask::STATE_COMPLETE;
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT g 
                FROM \Ia\Entity\GearmanTask g
                WHERE g.state != :completeState');
        $query->setParameters(array(
            'completeState'=>$completeState, 
        ));
        return $query->getResult();
    }    
    
    /**
     * Return all tasks which have been submitted to gearman but have not yet been marked as completed (or failed)
     */
    public function getSubmittedTasks()
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT g 
                FROM \Ia\Entity\GearmanTask g
                WHERE g.state = :submittedState
                OR g.state = :queuedState
                OR g.state = :runningState');
        $query->setParameters(array(
            'submittedState'=>\Ia\Entity\GearmanTask::STATE_SUBMITTED, 
            'queuedState'   =>\Ia\Entity\GearmanTask::STATE_QUEUED,
            'runningState'  =>\Ia\Entity\GearmanTask::STATE_RUNNING 
        ));
        return $query->getResult();
    }
    
    /**
     * Return all open tasks by user
     */
    public function getAllByRequestUser(\Ia\Entity\User $user, $max_age_sec = 300)
    {
        $max_age = date('Y-m-d H:i:s',(time() - $max_age_sec));
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT g 
                FROM \Ia\Entity\GearmanTask g
                WHERE g.request_user = :user
                AND (g.state != :stateComplete OR g.current_finish > :maxAge)
                ');
        $query->setParameters(array(
            'user'          => $user,
            'stateComplete' => \Ia\Entity\GearmanTask::STATE_COMPLETE,
            'maxAge'        => $max_age
        ));
        return $query->getResult();
    }
    
    /**
     * Return all open tasks by resource
     */
    public function getAllByResource($resource, $max_age_sec = 300)
    {
        $max_age = date('Y-m-d H:i:s',(time() - $max_age_sec));
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT g 
                FROM \Ia\Entity\GearmanTask g
                WHERE g.resource = :resource
                AND (g.state != :stateComplete OR g.current_finish > :maxAge)
                ');
        $query->setParameters(array(
            'resource'      => $resource,
            'stateComplete' => \Ia\Entity\GearmanTask::STATE_COMPLETE,
            'maxAge'        => $max_age
        ));
        return $query->getResult();
    }

}