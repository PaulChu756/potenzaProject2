<?php

class CronController extends Zend_Controller_Action
{

    protected $_dc = null;

    protected $_em = null;
    
    public function init()
    {
        //Gedmo extensions
        if(\Ia\Config::get('resources/doctrine/orm/entityManagers/default/filters/soft-deleteable'))
            $this->getEntityManager()->getFilters()->enable('soft-deleteable');    
    }
    
    protected $_worker_servers = [];
    
    protected function _getWorkerServers()
    {
        if(count($this->_worker_servers)==0){
            if(\Ia\Config::get('gearman_worker/servers')){
                foreach(\Ia\Config::get('gearman_worker/servers') as $worker_server){
                    $parts = explode(':',$worker_server);
                    if(count($parts)==2){
                        $this->_worker_servers[] = $parts;
                    }
                }
            }
            if(count($this->_worker_servers)==0){
                $this->_worker_servers[] = ['127.0.0.1', 4730];
            }
        }
        shuffle($this->_worker_servers);
        return $this->_worker_servers;
    }

    /**
     * $taskOrJob \GearmanTask | \GearmanJob
     */
    protected function _getInternalUniqueId($taskOrJob)
    {
        $taskOrJobUnique = $taskOrJob->unique();
        ////error_log('unique: '.$taskOrJob->unique());
        if(strpos($taskOrJobUnique,'_')!==false){
            $parts = explode('_',$taskOrJobUnique);
            return $parts[0];
        }
        return $taskOrJobUnique;
    }
    
    /**
     * Make sure all scheduled jobs configured in .ini files are registered in the database
     */
    protected function _registerConfiguredGearmanTasks()
    {
        $gearman = (\Ia\Config::get('gearman')) ? \Ia\Config::get('gearman') : array();
        foreach (\Ia\Config::get('modules') as $moduleConfig){
            if(isset($moduleConfig['gearman'])){
                $gearman = array_merge($moduleConfig['gearman'],$gearman);
            }
        }
        $newTasks = false;
        foreach($gearman as $resource=>$schedule){
            $gearmanTaskObj = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->findOneBy(array('resource'=>$resource,'type'=>\Ia\Entity\GearmanTask::TYPE_SCHEDULED));
            if(!$gearmanTaskObj){
                $reflectionClass = new ReflectionClass($resource);
                if($reflectionClass->IsInstantiable() && $schedule){
                    //must be a valid object and have a valid schedule
                    $resourceInstance = new $resource;
                    $gearmanTaskObj = new \Ia\Entity\GearmanTask;
                    $gearmanTaskObj->createTask(\Ia\Entity\GearmanTask::TYPE_SCHEDULED,
                               \Ia\Entity\GearmanTask::PRIORITY_NORMAL, 
                               $schedule, 
                               $resourceInstance, 
                               array());
                    $this->getEntityManager()->persist($gearmanTaskObj);
                    $newTasks = true;
                }
            } elseif ($gearmanTaskObj->schedule != $schedule){
                if($schedule){
                    $gearmanTaskObj->schedule = $schedule;
                    $this->getEntityManager()->persist($gearmanTaskObj);
                } else {
                    //remove if schedule is set to 0
                    $this->getEntityManager()->remove($gearmanTaskObj);
                }
                $newTasks = true;
            }
        }
        if($newTasks){
            $this->getEntityManager()->flush();      
        }    
        return;
    }
    
    
    public function gearmanRunOnetimeAction()
    {
        try{
            if($this->getRequest()->getParam('id')){
                $task = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->find($this->getRequest()->getParam('id'));
                if($task){
                    
                    if($task->type==\Ia\Entity\GearmanTask::TYPE_SCHEDULED){
                        $this->getEntityManager()->detach($task);
                        $task->stall_flag = 0;    
                        $task->percent_complete = 0;    
                        $task->status_message = null;
                        $task->next_run = null;
                        $task->schedule = null;  
                        $task->registered_on = new \DateTime;
                        $task->current_start = null;
                        $task->current_finish = null;
                        $task->last_success_finish = null;
                        $task->last_result = null;
                        $task->setState(\Ia\Entity\GearmanTask::STATE_WAITING);  
                        $task->status = null;
                        $task->job_handle = null;    
                        $task->id = null;
                    }
                    
                    if(Zend_Registry::isRegistered('auth'))
                        $task->request_user = Zend_Registry::get('auth');

                    $task->schedule = null;
                    $task->type = \Ia\Entity\GearmanTask::TYPE_ONETIME;
                    $task->priority = \Ia\Entity\GearmanTask::PRIORITY_HIGH;
                    $this->getEntityManager()->persist($task);
                    $this->getEntityManager()->flush();
                    
                    \Ia_View_Helper_Alert::addAlert('One-time task queued and attached to current user.','success');

                } else {
                    throw new \Exception('Could not locate task with id #'.$this->getRequest()->getParam('id'));
                }
            }
        } catch (\Exception $e) {
            \Ia_View_Helper_Alert::addAlert('Error running task: '.$e->getMessage(),'error');
            if($_SERVER['HTTP_REFERER']==$_SERVER['REQUEST_URI'])
                $this->_redirect('/cron/gearman-monitor');
            else
                $this->_redirect($_SERVER['HTTP_REFERER']);
        }
        if($_SERVER['HTTP_REFERER']==$_SERVER['REQUEST_URI'])
            $this->_redirect('/cron/gearman-monitor');
        else
            $this->_redirect($_SERVER['HTTP_REFERER']);
    }
    
