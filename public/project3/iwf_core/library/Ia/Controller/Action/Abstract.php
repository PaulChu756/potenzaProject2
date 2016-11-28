<?php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

abstract class Ia_Controller_Action_Abstract extends Zend_Controller_Action
{
    protected $_session = null;
    protected $_page = 1;
    protected $_perPage = null;
    protected $_filters = array();
    protected $_filterWidgets = array();
    protected $_order = '';
    
    public $noPaginate = false;
        
    /**
     *
     * @var \Bisna\Application\Container\DoctrineContainer
     */
    protected $doctrineContainer;

    public function getEntityManager()
    {
        if($this->dc === null){
            $this->dc = \Zend_Registry::get('doctrine');
        }
        if($this->em == null){
            $this->em = $this->dc->getEntityManager();
        }
        if(!$this->em->isOpen()){
            $this->em = $this->dc->resetEntityManager();
        }
        return $this->em;
    }    

    public function getCurrentRole()
    {
        $role = 'guest';
        if(\Zend_Registry::isRegistered('auth')){
            $user = \Zend_Registry::get('auth');
            if($user->role){
                $role = $user->role;
            }
        }
        return $role;
    }
    
    public function addFilterWidget($class,$column,$default){
        if($column!==null){ //null indicates a filter that does not directly interact with column
            if(!array_key_exists($column,$this->getFilters())){
                $filters = array_merge(array($column=>$default),$this->getFilters());
                $this->setState(array('filters'=>$filters));
            } else {
                $default = $this->getFilters($column);
            }
        }
        $this->_filterWidgets[][$class] = array($column,$default);
        $this->view->filterWidgets = $this->_filterWidgets;
    }

    public function removeFilterWidget($class){
        foreach($this->_filterWidgets as $key=>$widgets){
            foreach($widgets as $keyClass=>$data){
                if($class==$keyClass){
                    unset($this->_filterWidgets[$key][$keyClass]);
                }
            }
        }
        $this->view->filterWidgets = $this->_filterWidgets;
    }    

    public function resetFilterWidgets()
    {
        foreach($this->_filterWidgets as $key=>$widgets){
            foreach($widgets as $class=>$widget){
                $column = $widget[0];
                $default = $widget[1];
                if(!array_key_exists($column,$this->getFilters())){
                    $filters = array_merge(array($column=>$default),$this->getFilters());
                    $this->setState(array('filters'=>$filters));
                } else {
                    $default = $this->getFilters($column);
                }
                $this->_filterWidgets[$key][$class] = array($column,$default);
            }
        }
        $this->view->filterWidgets = $this->_filterWidgets;
    }
    
    public function getPage()
    {
        return $this->_page;
    }
    
    public function getPerPage()
    {
        return $this->_perPage;
    }
    
    public function getFilters($key=null)
    {
        if($key!==null)
            if(isset($this->_filters[$key]))
                return $this->_filters[$key];
            else
                return null;
        else
            return $this->_filters;
    }
    
    public function getOrder()
    {
        return $this->_order;
    }

    public function getSessionSalt()
    {
        $cache = Zend_Registry::get('cache');
        if(($salt=$cache->load('session_salt'))===false) {
            $salt = 'salt'.rand(100000,999999);
            $cache->save($salt,'session_salt');            
        }
        return $salt;
    }
    
    public function getSession()
    {        
        if($this->_session==null){
            
            $this->_session = new Zend_Session_Namespace($this->getSessionSalt().get_class($this));   
        }
        return $this->_session;
    }

    /**
     * Anchor actions are actions we want to include in the "return stack."  In other words, where we return to after completing an action
     */
    public function getAnchorActions()
    {
        return array('index');
    }

