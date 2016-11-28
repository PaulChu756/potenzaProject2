<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    public static function handle($errno, $errstr, $errfile, $errline)
    {
        if ( E_RECOVERABLE_ERROR===$errno ) {
            throw new Exception($errstr . " in $errfile:$errline". $errno);
        }
    }

    protected function _initErrorHandling()
    {
        set_error_handler(array(__CLASS__, 'handle'));
    }

    protected function _initAppDate()
    {
        $session = new Zend_Session_Namespace('app_session');
        if(isset($session->app_date)){
            $app_date = $session->app_date;
        } else {
            $app_date = date('Y-m-d',strtotime('Yesterday'));
        }
        Zend_Registry::set('app_date',$app_date);
    }

    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }

    protected function _initPhpThumb()
    {
        require_once(APPLICATION_PATH . '/../library/phpThumb/phpthumb.class.php');
        $phpThumb = new phpThumb();
        $phpThumb->setParameter('config_document_root', APPLICATION_PATH.'/../library/phpThumb');
        $phpThumb->setParameter('config_cache_directory', APPLICATION_PATH.'/cache/');
        Zend_Registry::set('phpThumb',$phpThumb);
    }

        
    public function _initAutoloader()
    {
        // Create an resource autoloader component
        $autoloader = new Zend_Loader_Autoloader_Resource(array(
            'basePath'    => APPLICATION_PATH,
            'namespace' => ''
        ));

        // Add some resources types
        $autoloader->addResourceTypes(array(
            'forms'   => array(
                'path'           => 'forms',
                'namespace' => 'Form'    
            ),
            'models' => array(
                'path'           => 'models',
                'namespace' => 'Model'    
            ),
        ));

        // Return to bootstrap resource registry
        return $autoloader;
    }    
    
    public function _initMail()
    {
        $options = $this->getApplication()->getOptions();
                
		$server = isset($options['resources']['mail']['transport']) ?
			$options['resources']['mail']['transport'] : false;
		
		if ($server){
			$transport = new Zend_Mail_Transport_Smtp($server['host'],$server);
			Zend_Mail::setDefaultTransport($transport);	
			Zend_Registry::set('default_transport', $transport);
		}	    
    
    }
    
	protected function _initCache(){

		$frontendOptions = array(
			'lifetime' => 2678400, // cache lifetime of approximately 1 month
			'automatic_serialization' => true
		);

		$backendOptions = array(
			'cache_dir' => dirname(__FILE__).'/cache/' // Directory where to put the cache files
		);

		// getting a Zend_Cache_Core object
		$cache = Zend_Cache::factory('Core',
								 'File',
								 $frontendOptions,
								 $backendOptions);
		$registry = Zend_Registry::getInstance();
        $registry->set("cache", $cache);	
	}

	protected function _initLocks(){
        $lockFolder = dirname(__FILE__).'/locks/';
        if(!file_exists($lockFolder)){
            mkdir($lockFolder);
            if(!file_exists($lockFolder)){
                echo 'Could not create locks folder at: '.$lockFolder;
                exit;
            }
        }                    
		$lock = new Ia\Lock($lockFolder);
		$registry = Zend_Registry::getInstance();
        $registry::set('lock',$lock);
	}    
    
}