    /** 
     * Delete gearman task
     */ 
    public function gearmanTaskDeleteAction()
    {
        try{
            if($this->getRequest()->getParam('id')){
                $task = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->find($this->getRequest()->getParam('id'));
                if($task){
                    $this->getEntityManager()->remove($task);
                    $this->getEntityManager()->flush();
                    \Ia_View_Helper_Alert::addAlert('Task has been removed','success');
                    \Ia_View_Helper_Alert::exitToJson();
                }
            }
        } catch (\Exception $e) {
            \Ia_View_Helper_Alert::addAlert('Error removing task: '.$e->getMessage(),'error');
            \Ia_View_Helper_Alert::exitToJson();
        }
        \Ia_View_Helper_Alert::addAlert('Unable to locate task to remove.','error');
        \Ia_View_Helper_Alert::exitToJson();
    }
    
    /**
     * Monitoring GUI
     */
    public function gearmanMonitorAction()
    {
        if($this->getRequest()->getParam('format')=='json'){
            $tasks = array();
            if($this->getRequest()->getParam('user_id')){
                $gearmanTasks = array();
                $user = $this->getEntityManager()->getRepository('\Ia\Entity\User')->find($this->getRequest()->getParam('user_id'));
                if($user){
                    $gearmanTasks = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->getAllByRequestUser($user);
                }
            } elseif($this->getRequest()->getParam('resource')) {
                $resource = $this->getRequest()->getParam('resource');
                $gearmanTasks = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->getAllByResource($resource);            
            } else {
                $gearmanTasks = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->getAllPending();
            }
            foreach($gearmanTasks as $gearmanTask){
                $params = unserialize($gearmanTask->params);
                $params_str = '';
                if(is_array($params)){
                    foreach($params as $key=>$value){
                        $params_str .= $key.': '.$value.chr(10);
                    }    
                }
                $tasks[] = array(
                    'id' => $gearmanTask->id,
                    'job_name' => $gearmanTask->job_name,
                    'job_url' => $gearmanTask->job_url,
                    'registered_on' => $gearmanTask->registered_on->format('m/d g:ia'),
                    'job_handle' => $gearmanTask->job_handle,
                    'stall_flag' => $gearmanTask->stall_flag,
                    'percent_complete' => $gearmanTask->percent_complete,
					'state' => $gearmanTask->state,
					'status' => unserialize($gearmanTask->status),
                    'type' => $gearmanTask->type,
                    'status_message' => $gearmanTask->status_message,
                    'priority' => $gearmanTask->priority,
                    'next_run' => ($gearmanTask->next_run) ? date('m/d g:ia',$gearmanTask->next_run) : null,
                    'schedule' => $gearmanTask->schedule,
                    'current_start' => ($gearmanTask->current_start) ? $gearmanTask->current_start->format('m/d g:ia') : null,
                    'current_finish' => ($gearmanTask->current_finish) ? $gearmanTask->current_finish->format('m/d g:ia') : null,
                    'last_success_finish' => ($gearmanTask->last_success_finish) ? $gearmanTask->last_success_finish->format('m/d g:ia') : null,
                    'last_result' => $gearmanTask->last_result,
                    'resource' => $gearmanTask->resource,
                    'params' => $params_str
                );
            }
            $this->_helper->json($tasks);            
        }
    }
    
