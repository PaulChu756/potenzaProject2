<?php
if(php_sapi_name()!=='cli')
    die('This script must be run from the command line.');

set_time_limit(0);

require_once(dirname(__FILE__) . '/../public/_env.php');

/* PUT SITE IN MAINTENANCE MODE */
if(!MAINTENANCE){
    echo 'This can only be run in maintenance mode';
    exit;
}

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Add main library and each module's library folder to the include path
require_once realpath(APPLICATION_PATH . '/../scripts/_module_paths.php');

/** Zend_Application */
require_once 'Zend/Application.php';

// Creating application
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

// Bootstrapping resources
$bootstrap = $application->bootstrap()->getBootstrap();
$bootstrap->bootstrap('Doctrine');

// Retrieve Doctrine Container resource
$container = $application->getBootstrap()->getResource('doctrine');

$createPatchesTable = 'CREATE TABLE IF NOT EXISTS `patches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patch_name` VARCHAR(50) DEFAULT NULL,
  `ran_on` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;';
$container->getEntityManager()->getConnection()->exec($createPatchesTable);

$sql = 'SELECT `patch_name` FROM `patches`';
$stmt = $container->getEntityManager()->getConnection()->prepare($sql);
$stmt->execute();
$patches = array();
$results = $stmt->fetchAll();

foreach($results as $result){
    $patches[] = $result['patch_name'];
}


$preUpdatePatches = array(); //these patches should be run BEFORE the doctrine orm update
$postUpdatePatches = array(); //these patches should be run AFTER the doctrine orm update (DEFAULT)

$preUpdatePatchesCount = 0;
$postUpdatePatchesCount = 0;

/* SET CURRENT DIRECTORY TO PATCHES */
$cwd = getcwd();

$patch_dirs = array();
$patch_dirs[] = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'patches';

if(file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules')){
  foreach(scandir(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules') as $moduleDir){
    if(is_dir(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleDir . DIRECTORY_SEPARATOR . 'patches')){
      $patch_dirs[] = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleDir . DIRECTORY_SEPARATOR . 'patches';
    }
  }
}

foreach($patch_dirs as $patch_dir){
  chdir($patch_dir);
  foreach(scandir(getcwd()) as $file){
      if(!in_array($file,$patches)){
          if($file!=='_example.php' && $file!=='run.php' && strpos($file,'.php')!==false){
              if(strpos($file,'preupdate')!==false){
                  $preUpdatePatches[] = array('path'=>getcwd(),'file'=>$file);
              } else {
                  $postUpdatePatches[] = array('path'=>getcwd(),'file'=>$file);
              }
          }
      }
  }
}

foreach($preUpdatePatches as $preUpdatePatch){
    chdir($preUpdatePatch['path']);
    echo 'Running '.$preUpdatePatch['path'].DIRECTORY_SEPARATOR.$preUpdatePatch['file'].'...'.chr(10);
    try{
       require_once($preUpdatePatch['file']);
       echo $preUpdatePatch['file'].' applied!'.chr(10);   
       $container->getEntityManager()->getConnection()->exec('INSERT INTO `patches` (`patch_name`,`ran_on`) VALUES (\''.$preUpdatePatch['file'].'\',NOW());');
    } catch(Exception $e) {
       echo 'Failed!  The following exception was thrown: '.$e->getMessage().chr(10);
       exit;
    }
}

echo 'Running schema update...';
$metadatas = $container->getEntityManager()->getMetadataFactory()->getAllMetadata();
$tool = new \Doctrine\ORM\Tools\SchemaTool($container->getEntityManager());
$tool->updateSchema($metadatas);
echo 'Schema updated!'.chr(10);

foreach($postUpdatePatches as $postUpdatePatch){
    chdir($postUpdatePatch['path']);
    echo 'Running '.$postUpdatePatch['path'].DIRECTORY_SEPARATOR.$postUpdatePatch['file'].'...'.chr(10);
    try{
       require_once($postUpdatePatch['file']);
       echo $postUpdatePatch['file'].' applied!'.chr(10);  
       $container->getEntityManager()->getConnection()->exec('INSERT INTO `patches` (`patch_name`,`ran_on`) VALUES (\''.$postUpdatePatch['file'].'\',NOW());');
    } catch(Exception $e) {
       echo 'Failed!  The following exception was thrown: '.$e->getMessage().chr(10);
       exit;
    }
}

/* REVERT CURRENT DIRECTORY */
chdir($cwd);

/* TAKE OUT OF MAINTENANCE MODE */
echo 'Complete!  Be sure to take application out of maintenance mode'.chr(10);
exit;
