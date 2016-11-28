<?php
/*
 * http://docs.doctrine-project.org/en/latest/tutorials/getting-started.html#entity-repositories
 */

namespace Ia\Entity;

use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository
{

    public function getMessagesByEmail($email=true,$limit=null)
    {
        $query = $this
                    ->getEntityManager()
                    ->createQuery('SELECT m FROM \Ia\Entity\Message m
                        WHERE m.email = :email AND m.error = :error');
        $query->setParameters(array('email'=>$email, 'error'=>false));
        if($limit!==null){
            $query->setMaxResults($limit);
        }
        return $query->getResult();
    }

    public function getMessagesQueryByFolder(\Ia\Entity\User $user,$folder,$countOnly=false)
    {
        if(!in_array($folder, \Ia\Entity\Message::getFolders())){
            throw new \Exception('Unrecognized folder `'.$folder.'`');
        }
        $select = ($countOnly) ? 'count(e.id)' : 'e';
        switch($folder){
            case 'new':
                $dql = 'SELECT '.$select.' FROM \Ia\Entity\Message e
                        WHERE e.active = :active
                        AND e.recipient_user = :user
                        AND e.dismissed = :dismissed
                        ORDER BY e.id DESC';
                $params = array('active'=>true,'user'=>$user->id,'dismissed'=>false);
                break;
            case 'inbox':
                $dql = 'SELECT '.$select.' FROM \Ia\Entity\Message e
                        WHERE e.active = :active
                        AND e.recipient_user = :user
                        ORDER BY e.id DESC';
                $params = array('active'=>true,'user'=>$user->id);            
                break;
            case 'archived':
                $dql = 'SELECT '.$select.' FROM \Ia\Entity\Message e
                        WHERE e.active = :active
                        AND e.recipient_user = :user
                        ORDER BY e.id DESC';
                $params = array('active'=>false,'user'=>$user->id);                
                break;
            case 'sent':
                $dql = 'SELECT '.$select.' FROM \Ia\Entity\Message e
                        WHERE e.origin_user = :user
                        ORDER BY e.id DESC';
                $params = array('user'=>$user->id);              
                break;
            default:
                throw new \Exception('Unrecognized folder `'.$folder.'`');
                break;
        }
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters($params);
        return $query;  
    }

    public function getActiveJsonByRecipient($active=true,\Ia\Entity\User $user)
    {
        $results = $this->getAllActive($active, $user);
        $arrResults = array();
        foreach($results as $result){
            $arrResults[$result->id] = $result->subject;
        }
        return json_encode($arrResults);
    }

    public function getAllActiveByRecipient($active=true,\Ia\Entity\User $user)
    {
        $query = $this->getEntityManager()->createQuery('
                        SELECT e FROM \Ia\Entity\Message e
                        WHERE e.active = :active
                        AND e.recipient_user = :user
                        ORDER BY e.id DESC');
        $query->setParameters(array('active'=>$active,'user'=>$user->id));   
        $results = $query->getResult();
        return $results;
    }

    public function getActiveCountByRecipient($active=true,\Ia\Entity\User $user)
    {
        $query = $this->getEntityManager()->createQuery('
                        SELECT count(e) FROM \Ia\Entity\Message e
                        WHERE e.active = :active
                        AND e.recipient_user = :user');
        $query->setParameters(array('active'=>$active,'user'=>$user->id));   
        return $query->getSingleScalarResult();
    }

}