    /**
     * Gearman callback which runs when a task is created, and keeps watching until it starts running
     */
    public function _gearmanTaskCreated(\GearmanTask $task)
    {
        //error_log('_gearmanTaskCreated'.$task->unique());
        $client = new GearmanClient();
        foreach($this->_getWorkerServers() as $worker_server){
            $client->addServer($worker_server[0],$worker_server[1]);
        }
        $job_not_running = true;
        $pass = 1;
        $max_passes = 300;
        while($job_not_running){
            $gearmanTaskObj = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->find($this->_getInternalUniqueId($task));
            if($pass > $max_passes){
                $gearmanTaskObj->status_message = 'Could not get job handle after max passes expended: '.$max_passes.'.';
                $this->getEntityManager()->persist($gearmanTaskObj);
                $this->getEntityManager()->flush();
                $job_not_running = false;
            } elseif(isset($this->_jobsEnded[$this->_getInternalUniqueId($task)])) {
                //$gearmanTaskObj->status_message = 'Job ended.';
                $this->getEntityManager()->persist($gearmanTaskObj);
                $this->getEntityManager()->flush();
                $job_not_running = false;                
            } else {
                $job_handle = $task->jobHandle();   
                /**
                 * Store the handle as soon as we have it.
                 */
                if($gearmanTaskObj->job_handle != serialize($job_handle)){
                    $gearmanTaskObj->job_handle = serialize($job_handle);
                    //$gearmanTaskObj->status_message = 'Got job handle.';
                    $this->getEntityManager()->persist($gearmanTaskObj);
                    $this->getEntityManager()->flush();
                } 
                $stat = $this->getStatusAcrossServers($job_handle);
                $queued = ($stat[0]);
                $running = ($stat[1]);
                if($queued && !$running){
                    if($gearmanTaskObj->state != \Ia\Entity\GearmanTask::STATE_QUEUED){
                        //$gearmanTaskObj->status_message = 'Job queued.';
                        $gearmanTaskObj->setState(\Ia\Entity\GearmanTask::STATE_QUEUED);
                        $this->getEntityManager()->persist($gearmanTaskObj);
                        $this->getEntityManager()->flush();
                    }
                } elseif($job_handle || ($queued && $running)) {
                    //job is running, this function can now exit
                    if($gearmanTaskObj->state != \Ia\Entity\GearmanTask::STATE_RUNNING){
                        //$gearmanTaskObj->status_message = 'Job running.';
                        $gearmanTaskObj->setState(\Ia\Entity\GearmanTask::STATE_RUNNING);
                        $gearmanTaskObj->percent_complete = 0;
                        $gearmanTaskObj->setCurrentStart(new \DateTime);
                        $this->getEntityManager()->persist($gearmanTaskObj);
                        $this->getEntityManager()->flush();
                    }
                    $job_not_running = false;
                }
                sleep(1);
                $pass++;
                //if($pass % 10 == 0)
                    //error_log('pass '.$pass.': '.$task->unique());
            }
        }
    }     
    
    /**
     * Callback that fires when status changes (percent completed)
     */
    public function _gearmanTaskStatus(\GearmanTask $task)
    {
        //error_log('_gearmanTaskStatus'.$task->unique());
        $gearmanTaskObj = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->find($this->_getInternalUniqueId($task));
        $gearmanTaskObj->percent_complete = $task->taskNumerator();
        $this->getEntityManager()->persist($gearmanTaskObj);
        $this->getEntityManager()->flush();
    }
    
    /**
     * Callback that fires when workload (partial update, status_message) sent
     */
    public function _gearmanTaskData(\GearmanTask $task)
    {
        //error_log('_gearmanTaskData'.$task->unique());
        $gearmanTaskObj = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->find($this->_getInternalUniqueId($task));
        $gearmanTaskObj->status_message = unserialize($task->data());
        $this->getEntityManager()->persist($gearmanTaskObj);
        $this->getEntityManager()->flush();
    }    
    
