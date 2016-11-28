<?php
namespace Ia\Observer;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class CleanLogs extends ObserverAbstract implements ObserverInterface
{

    public function execute()
    {
        //90 is default
        $keep_logs_for_x_days = (\Ia\Config::get('keep_logs_for_x_days')) ? \Ia\Config::get('keep_logs_for_x_days') : 30;
        $dateTimeObj = new \DateTime;
        $dateTimeObj->sub(new \DateInterval('P'.$keep_logs_for_x_days.'D'));
        $query = $this->getEntityManager()->createQuery('
                SELECT count(e.id) FROM \Ia\Entity\Log e
                WHERE e.created < :oldestDate');
        $query->setParameters(array('oldestDate'=>$dateTimeObj));
        $count = $query->getSingleScalarResult();
        if($count > 0){
            $sql = 'DELETE FROM `logs` WHERE created < \''.$dateTimeObj->format('Y-m-d').'\';';
            $this->getEntityManager()->getConnection()->exec($sql);
            return $count.' logs cleaned';
        }
        return false;
    }

}