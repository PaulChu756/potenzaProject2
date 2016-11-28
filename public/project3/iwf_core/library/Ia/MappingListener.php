<?php
/**
 * ADD TO CONFIG:
 * resources.doctrine.dbal.connections.default.eventListeners.loadClassMetadata = "Ia\MappingListener"
 */
namespace Ia;
use Doctrine\ORM\Event as Event;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ClassMetadataInfo;

class MappingListener
{

    public function loadClassMetadata(Event\LoadClassMetadataEventArgs $eventArgs)
    {
        $frontendOptions = array(
            'lifetime' => 3600, // cache lifetime of approximately 1 hour
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH.'/cache/' // Directory where to put the cache files
        );

        // getting a Zend_Cache_Core object
        $cache = \Zend_Cache::factory('Core',
                                 'File',
                                 $frontendOptions,
                                 $backendOptions);

        if(($discrMap=$cache->load('discrMap'))===false) {
            $discrMap = $this->_getDiscrMap();
            $cache->save($discrMap,'discrMap');            
        }
        if($discrMap){
            $metadata = $eventArgs->getClassMetadata();
            if(isset($discrMap[$metadata->getTableName()])){
                $metadata->setDiscriminatorMap($discrMap[$metadata->getTableName()]);
                $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE);
                $metadata->setDiscriminatorColumn(array('name'=>'discr'));
                $dc = \Zend_Registry::get('doctrine');
                $dc->getCacheInstance()->deleteAll();
            }
        }
    }

    protected function _getDiscrMap()
    {
        $discrMap = array();
        $application_ini = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'application.ini';
        if(realpath($application_ini)){
            $config = new \Zend_Config_Ini(realpath($application_ini), APPLICATION_ENV);
            if($config->discr_map){
                foreach($config->discr_map as $tableName=>$data){
                    if(!isset($discrMap[$tableName])){
                        $discrMap[$tableName] = array();
                    }
                    foreach($data as $discr=>$className){
                        $discrMap[$tableName][$discr] = $className;
                    }
                }
            }
            foreach(scandir(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules') as $module) {
                $module_ini = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 
                                $module . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'module.ini';
                if(realpath($module_ini)){
                    $config = new \Zend_Config_Ini(realpath($module_ini), APPLICATION_ENV);
                    if($config->discr_map){
                        foreach($config->discr_map as $tableName=>$data){
                            if(!isset($discrMap[$tableName])){
                                $discrMap[$tableName] = array();
                            }
                            foreach($data as $discr=>$className){
                                $discrMap[$tableName][$discr] = $className;
                            }
                        }
                    }
                }
            }
        }
        return $discrMap;
    }
      
}