    protected $_jobsEnded = array();
    
    /**
     * Generic function for ending a task - fired by a callback
     */
    protected function _gearmanTaskEnded(\GearmanTask $task, $result)
    {
        //error_log('_gearmanTaskEnded'.$task->unique());
        if(!isset($this->_jobsEnded[$this->_getInternalUniqueId($task)]))
            $this->_jobsEnded[$this->_getInternalUniqueId($task)] = $result;
        $gearmanTaskObj = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->find($this->_getInternalUniqueId($task));
        $gearmanTaskObj->setComplete($task, $this->_jobsEnded[$this->_getInternalUniqueId($task)]);
        $this->getEntityManager()->persist($gearmanTaskObj);
        $this->getEntityManager()->flush();
    }
    
    /**
     * Callback that fires when tasks ends (good, bad or ugly)
     */
    public function _gearmanTaskComplete(\GearmanTask $task)
    {
        //error_log('_gearmanTaskComplete'.$task->unique());
        if(!isset($this->_jobsEnded[$this->_getInternalUniqueId($task)])){
            $this->_jobsEnded[$this->_getInternalUniqueId($task)] = $result;
            $return_code = $task->returnCode();
            $this->_gearmanTaskEnded($task, \Ia\Entity\GearmanTask::RESULT_SUCCESS);
        }
        return GEARMAN_SUCCESS;
    }
    
    public function _gearmanTaskException(\GearmanTask $task)
    {
        //error_log('_gearmanTaskException'.$task->unique());
        if(!isset($this->_jobsEnded[$this->_getInternalUniqueId($task)])){
            $this->_jobsEnded[$this->_getInternalUniqueId($task)] = $result;
            $return_code = $task->returnCode();
            $this->_gearmanTaskEnded($task, \Ia\Entity\GearmanTask::RESULT_EXCEPTION);
        }
        return GEARMAN_EXCEPTION;
    }
    
    public function _gearmanTaskFail(\GearmanTask $task)
    {
        //error_log('_gearmanTaskFail'.$task->unique());
        if(!isset($this->_jobsEnded[$this->_getInternalUniqueId($task)])){
            $this->_jobsEnded[$this->_getInternalUniqueId($task)] = $result;
            $return_code = $task->returnCode();
            $this->_gearmanTaskEnded($task, \Ia\Entity\GearmanTask::RESULT_FAIL);
        }
        return GEARMAN_FAIL;            
    }  

    /**
     * Due to what I believe to be a bug in Gearman_Client::jobStatus
     */
    public function getStatusAcrossServers($job_handle){
        $stat = [0,0];
        foreach($this->_getWorkerServers() as $worker_server){
            $client = new GearmanClient();
            $client->addServer($worker_server[0],$worker_server[1]);
            $this_server_stat = $client->jobStatus($job_handle);
            $stat[0] += $this_server_stat[0];
            $stat[1] += $this_server_stat[1];
        }
        return $stat;
    }
    
