<?php
if(isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'],'nginx')!==false){
    //nginx patch
    $uri = urldecode($_SERVER['REQUEST_URI']);
    $parts = explode('?',$uri);
    if($parts && isset($parts[1])){
        $subparts = explode('&',$parts[1]);
        if($subparts){
            foreach($subparts as $subpart){
                $paramPair = explode('=',$subpart);
                if(strpos($paramPair[0],'[]')!==false){
                    $arrayKey = str_replace('[]','',$paramPair[0]);
                    if(!isset($_REQUEST[$arrayKey]))
                        $_REQUEST[$arrayKey] = array();
                    if(!isset($_GET[$arrayKey]))
                        $_GET[$arrayKey] = array();
                    $_REQUEST[$arrayKey][] = $paramPair[1];
                    $_GET[$arrayKey][] = $paramPair[1];
                } else {
                    $_REQUEST[$paramPair[0]] = $paramPair[1];
                    $_GET[$paramPair[0]] = $paramPair[1]; 
                }
            }
        }
    }
}

require_once('_env.php');

//define paths

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
    
// Define path to application directory
defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../public'));
    
// Add main library and each module's library folder to the include path
require_once realpath(APPLICATION_PATH . '/../scripts/_module_paths.php');
    
/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap();
$options = $application->getOptions();

if (PHP_SAPI == 'cli')
{
    global $argv;
    $url = $argv[1];
    $parts = explode('/',$url);
    $domain = $parts[2];
    $_SERVER = array();
    $_SERVER['HTTP_HOST'] = $domain;
    $frontController = $application->getBootstrap()->getResource('frontController');
    $frontController->throwExceptions(true);
    $frontController->setRequest(
        new Zend_Controller_Request_Http(
            Zend_Uri::factory($url)
        )
    );
}

\Zend_Registry::set('time_start',microtime(true));
if(isset($options['sessions']['persistent']) && $options['sessions']['persistent']){
    $length = (isset($options['sessions']['length'])) ? $options['sessions']['length'] : (365 * 24 * 60 * 60);
    ini_set('session.gc_maxlifetime', $length);
    session_set_cookie_params($length);
}

$application->run();