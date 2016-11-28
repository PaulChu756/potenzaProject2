<?php
namespace Ia\Observer;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class CleanMessages extends ObserverAbstract implements ObserverInterface
{

    public function execute()
    {
        //90 is default
        $keep_messages_for_x_days = (\Ia\Config::get('keep_messages_for_x_days')) ? \Ia\Config::get('keep_messages_for_x_days') : 30;
        $dateTimeObj = new \DateTime;
        $dateTimeObj->sub(new \DateInterval('P'.$keep_messages_for_x_days.'D'));
        $query = $this->getEntityManager()->createQuery('
                SELECT count(e.id) FROM \Ia\Entity\Message e
                WHERE e.active = :notActive AND e.created_at < :oldestDate');
        $query->setParameters(array('notActive'=>0,'oldestDate'=>$dateTimeObj));
        $count = $query->getSingleScalarResult();
        if($count > 0){
            $sql = 'DELETE FROM `messages` WHERE active = 0 AND created_at < \''.$dateTimeObj->format('Y-m-d').'\';';
            $this->getEntityManager()->getConnection()->exec($sql);
            return $count.' messages cleaned';
        }
        return false;
    }

}