    /** 
     * Check up on tasks submitted by other processes.
     * If we get a good update on state, update it.
     * If we think it has stalled, increment the stall_flag until it reaches 3. Then mark as stalled.
     */ 
    protected function _checkSubmittedTasks(\GearmanClient $client)
    {
        $gearman_tasks = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->getSubmittedTasks();
        foreach($gearman_tasks as $gearman_task){
            $update = false;
            $job_handle = unserialize($gearman_task->job_handle);
            $stat = $this->getStatusAcrossServers($job_handle);
            $queued = ($stat[0]);
            $running = ($stat[1]);         
            switch($gearman_task->state){
                case \Ia\Entity\GearmanTask::STATE_SUBMITTED:
                    if($queued) {
                        //submitted > queued
                        $gearman_task->setState(\Ia\Entity\GearmanTask::STATE_QUEUED);
                        $update = true;
                    } elseif ($running) {
                        ///submitted > running
                        $gearman_task->setState(\Ia\Entity\GearmanTask::STATE_RUNNING);
                        $update = true;
                    } elseif (!$queued && !$running) {
                        //submitted but not queued and not running
                        $gearman_task->stall_flag = intval($gearman_task->stall_flag) + 1;
                        if($gearman_task->stall_flag > 3){
                            $gearman_task->setComplete(false, \Ia\Entity\GearmanTask::RESULT_STALLED);
                        }
                    }
                    break;
                case \Ia\Entity\GearmanTask::STATE_QUEUED:
                    if(!$queued) {
                        //queued > not queued. stalled?
                        $gearman_task->stall_flag = intval($gearman_task->stall_flag) + 1;
                        if($gearman_task->stall_flag > 3){
                            $gearman_task->setComplete(false, \Ia\Entity\GearmanTask::RESULT_STALLED);
                        }
                        $update = true;
                    } elseif ($running) {
                        //queued > running
                        $gearman_task->setState(\Ia\Entity\GearmanTask::STATE_RUNNING);
                        $update = true;
                    }
                    break;
                case \Ia\Entity\GearmanTask::STATE_RUNNING:
                    if(!$queued || !$running) {
                        //running > not queued or not running
                        $gearman_task->stall_flag = intval($gearman_task->stall_flag) + 1;
                        if($gearman_task->stall_flag > 3){
                            $gearman_task->setComplete(false, \Ia\Entity\GearmanTask::RESULT_STALLED);
                        }
                        $update = true;
                    }
                    break;
            }
            if($update){
                $this->getEntityManager()->persist($gearman_task);
                $this->getEntityManager()->flush();
            }
        }
    }
    
    /**
     * Queue any tasks that need to be run by gearman worker. This should be on a cron job.
     */
    public function gearmanClientAction()
    {                
        /*if(APPLICATION_ENV != 'production'){
            echo '<p>Gearman client only enabled in production environments. However you may use the following 
            links to run the jobs manually:</p><ul>';
            $gearman = (\Ia\Config::get('gearman')) ? \Ia\Config::get('gearman') : array();
            foreach (\Ia\Config::get('modules') as $moduleConfig){
                if(isset($moduleConfig['gearman'])){
                    $gearman = array_merge($moduleConfig['gearman'],$gearman);
                }
            }
            foreach($gearman as $name=>$schedule){
                $className = $name;
                if(substr($name, 0, 1) == '\\'){
                    $name = substr($name,1);
                }
                $name = str_replace('\\', '_', $name);
                echo '<li><a href="'.$this->view->url(array('action'=>'execute-resource','resource'=>$name)).
                        '" target="_blank">'.$className.'</a></li>';
            }
            echo '</ul>';
            exit;
        }*/
        
        $client = new GearmanClient();
        foreach($this->_getWorkerServers() as $worker_server){
            $client->addServer($worker_server[0],$worker_server[1]);
        }
          
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $this->_checkSubmittedTasks($client);
        /**
         * Make sure all scheduled jobs configured in .ini files are registered in the database
         */
        $this->_registerConfiguredGearmanTasks();

        $gearman_tasks = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->getTasksToRun();
        if(count($gearman_tasks)>0){
            /**
             * Register callbacks
             */
            $client->setStatusCallback(array($this,'_gearmanTaskStatus'));
            $client->setDataCallback(array($this,'_gearmanTaskData'));
            $client->setCreatedCallback(array($this,'_gearmanTaskCreated'));
            $client->setCompleteCallback(array($this,'_gearmanTaskComplete'));
            $client->setExceptionCallback(array($this,'_gearmanTaskException'));
            $client->setFailCallback(array($this,'_gearmanTaskFail'));
            foreach($gearman_tasks as $gearman_task){
                if($gearman_task->priority == \Ia\Entity\GearmanTask::PRIORITY_HIGH 
                    || APPLICATION_ENV == 'production'){
                    $gearman_task->setState(\Ia\Entity\GearmanTask::STATE_SUBMITTED);
                    $workload = serialize(array($gearman_task->resource,unserialize($gearman_task->params)));
                    $unique_id = ($gearman_task->type==\Ia\Entity\GearmanTask::TYPE_SCHEDULED) ? $gearman_task->id.'_'.$gearman_task->next_run : $gearman_task->id;
                    //error_log('adding task '.$unique_id);
                    switch($gearman_task->priority){
                        case \Ia\Entity\GearmanTask::PRIORITY_HIGH:
                            $client->addTaskHigh("run_observer", $workload, null, $unique_id); 
                            break;
                        case \Ia\Entity\GearmanTask::PRIORITY_LOW:
                            $client->addTaskLow("run_observer", $workload, null, $unique_id); 
                            break;
                        default:
                            $client->addTask("run_observer", $workload, null, $unique_id); 
                            break;
                    }
                    $this->getEntityManager()->persist($gearman_task);
                    $this->getEntityManager()->flush();
                }    
            }
            $client->runTasks();
        } 
    }
    