    public function getReturnPath()
    {
        //no special formats
        if($this->getRequest()->getParam('format') || $this->getRequest()->getParam('noReturn'))
            return;
        
        //skip msgs view url
        if($this->getRequest()->getParam('controller') == "message" && $this->getRequest()->getParam('action') == "view-messages")
            return;

        $params = $this->getRequest()->getParams();
        if(array_key_exists('returnHome',$params)){
            $this->returnHome();
        }
                
        $iwf_return_path = new Zend_Session_Namespace('iwf_return_paths');
        if(!isset($iwf_return_path->routes))
            $iwf_return_path->routes = array();
        
        if(in_array($this->getRequest()->getParam('action'), $this->getAnchorActions()))
        {
            if(sizeof($iwf_return_path->routes)>0)
            {
                if($iwf_return_path->routes[max(array_keys($iwf_return_path->routes))]!==$this->view->url())
                {
                    $iwf_return_path->routes[] = $this->view->url();
                }
            } else {
                $iwf_return_path->routes[] = $this->view->url();
            }
        }
        if(sizeof($iwf_return_path->routes)>5)
        {
            array_shift($iwf_return_path->routes);
        }
    }
        
    public function setState($state){
        $this->_page = (isset($state['page'])) ? $state['page'] : $this->_page;
        $this->_perPage = (isset($state['perPage'])) ? $state['perPage'] : $this->_perPage;
        $this->_filters = (isset($state['filters'])) ? $state['filters'] : $this->_filters;
        $this->_order = (isset($state['order'])) ? $state['order'] : $this->_order;
        
        if(!$this->getRequest()->getParam('noSave')){
            $this->getSession()->page = $this->view->page = $this->_page;
            $this->getSession()->perPage = $this->view->perPage = $this->_perPage;
            $this->getSession()->filters = $this->view->filters = $this->_filters;    
            $this->getSession()->order = $this->view->order = $this->_order;    
        }
    }

    public function resetFilters()
    {
        $this->getSession()->searchFilter = null;
        $this->getSession()->filters = array();
        $this->getSession()->perPage = $this->_perPage;
        $this->getSession()->order = null;
    }
    
    public function getState()
    {
        if($this->getRequest()->getParam('resetFilters') && $this->getRequest()->getParam('resetFilters')==1){
            $this->resetFilters();
            $this->_helper->redirector->gotoUrl($this->view->url(array('resetFilters'=>null)));
        }
    
        $this->_page = (isset($this->getSession()->page)) ? $this->getSession()->page : $this->_page;
        
        if($this->noPaginate || ($this->getRequest()->getParam('noPaginate') && $this->getRequest()->getParam('noPaginate')==1))
            $this->_perPage = -1;
        else
            $this->_perPage = (isset($this->getSession()->perPage)) ? $this->getSession()->perPage : $this->_perPage;

        $this->_filters = (isset($this->getSession()->filters)) ? $this->getSession()->filters : $this->_filters;
        $this->_order = (isset($this->getSession()->order)) ? $this->getSession()->order : $this->_order;
        
        $filters = array();
        if($this->getRequest()->getParam('filters')){
            $filters = $this->getRequest()->getParam('filters');
        } else {
            /* This offers us a way to pass filters in url. e.g. http://etiquette.acadiancontractors.com/customer/job/index/filter_c.id/2 */
            foreach($this->getRequest()->getParams() as $key=>$value){
                if(strpos($key,'filter_')!==false){
                    $filters[str_replace('filter_','',$key)] = $value;
                }
            }
        }
        
        if($this->getRequest()->getParam('page'))
            $this->_page = $this->getRequest()->getParam('page');
        if($this->getRequest()->getParam('perPage'))
            $this->_perPage = $this->getRequest()->getParam('perPage');        
        if(sizeof($filters)>0){
            $this->_filters = array_merge($this->_filters,$filters);
        }
        if($this->getRequest()->getParam('order'))
            $this->_order = $this->getRequest()->getParam('order');

        if(!$this->getRequest()->getParam('noSave')){
            $this->getSession()->page = $this->view->page = $this->_page;
            $this->getSession()->perPage = $this->view->perPage = $this->_perPage;
            $this->getSession()->filters = $this->view->filters = $this->_filters;
            $this->getSession()->order = $this->view->order = $this->_order;
        }
    }

