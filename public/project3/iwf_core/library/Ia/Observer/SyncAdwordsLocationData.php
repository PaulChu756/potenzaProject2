<?php
namespace Ia\Observer;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class SyncAdwordsLocationData extends ObserverAbstract implements ObserverInterface
{

    public function execute()
    {
        $script = realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . 
                           DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 
                          'sync_adwords_location_data.mysql');
        if(!$script){
            throw new \Exception('Could not locate sync script.');
        }
        $sql = file_get_contents($script);
        $this->getEntityManager()->getConnection()->exec($sql);
        return array('Successfully synced adwords location data',array());
    }

}