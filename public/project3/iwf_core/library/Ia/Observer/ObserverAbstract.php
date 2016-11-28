<?php
namespace Ia\Observer;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
abstract class ObserverAbstract
{

	protected $_em = null;

	protected $_dc = null;

    protected $_view = null;

    protected $_request = null;
    
    protected $_gearman_job = null;
    
    protected $_percent_complete = 0;
    
    protected $_loggers = array();
    
    protected $_messages = array();
    
    public function getLogger($task_id)
    {
        if(!$task_id)
            throw new \Exception('Task id is required to get logger.');
        if(!isset($this->_loggers[$task_id])){
            $log_dir = APPLICATION_PATH.DIRECTORY_SEPARATOR.'..'.
                DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'crons';
            if(!file_exists($log_dir)){
                mkdir($log_dir);
            }
            if(!file_exists($log_dir.'/'.$task_id.'.log')){
                touch($log_dir.'/'.$task_id.'.log');
            }
            $logger = new \Zend_Log();
            $writer = new \Zend_Log_Writer_Stream($log_dir.'/'.$task_id.'.log');
            $logger->addWriter($writer);  
            $this->_loggers[$task_id] = $logger;
        }
        return $this->_loggers[$task_id];
    }
    
    public function setGearmanJob(\GearmanJob $job)
    {
        $this->_gearman_job = $job;
        return $this;
    }
            
    public function setPercentComplete($percent_complete)
    {
        $this->_percent_complete = $percent_complete;
        if($this->_gearman_job){
            $this->_gearman_job->sendStatus($percent_complete,100);
        }
    }
    
    public function setStatusMessage($message)
    {
        $this->_messages[] = $message;
        if($this->_gearman_job){
            $this->getLogger($this->_gearman_job->unique())->log($message, \Zend_Log::INFO);
            $this->_gearman_job->sendData(serialize($message));
        }
    }
    
    public function getMessages()
    {
        return $this->_messages;
    }

	public function getEntityManager()
	{
		if($this->_dc === null){
	        $this->_dc = \Zend_Registry::get('doctrine');
		}
		if($this->_em == null){
	        $this->_em = $this->_dc->getEntityManager();
		}
        if(!$this->_em->isOpen()){
            $this->_em = $this->_dc->resetEntityManager();
        }
        return $this->_em;
	}

    public function getView()
    {
        if($this->_view===null){
            $this->_view = \Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');    
        }
        return $this->_view;
    }    

    public function setRequest($request)
    {
        $this->_request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->_request;
    }

}