    public function init(){

        $this->useScaffolding = true;

        if($this->_perPage===null){
            if(\Ia\Config::get('scaffolding/per_page')!==false){
                $this->_perPage = \Ia\Config::get('scaffolding/per_page');
            } else {
                $this->_perPage = 10;
            }
        }

        $this->view->controller = $this;

        if(!isset($this->view->formatters))
            $this->view->formatters = array();
            
        if(!isset($this->view->noJoin))
            $this->view->noJoin = array();
            
        if(!isset($this->view->bulkActions))
            $this->view->bulkActions = array();
            
        $this->view->params = $this->getRequest()->getParams();
    
        $this->em = $this->getEntityManager();

        //Gedmo extensions
        if(\Ia\Config::get('resources/doctrine/orm/entityManagers/default/filters/soft-deleteable'))
            $this->em->getFilters()->enable('soft-deleteable');

        if(Zend_Registry::isRegistered('auth'))
            $this->user = Zend_Registry::get('auth');
        $this->getState();        
        $this->loadAssets();
        
        $this->_initActionContexts();

        if($this->getRequest()->getParam('format')=='excel'){
            if(\Ia\Config::get('max_memory_limit'))
                ini_set('memory_limit', \Ia\Config::get('max_memory_limit')); //allows for download of very large excel files
            else
                ini_set('memory_limit', '256M'); //allows for download of very large excel files
            set_time_limit(0);
        } elseif($this->getRequest()->getParam('printer-friendly') && !$this->getRequest()->getParam('format')){
            $this->_helper->layout->setLayout('printer');
        } elseif($this->getCurrentRole() && \Ia\Config::get('role_layouts/'.$this->getCurrentRole())){
            $this->_helper->layout->setLayout(\Ia\Config::get('role_layouts/'.$this->getCurrentRole()));
        }

        $this->scaffolds = array('view','index','create','update','delete','rest');

        $this->getReturnPath();

        $indexActions = $this->getIndexActions();
        foreach($indexActions as $key=>$indexAction){
            if(
                $indexAction['request']['module']==$this->getRequest()->getParam('module') && 
                $indexAction['request']['controller']==$this->getRequest()->getParam('controller') && 
                $indexAction['request']['action']==$this->getRequest()->getParam('action')
              ){
                unset($indexActions[$key]);
            }
        }
        $this->view->indexActions = $indexActions;

        return parent::init();
    }

    public function getIndexActions()
    {
        return array(
                array(
                        'request' => array(
                            'module'        =>  $this->getRequest()->getParam('module'),
                            'controller'    =>  $this->getRequest()->getParam('controller'),
                            'action'        =>  'create'
                            ),
                        'label' => 'Create '.ucwords($this->view->singular),
                        'iconClass' => 'glyphicon glyphicon-plus',
                        'btnClass' => 'btn btn-default'
                    )
            );
    }
    
    public function returnHome($newEntity=null)
    {
        if(!($this->getRequest()->getParam('format') && $this->getRequest()->getParam('format')=='json')){
            $iwf_return_path = new Zend_Session_Namespace('iwf_return_paths');
            $previous = array_pop($iwf_return_path->routes);
            if($previous==$this->view->url())
                $previous = array_pop($iwf_return_path->routes);
            $this->_helper->redirector->gotoUrl($previous);
        }
    }
    
    public function returnReferer()
    {
         if(!(isset($_SERVER['HTTP_REFERER'])) || $_SERVER['HTTP_REFERER']==$_SERVER['REQUEST_URI'])
            $this->_redirect('/');
        else
            $this->_redirect($_SERVER['HTTP_REFERER']);
    }

    public function loadAssets()
    {
        /* Action Specific */
        $pathsToJs = array(
            $this->getRequest()->getParam('module').'/'.$this->getRequest()->getParam('controller').'/'.$this->getRequest()->getParam('action').'.js', //action specific
            $this->getRequest()->getParam('module').'/'.$this->getRequest()->getParam('controller').'.js', //controller specific
            $this->getRequest()->getParam('module').'.js', //module specific,
        );
        foreach(scandir(APPLICATION_PATH . '/../public/js') as $file){
            if(strpos($file,'.global.js')!==false){
                $pathsToJs[] = $file;
            }
        }
        foreach($pathsToJs as $pathToJs){
            $js = realpath(APPLICATION_PATH . '/../public/js/'.$pathToJs);
            if($js){
                $this->view->headScript()->appendFile('/js/'.$pathToJs);
            }
            //$pathToCss = $this->getRequest()->getParam('module').'/'.$this->getRequest()->getParam('controller').'/'.$this->getRequest()->getParam('action').'.css';
            $pathToCss = str_replace('.js','.css',$pathToJs);
            $css = realpath(APPLICATION_PATH . '/../public/css/'.$pathToCss);
            if($css){
                $this->view->headLink()->appendStylesheet('/css/'.$pathToCss);
            }
        }        
    }
    
