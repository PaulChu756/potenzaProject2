<?php
/*
 * http://docs.doctrine-project.org/en/latest/tutorials/getting-started.html#entity-repositories
 */

namespace Ia\Entity;

use Doctrine\ORM\EntityRepository;

class LocationRepository extends EntityRepository
{
    public function findMatches($str='',$target_type=null,$limit=10)
    {
        $sql = 'SELECT `criteria_id`,`canonical_name`,`target_type`
        FROM `locations` ';
        $wheres = array();
        if($str)
            $wheres[] = '`canonical_name` LIKE \'%'.$str.'%\'';
        if($target_type)
            $wheres[] = '`target_type` = \''.$target_type.'\'';
        if($wheres)
            $sql .= 'WHERE '.implode(' AND ',$wheres).' ';
        if($limit)
            $sql .= ' LIMIT '.$limit;
        return $this->getEntityManager()->getConnection()->query($sql)->fetchAll();
    }
    
    public function findByIds($ids = array())
    {
        if(count($ids)>0){
            $sql = 'SELECT `criteria_id`,`canonical_name`,`target_type`
            FROM `locations` WHERE `criteria_id` IN ('.implode(',',$ids).')';
            return $this->getEntityManager()->getConnection()->query($sql)->fetchAll();
        } else {
            return array();
        }
    }

}