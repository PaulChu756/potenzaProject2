<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $this->doctrineContainer = \Zend_Registry::get('doctrine');

        /* Initialize action controller here */
    }

    public function indexAction()
    {
        if(\Ia\Config::get('index')){
            $parts = explode('_',\Ia\Config::get('index'));
            $this->view->module = $module = $parts[0];
            $this->view->controller = $controller = $parts[1];
            $this->view->action = $action = $parts[2];
            $this->view->params = $this->getRequest()->getParams();
            if(\Ia\Config::get('redirect_index')){
                $this->_helper->redirector->gotoRoute(array('module'=>$module,'controller'=>$controller,'action'=>$action));
            }
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
    
    public function testAction()
    {
        $ws = $this->_getWorkerServers();
        echo '<pre>'.print_r($ws,1).'</pre>';
        $client = new GearmanClient();
        foreach($ws as $worker_server){
            $client->addServer($worker_server[0],$worker_server[1]);
        }
        //$gearman_tasks = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->findBy(array('last_result'=>'stalled'));
        $gearman_tasks = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->findBy(array('state'=>'submitted'));
        $gearman_tasks2 = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->findBy(array('state'=>'running'));
        $gearman_tasks3 = $this->getEntityManager()->getRepository('\Ia\Entity\GearmanTask')->findBy(array('state'=>'queued'));
        $still_running = [];

        echo '<h1>Submitted</h1>';

        foreach($gearman_tasks as $gt){
            echo '<hr />';
            echo $gt->id.' - '.$gt->job_handle.' - '.$gt->status_message.'<br />';
            $stat = $this->getStatusAcrossServers(unserialize($gt->job_handle));
            echo '<pre>'.print_r($stat,1).'</pre>';
            $queued = ($stat[0]) ? 'Yes' : 'No';
            $running = ($stat[1]) ? 'Yes' : 'No';
            echo ' / Queued: '.$queued.' / Running: '.$running.'<hr />';
            if($running == 'Yes'){
                $still_running[] = $gt->id;
            }
        }

        echo '<h1>Queued</h1>';

        foreach($gearman_tasks3 as $gt){
            echo '<hr />';
            echo $gt->id.' - '.$gt->job_handle.' - '.$gt->status_message.'<br />';
            $stat = $this->getStatusAcrossServers(unserialize($gt->job_handle));
            echo '<pre>'.print_r($stat,1).'</pre>';
            $queued = ($stat[0]) ? 'Yes' : 'No';
            $running = ($stat[1]) ? 'Yes' : 'No';
            echo ' / Queued: '.$queued.' / Running: '.$running.'<hr />';
            if($running == 'Yes'){
                $still_running[] = $gt->id;
            }
        }

        echo '<h1>Running</h1>';

        foreach($gearman_tasks2 as $gt){
            echo '<hr />';
            echo $gt->id.' - '.$gt->job_handle.' - '.$gt->status_message.'<br />';
            $stat = $this->getStatusAcrossServers(unserialize($gt->job_handle));
            echo '<pre>'.print_r($stat,1).'</pre>';
            $queued = ($stat[0]) ? 'Yes' : 'No';
            $running = ($stat[1]) ? 'Yes' : 'No';
            echo ' / Queued: '.$queued.' / Running: '.$running.'<hr />';
            if($running == 'Yes'){
                $still_running[] = $gt->id;
            }
        }

        echo '<br />Still running ('.implode(',',$still_running).')';
        //echo '<hr />';
        exit;
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

    protected $_worker_servers = [];
    
    protected function _getWorkerServers()
    {
        //return [['10.128.17.109', 4730]]; //worker1
        //return [['10.128.46.93', 4730]]; //worker2
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

}