    /**
     * Start gearman worker
     */
    public function gearmanWorkerAction()
    {                
        set_error_handler([$this,'errHandle']);
        register_shutdown_function([$this,'fatalErrorShutdownHandler']);
        
        $this->_start_version = filemtime(realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..'));
        $this->_worker_start_time = time();
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $worker= new \GearmanWorker();
        foreach($this->_getWorkerServers() as $worker_server){
            $worker->addServer($worker_server[0],$worker_server[1]);
        }
        $worker->addFunction("run_observer", array($this,"run_observer"));
        while ($worker->work());
    }
    
    /**
     * The timestamp of the current deployment, as of the "birth" of this worker
     */
    protected $_start_version = null;
    
    /**
     * The current timestamp when the worker started
     */
    protected $_worker_start_time = null;
    
    function errHandle($errNo, $errStr, $errFile, $errLine) {
        if($errNo==E_ERROR){
            //don't sweat the small stuff. 1 is passed by fatalErrorShutdownHandler
            $msg = $errNo.': '.$errStr.' in '.$errFile.' on line '.$errLine;
            if(!in_array($this->_getInternalUniqueId($this->_gearman_job),$this->_fatal_error_job_ids)){
                $this->_fatal_error_job_ids[] = $this->_getInternalUniqueId($this->_gearman_job);
                $this->_gearman_job->sendException(serialize(array($msg,array())));
            }
            $this->gearmanWorkerAction();
        }
        return true;
    }
    
    function fatalErrorShutdownHandler()
    {
        $last_error = error_get_last();
        if ($last_error['type'] === E_ERROR) {
            // fatal error
            $this->errHandle(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
        }
    }
    
    protected $_gearman_job = null;
    
    protected $_fatal_error_job_ids = [];
    
    /**
     * Gearman worker callback - executes and observer
     */
    public function run_observer($job)
    {
        //error_log('run_observer: '.$job->unique());
        
        $version_file = realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..');
        clearstatcache(false, $version_file);
        $current_version = filemtime($version_file);
        if($this->_start_version != $current_version){
            //error_log('version changed, commit suicide');
            //version changed, commit suicide
            exit;
        }
        $max_age = (60 * 60); //one hour
        $age = time() - $this->_worker_start_time;
        if($age > $max_age){
            //older than max age, commit suicide
            exit;
        }
        
        //do not run if we already know this job is fatally failing
        if(!in_array($this->_getInternalUniqueId($job),$this->_fatal_error_job_ids)){
            $this->_gearman_job = $job;        

            try {
                $workload = unserialize($job->workload());

                if(!is_array($workload)){
                    throw new \Exception('Workload not properly formatted. array(observer,params)');
                }
                $observerClass = $workload[0];
                if(!class_exists($observerClass)){
                    throw new \Exception($observerClass.' does not exist.');
                }
                $observer = new $observerClass;
                $requestParams = (isset($workload[1]) && is_array($workload[1])) ? $workload[1] : array();
                $requestClone = clone $this->getRequest();
                foreach($requestParams as $key=>$value){
                    $requestClone->setParam($key,$value);
                }
                $observer->setRequest($requestClone);
                $observer->setGearmanJob($job);
                $message = $observer->execute();
                
                //error_log('complete: '.$job->unique());
                //error_log($message);
                
                $job->sendComplete(serialize($message));

                //this could possibly fix our memory leak issues
                $this->getEntityManager()->clear();

            } catch (\Exception $e) {
                $job->sendException(serialize(array($e->getMessage(),array())));
            }
        } else {
            return false;
        }
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

    protected function _getLockTagFromClassName($className)
    {
        if(substr($className, 0) !== '\\'){
            $className = '\\\\'.$className;
        }
        return 'cron_resource_'.md5($className);
    }

    public function executeResourceAction()
    {

        $lock = \Zend_Registry::get('lock');

        \Ia\MemoryLimit::setMax();
        set_time_limit(0);       

        try{
            $class = $this->getRequest()->getParam('resource');
            if(!$class){
                throw new Exception('Resource is required.');
            }
            $class = str_replace('_','\\',$class);
            $observerInstance = new $class;

            $lockTag = $this->_getLockTagFromClassName(get_class($observerInstance));
            $lock->setTag($lockTag);
            $lock->getLock(5); //will throw exception if cannot get it

            $observerInstance->setRequest($this->getRequest());
            $message = $observerInstance->execute();
            if(is_array($message))
                echo $message[0].'<br /><pre>'.print_r($message[1],1).'</pre>';
            else
                echo $message;

            $lock->releaseLock();

        } catch (Exception $e){ 
            echo 'Error: '.$e->getMessage();
        }
        echo '<div>DONE</div>';
        exit;
    }

    public function reRegisterCronsAction()
    {
        $cron = $this->_getCrons();

        $num = 0;

        foreach($cron as $resource=>$schedule){
            $cronObj = $this->getEntityManager()->getRepository('\Ia\Entity\Cron')->findOneByResource($resource);
            if($cronObj){
                $cronObj->next_run = \Ia\Observer\tdCron::getNextOccurrence($schedule);
                $this->getEntityManager()->persist($cronObj);
                $num++;
            }
        }

        $this->getEntityManager()->flush();

        Ia_View_Helper_Alert::addAlert($num.' cron jobs re-registered','success');
        if($_SERVER['HTTP_REFERER']==$_SERVER['REQUEST_URI'])
            $this->_redirect('/');
        else
            $this->_redirect($_SERVER['HTTP_REFERER']);
    } 


    protected function _getCrons()
    {
        $cron = (\Ia\Config::get('cron')) ? \Ia\Config::get('cron') : array();
        foreach (\Ia\Config::get('modules') as $moduleConfig){
            if(isset($moduleConfig['cron'])){
                $cron = array_merge($moduleConfig['cron'],$cron);
            }
        }
        return $cron;
    }
    
    public function indexAction() {

        \Ia\MemoryLimit::setMax();
        set_time_limit(0);
        
        if(APPLICATION_ENV != 'production'){
            echo '<p>CRON only enabled in production environments. However you may use the following 
            links to run the jobs manually:</p><ul>';
            $cron = $this->_getCrons();
            foreach($cron as $name=>$schedule){
                $className = $name;
                if(substr($name, 0, 1) == '\\'){
                    $name = substr($name,1);
                }
                $name = str_replace('\\', '_', $name);
                echo '<li><a href="'.$this->view->url(array('action'=>'execute-resource','resource'=>$name)).
                        '" target="_blank">'.$className.'</a></li>';
            }
            echo '</ul>';
            exit;
        }

        $lock = \Zend_Registry::get('lock');

        $log_prefix = '[CRON : '.$_SERVER['REMOTE_ADDR'].'/'.getmypid().'] ';

        //debug
        //\Ia\Log::write($log_prefix.' starting...',null,null,'SUCCESS');

        try{
            $this->_helper->viewRenderer->setNoRender(true);
            $cron = $this->_getCrons();
            
            //debug
            //\Ia\Log::write($log_prefix.' '.print_r($cron,1),null,null,'SUCCESS');
            
            foreach($cron as $resource=>$schedule){
                $cronObj = $this->getEntityManager()->getRepository('\Ia\Entity\Cron')->findOneByResource($resource);
                if(!$cronObj){
                    \Ia\Log::write($log_prefix.' Resource not found "'.$resource.'" - registering',null,null,'SUCCESS');
                    //register object in the database for the first time - we wait to run it.
                    $cronObj = new \Ia\Entity\Cron;
                    $cronObj->resource = $resource;
                    $cronObj->next_run = \Ia\Observer\tdCron::getNextOccurrence($schedule);
                    $this->getEntityManager()->persist($cronObj);
                    $this->getEntityManager()->flush();      
                } else {
                    //we do nothing until we have the lock
                    //get lock
                    $lockTag = $this->_getLockTagFromClassName($resource);
                    $lock->setTag($lockTag);

                    if($this->getRequest()->getParam('debug')) {
                        echo '<h1>'.$resource.'</h1>';
                        echo 'Lock tag `'.$lockTag.'`, lock file: '.md5($lockTag).' / '.$lock->lockFile.'<br />';
                        echo 'Lock File Timestamp: '.date('Y-m-d H:i:s',filemtime($lock->lockFile)).'<br />';
                    }
                    try{
                        $lock->getLock(5);
                        $gotLock = true;
                    } catch (\Exception $e) {
                        if($this->getRequest()->getParam('debug')) {
                            echo $e->getMessage();
                        }
                        $gotLock = false;
                    }
                    if($gotLock){
                        if($this->getRequest()->getParam('debug')){
                            echo 'Got lock for '.$resource.'<br />';
                        }
                        $cronObj = $this->getEntityManager()->getRepository('\Ia\Entity\Cron')->findOneByResource($resource);
                        if($cronObj->next_run <= time()){
                            $observerInstance = new $resource;
                            $observerInstance->setRequest($this->getRequest());
                            try{
                                if($this->getRequest()->getParam('debug')){
                                    echo 'Not executing, in debug mode.<br />';
                                } else {
                                    $message = $observerInstance->execute();
                                }
                                if(is_array($message))
                                    \Ia\Log::write($log_prefix.' ['.$resource.'] '.$message[0],serialize($message[1]),null,'SUCCESS');
                                elseif($message)
                                    \Ia\Log::write($log_prefix.' ['.$resource.'] '.$message,null,null,'SUCCESS');
                            } catch(Exception $e) {
                                \Ia\Log::write($log_prefix.' ['.$resource.'] '.$e->getMessage(),null,null,'ERROR');
                            }                                   
                            //bump reference time by 60 seconds to avoid duplicating jobs
                            $cronObj->next_run = \Ia\Observer\tdCron::getNextOccurrence($schedule,(time() + 60));

                            //debug
                            //\Ia\Log::write($log_prefix.' ['.$resource.'] Next run '.date('Y-m-d H:i:s',$cronObj->next_run));
                            //\Ia\Log::write($log_prefix.' ['.$resource.'] Updating id #'.$cronObj->id);
                            //\Ia\Log::write($log_prefix.' ['.$resource.'] Updating '.get_class($cronObj));

                            if($this->getRequest()->getParam('debug')){
                                echo 'Not moving schedule forward, in debug mode.<br />';
                            } else {
                                $this->getEntityManager()->persist($cronObj);
                                $this->getEntityManager()->flush();
                            }

                            //debug
                            //\Ia\Log::write($log_prefix.' ['.$resource.'] UPDATED id #'.$cronObj->id);
                        } else {
                            $notReadyMsg = $log_prefix.' ['.$resource.'] Not ready to run.  Next run: '.date('Y-m-d H:i:s',$cronObj->next_run);
                            if($this->getRequest()->getParam('debug')){
                                echo $notReadyMsg.'<br />';
                            }                            
                            //debug
                            //\Ia\Log::write($notReadyMsg,null,null,'SUCCESS');
                        }
                        $lock->releaseLock();
                        if($this->getRequest()->getParam('debug')){
                            echo 'Released lock for '.$resource.'<br />';
                        }
                    } elseif($this->getRequest()->getParam('debug')) {
                        echo 'Could not acquire lock for '.$resource.'<br />';
                    }
                }
            }            
        } catch(Exception $e) {
            \Ia\Log::write($log_prefix.' [FATAL ERROR] '.$e->getMessage(),null,null,'ERROR');
        } 

        //debug
        //\Ia\Log::write($log_prefix.' shutting down.',null,null,'SUCCESS');

        if($this->getRequest()->getParam('redirect')){
            \Ia_View_Helper_Alert::addAlert('Cron ran successfully.','success');
            if(!(isset($_SERVER['HTTP_REFERER'])) || $_SERVER['HTTP_REFERER']==$_SERVER['REQUEST_URI'])
                $this->_redirect('/');
            else
                $this->_redirect($_SERVER['HTTP_REFERER']);
        }        
        exit;   
    }
}
