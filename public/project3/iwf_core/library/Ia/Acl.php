<?php

namespace Ia;
/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Acl extends \Zend_Acl {

    public $request;
    
    public function setRequest($request){
        $this->request = $request;
    }
    
    public function getRequest(){
        return $this->request;
    }

    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        /**
         * The purpose of this modification is to allow us to append text to resources,
         * essentially allowing us to create multiple navigation items with the same resource.
         * e.g. module_controller_action_id_1, module_controller_action_id_2, etc
         */
        if(is_string($resource) && strpos($resource, '_') !== false){
            $parts = explode('_', $resource);
            if(count($parts)>3){
                while(count($parts)>3){
                    array_pop($parts);
                }
                $resource = implode('_',$parts);
            }
        }
        return parent::isAllowed($role,$resource,$privilege);
    }

    public function populate(){
    
        //roles populated from application config
        foreach(Config::get('acl/roles') as $role=>$parents){
            if($parents)
                $this->addRole(new \Zend_Acl_Role($role),$parents);        
            else
                $this->addRole(new \Zend_Acl_Role($role));        
        }
        
        /* Everyone must have access to the forbidden page :P */
        $this->add(new \Zend_Acl_Resource('user_forbidden'));
        $this->allow(null,'user_forbidden');
        
        /* Everyone must be able to (at least try to) login */
        $this->add(new \Zend_Acl_Resource('user_login'));
        $this->allow(null,'user_login'); 
        
        /* Everyone must be able to logout */
        $this->add(new \Zend_Acl_Resource('user_logout'));
        $this->allow(null,'user_logout');        
        
        $resources = Config::get('acl/resources');
        foreach (Config::get('modules') as $moduleConfig){
            if(isset($moduleConfig['acl']['resources'])){
                $resources = array_merge($moduleConfig['acl']['resources'],$resources);
            }
        }
                
        foreach($resources as $resource=>$rules){
            $this->add(new \Zend_Acl_Resource($resource));
            if(isset($rules['allow']))
                foreach($rules['allow'] as $allowRole)
                    if($allowRole)
                        $this->allow($allowRole,$resource);
            if(isset($rules['deny']))
                foreach($rules['deny'] as $denyRole)
                    if($denyRole)
                        $this->deny($denyRole,$resource);
            if(isset($rules['assert']))
                foreach($rules['assert'] as $assertRole=>$assertClass)
                    if($assertRole && $assertClass)
                        $this->allow($assertRole,$resource,null,new $assertClass);

        }
    
    }
    
}