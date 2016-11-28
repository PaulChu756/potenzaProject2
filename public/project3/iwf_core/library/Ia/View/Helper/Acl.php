<?php

class Ia_View_Helper_Acl extends Zend_View_Helper_Abstract
{
    /* Add some caching? */
    public function acl($url=null,$resource=null,$request=null)
    {
        if($request) {
            if(!isset($request['module']))
                $request['module'] = $this->view->params['module'];
            if(!isset($request['controller']))
                $request['controller'] = $this->view->params['controller'];
            $module = ($request['module']=='default') ? '' : $request['module'].'_';
            $resource = $module.$request['controller'].'_'.$request['action'];
        } elseif($url && !$resource){
            $parts = explode('/',$url);
            if(!$parts[0])
                array_shift($parts);
            if(in_array($parts[0],$this->getModules())){
                $module = array_shift($parts).'_';
            } else { //default module
                $module = '';
            }
            $controller = array_shift($parts);
            $action = array_shift($parts);
            $resource = $module.$controller.'_'.$action;
            $request = array();
            while(sizeof($parts)>0){
                $request[array_shift($parts)] = array_shift($parts);
            }
        }
        if(Zend_Registry::isRegistered('acl')){
            if(Zend_Registry::isRegistered('auth')){
                $user = Zend_Registry::get('auth');
            } else {
                $user = new stdClass();
                $user->role = 'guest';
            }         
            $acl = Zend_Registry::get('acl');
            if($request){
                $acl->setRequest($request);
            }
            if($acl->has($resource) && $acl->isAllowed($user->role,$resource)){
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
    
    public function getModules()
    {
        $front = Zend_Controller_Front::getInstance();
        $module_dir = realpath(APPLICATION_PATH.DIRECTORY_SEPARATOR.'modules');
        $temp = array_diff( scandir( $module_dir), Array( ".", "..", ".svn"));
        $modules = array();
        $controller_directorys = array();
        foreach ($temp as $module) {
            if (is_dir($module_dir . "/" . $module)) {
                    array_push($modules,$module);
            }
        }
        return $modules;            
    }    
    
    
    
    

}