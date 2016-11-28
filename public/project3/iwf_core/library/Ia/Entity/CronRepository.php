<?php
/*
 * http://docs.doctrine-project.org/en/latest/tutorials/getting-started.html#entity-repositories
 */

namespace Ia\Entity;

use Doctrine\ORM\EntityRepository;

class CronRepository extends EntityRepository
{

    public function findOneByResource($resource)
    {
        $resource = str_replace('\\','',$resource);
        $query = $this->getEntityManager()->createQuery('SELECT e FROM \Ia\Entity\Cron e');
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

}