<?php
ini_set('memory_limit','1G');
error_reporting(E_ALL);
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

    
// Add main library and each module's library folder to the include path
require_once realpath(APPLICATION_PATH . '/../scripts/_module_paths.php');

// Ensure library/ is on include_path
/*set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));*/

require_once 'Zend/Loader/Autoloader.php';
require_once 'Zend/Application.php';
require_once('ModelTestCase.php');
require_once('Zend/Test/PHPUnit/ControllerTestCase.php');
require_once('ControllerTestCase.php');

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

Zend_Loader_Autoloader::getInstance();
