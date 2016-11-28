<?php
/*
 * http://docs.doctrine-project.org/en/latest/tutorials/getting-started.html#entity-repositories
 */

namespace Ia\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{

    public function getActiveJson($userIds=array(),$role=null)
    {
        return json_encode($this->getActiveArray(false,$userIds,$role));
    }

    public function getActiveEmailsJson($userIds=array(),$role=null)
    {
        return json_encode($this->getActiveEmailsArray(false,$userIds,$role));
    }

    public function getActiveEmailsArray($userIds=array(),$role=null)
    {
        $results = $this->getAllActive($userIds,$role);
        $arrResults = array();
        foreach($results as $result){
            $arrResults[$result->id] = $result->email_address;
        }  
        return $arrResults;
    }

    public function getActiveArray($extended=false,$userIds=array(),$role=null)
    {
        $results = $this->getAllActive($userIds,$role);
        $arrResults = array();
        foreach($results as $result){
            if($extended)
                $arrResults[$result->id] = $result->first_name.' '.$result->last_name.' <'.$result->email_address.'>';
            else
                $arrResults[$result->id] = $result->first_name.' '.$result->last_name;
        }  
        return $arrResults;
    }

    public function getAllActive($userIds=array(),$role=null)
    {
        $dql = 'SELECT e FROM \Ia\Entity\User e WHERE';
        $params = array('active'=>true);
        if(is_array($userIds) && count($userIds) > 0){
            $dql .= ' (e.active = :active OR e.id IN (:userIds))';
            $params['userIds'] = $userIds;
        } else {
            $dql .= ' e.active = :active';
        }
        if($role){
            if(is_array($role)){
                $dql .= ' AND e.role IN (:role)';
                $params['role'] = $role;
            } else {
                $dql .= ' AND e.role = :role';
                $params['role'] = $role;
            }
        }
        $dql .= ' AND e.deletedAt IS NULL';
        $dql .= ' ORDER BY e.last_name';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters($params);   
        $results = $query->getResult();
        return $results;
    }

}