    protected function _handleScaffolds(){
        if($this->useScaffolding){
            if(in_array($this->getRequest()->getParam('action'),$this->scaffolds)){
                $this->view->addScriptPath(APPLICATION_PATH.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'scripts');
                $this->render('scaffold-'.$this->getRequest()->getParam('action'), null, true);  
            } 
        }        
    }
    
    /* CRUD */
    
    public function viewAction()
    {
        $record = $this->view->record = $this->retrieveRecord();
        $this->_handleScaffolds();
    }    

    public function searchAction()
    {
        //$record = $this->view->record = $this->retrieveRecord();
        $this->_handleScaffolds();
    }
        
    public function postCreate($newEntity)
    {
    
    }

    public function postUpdate($newEntity)
    {
    
    }

    protected function _handleUploads(Zend_Form $form, $entity)
    {
        $values = array();
        //handle file uploads
        foreach($form->getElements() as $element){
            switch($element->getType()){
                case 'Zend_Form_Element_File':
                case 'Ia_Form_Element_FileMultiple':
                case 'Ia_Form_Element_FileUploader':
                     if(!method_exists($entity, 'getAttachmentFilePath')){
                        Ia_View_Helper_Alert::addAlert('Unable to receive file upload as no path is configured for this entity.','error');
                    }
                    if(!method_exists($entity, 'getPropertyClass')) {
                        $element->setDestination($entity->getAttachmentFilePath($entity->id,$element->getName()));
                        $element->receive();
                        if($value = $element->getValue()){
                            if(is_array($value)){
                                if(sizeof($value)==1){
                                    $value = array_pop($value);
                                } else {
                                    $value = serialize($value);
                                }
                            }
                            $values[$element->getName()] = $value;
                            $entity->{$element->getName()} = $value;
                            $this->em->persist($entity);
                            $this->em->flush();
                        }
                        break;
                    }
                     
                    $destination = $entity->getAttachmentFilePath($entity->id,$element->getName());
                    $element->setDestination($destination);
                    $element->receive();
                    if(!$element->getValue()) {
                        //no file for upload
                        break;
                    }
                    $getelementProp = $entity->getPropertyClass($element->getName());
                    $assType = $getelementProp['association'];
                    if ($assType == 'N') {
                        $values[$element->getName()] = $element->getValue();
                        $entity->{$element->getName()} = $element->getValue();
                        $this->em->persist($entity);
                        $this->em->flush();
                    } else if ($assType == 'O') { 
                    } else if ($assType == 'M') {
                        if(!is_array($element->getValue())) {
                            $imagesObj = new $getelementProp['class'];
                            $imagesObj->{$element->getName()} = $element->getValue();
                            $imagesObj->item = $entity;
                            $entity->{$element->getName()} = new ArrayCollection();
                            $entity->{$element->getName()}[] = ($imagesObj);
                            $values[$element->getName()] = ($imagesObj);
                            $this->em->persist($entity);
                            $this->em->flush();
                        } else {
                            foreach($element->getValue() as $fileElement) {
                                if($fileElement){
                                    $imagesObj = new $getelementProp['class'];
                                    $imagesObj->{$element->getName()} = $fileElement;
                                    $imagesObj->item = $entity;
                                    $entity->{$element->getName()} = new ArrayCollection();
                                    $entity->{$element->getName()}[] = ($imagesObj);
                                    $values[$element->getName()][] = ($imagesObj);
                                    $this->em->persist($entity);
                                    $this->em->flush();
                                }
                            }
                        }
                    }
                    break;
            }
        }
        return $values;        
    }
    
    public function createAction()
    {
        $this->view->form = $form = $this->createForm;
        if($this->getRequest()->isPost()){
            $values = $this->getRequest()->getPost();
            if ($form->isValid($values)) {
                try{                  
                    $newEntity = $this->entity->createEntity($values);
                    $this->em->persist($newEntity);
                    $this->em->flush();
                    $this->_handleUploads($form,$newEntity);
                    Ia_View_Helper_Alert::addAlert('New '.$this->view->singular.' has been successfully created.','success',null,array('insert_id'=>$newEntity->id));
                    $this->postCreate($newEntity);
                    $this->returnHome($newEntity);
                } catch (Exception $e){
                    Ia_View_Helper_Alert::addAlert($e->getMessage(),'error');
                }
            } elseif ($this->getRequest()->getParam('format') == 'json') {
                foreach($form->getMessages() as $element=>$message){
                    $error_message = $form->getElement($element)->getLabel().' - ';
                    foreach($message as $key=>$message_text){
                        Ia_View_Helper_Alert::addAlert($error_message.$message_text,'error');
                    }
                }
            }
        }
        $this->_handleScaffolds();        
    }
    
    protected function _buildJoins($relations){
        $dql = '';
        foreach($relations as $relationKey=>$relationName){
            if(!in_array($relationName,$this->view->noJoin)){
                if(strpos($relationName,'.')!==false){
                    $relationNameParts = explode('.',$relationName);
                    $relationPart = array_pop($relationNameParts);
                    $aliasPart = implode('.',$relationNameParts); 
                    $alias = array_search($aliasPart,$relations);
                    $dql .= ' LEFT JOIN '.$alias.'.'.$relationPart .' '.$relationKey;
                } else {
                    $dql .= ' LEFT JOIN e.'.$relationName.' '.$relationKey;
                }
            }
        }
        return $dql;   
    }
    
    protected function _getIndexQuery($wheres = array()){
        
        $order = false;
        
        if(strlen($this->getOrder())>0)
            $order = (strpos($this->getOrder(),'.')===false) ? 'e.'.$this->getOrder() : $this->getOrder();
    
        $dql = "SELECT e FROM ".get_class($this->entity)." e";
        
        if(isset($this->view->relations))
            $dql .= $this->_buildJoins($this->view->relations);
                       
        foreach($this->getFilters() as $key=>$value){
            if(is_array($value)){
                if(!isset($value['Strategy'])){
                    throw new \Zend_Exception('Complex filter data submitted without corresponding strategy class');
                }
                $strategyClass = new $value['Strategy'];
                unset($value['Strategy']);
                $wheres = array_merge($wheres,$strategyClass->getWhereArray($key,$value));
            } else if($value!==''){
                if(!is_numeric($value))
                    $wheres[] = $key.'=\''.$value.'\'';
                else
                    $wheres[] = $key.'='.$value;
            }
        }
        
        $search = $this->getRequest()->getParam('search');
        if(!$search && isset($this->getSession()->searchFilter))
            $search = $this->getSession()->searchFilter;
        if($search){
            $this->getSession()->searchFilter = $search;
            foreach($search as $key=>$value){
                if($value)
                    if(strpos($key,'.')===false)
                        $wheres[] = "e.".$key." LIKE '%".str_replace("'","''",$value)."%'";
                    else
                        $wheres[] = $key." LIKE '%".str_replace("'","''",$value)."%'";
            }
            $this->view->params['search'] = $search;
        }
        
        if(sizeof($wheres)>0){
            $dql .= ' WHERE ('.implode(' AND ',$wheres).')';
        }
        
        if($order)
            $dql .= " ORDER BY ".$order;
        else
            $dql .= $this->defaultOrder();
              
        return $dql;

    }

    public function defaultOrder()
    {
        return " ORDER BY e.id DESC";
    }
    
    public function restAction()
    {
        $params = $this->getRequest()->getParams();
        
        unset($params['module']);
        unset($params['controller']);
        unset($params['action']);
        unset($params['method']);
        
        switch($this->getRequest()->getParam('method')){
            /* Returns Record */
            case 'find':
                if(!isset($params['id']))
                    exit; //fail silently
                $record = $this->view->record = $this->em->getRepository(get_class($this->entity))->find($params['id']);
                break;
            case 'findOneBy':
                if(sizeof($params)==0)
                    exit; //fail silently
                $record = $this->view->record = $this->em->getRepository(get_class($this->entity))->findOneBy($params);
                break;                
            /* Returns Collection */
            case 'findBy':
                if(sizeof($params)==0)
                    exit; //fail silently
                $records = $this->view->records = $this->em->getRepository(get_class($this->entity))->findBy($params);
                break;
            case 'findAll':
                $records = $this->view->records = $this->em->getRepository(get_class($this->entity))->findAll();
                break;
            default:
                exit; //fail silently
                break;
        }
                
        if(!isset($this->view->restColumns))
            $this->view->restColumns = $this->view->detailColumns;
            
        $this->_handleScaffolds();    
    }
    
    public function indexAction($dql=null)
    {
        try{
            if($dql==null)
                $dql = $this->_getIndexQuery();
            $query = $this->em->createQuery($dql);
            $adapter =  new Ia_Doctrine_Paginator_Adapter($query);
            $zend_paginator = new \Zend_Paginator($adapter);          
            $zend_paginator->setItemCountPerPage($this->getPerPage())
                ->setCurrentPageNumber($this->getPage());
            $this->view->paginator = $zend_paginator;
        } catch(\Exception $e) {
            //clearing does not happen in environments configured to display exceptions (because this can be helpful in debugging)
            if(!\Ia\Config::get('resources/frontController/params/displayExceptions') && !$this->getRequest()->getParam('cleared') && ($this->getSession()->searchFilter != null || sizeof($this->getSession()->filters)>0 || sizeof($this->getSession()->order)>0)){
                //if bad filter data is the cause of the exception, this can auto-recover
		        $this->resetFilters();
                $this->_helper->redirector->goToUrl($this->view->url(array('order'=>null,'cleared'=>true)));
            } else {
                throw new \Exception($e->getMessage());
            }
        }
        $this->_handleScaffolds();       
    }   
    
    protected $_record = null;
    
    public function retrieveRecord($id=null,$cache=true,$redirect=true,$failSilently=false)
    {
        if($cache && $this->_record !== null)
            return $this->_record;
            
        if($id==null && !$id = $this->getRequest()->getParam('id')){
            if($failSilently)
                return false;
            Ia_View_Helper_Alert::addAlert('The record you requested has been deleted or does not exist.','error');
        } 

        if($this->getRequest()->getPost('id'))
            $id = $this->getRequest()->getPost('id');

        if(!$record = $this->getEntityManager()->find(get_class($this->entity), $id)){
            if($failSilently)
                return false;
            Ia_View_Helper_Alert::addAlert('The record you requested has been deleted or does not exist.','error');
            if($redirect)
                $this->returnHome();
            else
                return false;
        }
        
        if($cache)
            $this->_record = $record;
            
        return $record;    
    }
    
    public function updateAction($values=array())
    {
        $record = $this->view->record = $this->retrieveRecord();
        $this->view->form = $form = $this->updateForm;
        $form->setDefaults($record->toArray());
        if(sizeof($values)>0 || $this->getRequest()->isPost()){
            $values = (sizeof($values)>0) ? $values : $this->getRequest()->getPost();
            if ($this->getRequest()->getParam('no-form') || $form->isValid($values)) {
                try{
                    $record = $record->updateEntity($record,$values);
                    $this->em->persist($record);
                    $this->em->flush();
                    $this->_handleUploads($form,$record);
                    Ia_View_Helper_Alert::addAlert('Record has been successfully updated.','success');
                    $this->postUpdate($record);
                    if($this->getRequest()->getParam('no-form'))
                    	exit;
                    $this->returnHome($record);
                } catch (Exception $e){
                    Ia_View_Helper_Alert::addAlert($e->getMessage(),'error');
                }
            }
        }
        
        $this->_handleScaffolds();

        if($this->getRequest()->getParam('format') == 'json'){
            header('Content-type: application/json');
            echo Ia_View_Helper_Alert::toJson();
            exit;
        }
        
    }
    
    public function activateAction()
    {
        $record = $this->view->record = $this->retrieveRecord();
        $record = $this->entity->updateEntity($record,array('active'=>$this->getRequest()->getParam('active')));
        $this->em->persist($record);
        $this->em->flush();
        switch($this->getRequest()->getParam('active')){
            case 1:
                Ia_View_Helper_Alert::addAlert('Record has been activated.','success');
                break;
            default:
                Ia_View_Helper_Alert::addAlert('Record has been deactivated.','success');
                break;
        }
        if($_SERVER['HTTP_REFERER']==$_SERVER['REQUEST_URI'])
            $this->_redirect('/');
        else
            $this->_redirect($_SERVER['HTTP_REFERER']);        
    }    
    
    public function deleteAction()
    {
        if(\Ia\Config::get('php/max_memory_limit')){
            ini_set('memory_limit',\Ia\Config::get('php/max_memory_limit'));
        } else {
            ini_set('memory_limit','512M');
        }
        
        $ids = false;
        
        if(!($id = $this->getRequest()->getParam('id')) && !($ids = $this->getRequest()->getParam('ids'))){
            return $this->returnHome();
        }
        
        if(!$ids)
            $ids = array($id);
            
        if(!is_array($ids) && strpos($ids,',')!==false){
            $ids = explode(',',$ids);
        } elseif(!is_array($ids) && strpos($ids,' ')!==false){
            $ids = explode(' ',$ids);
        } elseif (!is_array($ids)) {
            $ids = array($ids);
        }

        $num_deleted = 0;
                    
        foreach($ids as $id){
        
            if(!$record = $this->em->find(get_class($this->entity), $id)){
                Ia_View_Helper_Alert::addAlert('The record you requested has been deleted or does not exist','error');
                return $this->returnHome();
            }
            try{
                $this->em->remove($record);
                $num_deleted++;
                \Ia\Log::write(get_class($this->entity).' entity #'.$id.' deleted',null,null,'INFORMATION');
            } catch(Exception $e) {
                if(APPLICATION_ENV=='production')
                    Ia_View_Helper_Alert::addAlert('Error deleting record #'.$id.'. It is likely being referenced by another entity.  Try deactivating instead.','error');
                else
                    Ia_View_Helper_Alert::addAlert($e->getMessage(),'error');
            }
        
        }

        if($num_deleted==1){
            Ia_View_Helper_Alert::addAlert('Your '.$this->view->singular.' has been deleted','success');
        } else {
            Ia_View_Helper_Alert::addAlert($num_deleted.' '.$this->view->plural.' have been deleted','success');
        }
        $this->em->flush();
        

        return $this->returnHome($record);
    }      
    
    public function actions($name){
        $actions = array(
            'activate'=>array(
            'condition'=>'eval:($item->active==0)','label'=>'Active',
                'true'=>array(
                    'label'=>'Activate','url'=>array('action'=>'activate','active'=>1,'id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-play'
                ),'false'=>array(
                    'label'=>'Deactivate','url'=>array('action'=>'activate','active'=>0,'id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-pause'
                )
            ),
            'view'=>array('label'=>'View','url'=>array('action'=>'view','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-eye-open'),        
            'search'=>array('label'=>'Search','url'=>array('action'=>'search','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-eye-open'),

            'edit'=>array('label'=>'Edit','url'=>array('action'=>'update','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-pencil'),           
            'delete'=>array('label'=>'Delete','url'=>array('action'=>'delete','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-trash','onclick'=>'return confirm(\'Are you sure you want to permanently delete this record?\');'),        
            'print'=>array('label'=>'Print','url'=>array('action'=>'print','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-print'),        
            'download'=>array('label'=>'Download','url'=>array('action'=>'download','id'=>'eval:$item->id'),'icon'=>'glyphicon glyphicon-circle-arrow-down'), 
            'duplicate'=>array('label'=>'Duplicate','url'=>array('action'=>'duplicate','id'=>'eval:$item->id'),'icon'=>'fa fa-files-o'), 
        );
        return $actions[$name];
    }
    
	protected function _initActionContexts(){
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contexts = $contextSwitch->getContexts();
        if(!isset($contexts['excel'])){
            $contextSwitch->addContext('excel',array('suffix'=>'excel'));
        }
        if(!in_array('json', $contextSwitch->getActionContexts('index'))){
            $contextSwitch->setAutoJsonSerialization(false)
                            ->addActionContext('view', 'json')
                            ->addActionContext('index', 'json')
                            ->addActionContext('free-search', 'json')
                            ->addActionContext('create', 'json')
                            ->addActionContext('update', 'json')
                            ->addActionContext('delete', 'json')
                            ->addActionContext('index', 'excel')
                            ->initContext();
        }
	}
}
