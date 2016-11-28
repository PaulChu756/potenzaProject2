<?php
/*
 * This could always be replaced with something more sophisticated later on, if needed.
 */
 
class Ia_Controller_Plugin_SmartAdminNav extends Zend_Controller_Plugin_Abstract
{

    protected $_acl = null;
    protected $_role = null;
    protected $_links = 0;
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {

        //if we are clearing the cache on this request we skip the acl check entirely (so it can actually complete its action)
        if($request->getParam('controller')=='settings' && $request->getParam('action')=='clear-cache')
            return;
                    
        $navs = array('nav','sidebar');

        $view = Zend_Layout::getMvcInstance()->getView();
        
        $role = false;
        if(Zend_Registry::isRegistered('auth')){
            $user = Zend_Registry::get('auth');
            $role = $user->role;
        } 

        if(!$role) {
            $role = Ia\Config::get('acl/default_role');
        }
        
        $this->_role = $role;  

        $auth = Zend_Auth::getInstance();
        $acl = $this->_acl = Zend_Registry::get('acl');   
        
        //$cache = Zend_Registry::get('cache');        

        foreach($navs as $navType){

            $this->_links = 0;

            $param_keys = array();
            foreach($request->getParams() as $key=>$value){
                $param_keys[] = $value;
            }
            $param_keys[] = $this->_role;

            //$cache_key = $navType.'_'.md5(implode('_',$param_keys));
            
            //if(($xhtml=$cache->load($cache_key))===false) {
            
                $nav = (Ia\Config::get($navType)) ? Ia\Config::get($navType) : array();
                foreach (Ia\Config::get('modules') as $moduleKey => $moduleConfig){
                    if(isset($moduleConfig[$navType])){
                        if(!isset($nav['modules'])){
                            $nav['modules'] = array();
                        }
                        $nav['modules'][$moduleKey] = $moduleConfig[$navType];
                    }
                }
                
                switch($navType){
                    case 'sidebar':
                        $dropdown = false;
                        $xhtml = '<ul class="nav nav-pills nav-stacked">';
                        break;
                    default:
                        $xhtml = '<ul>';
                        $dropdown = true;
                        break;
                }                    

                $items = array();
                if(is_array($nav)){
                foreach($nav as $key=>$item){
                    if($key!=='modules')
                        $xhtml .= $this->_renderLi($key,$item,$request,$view);
                    else
                        foreach($item as $subKey=>$subItem)
                            $xhtml .= $this->_renderDropdown($subKey,$subItem,$request,$view,$dropdown);
                }
                }
                ksort($items);
                $xhtml .= implode('',$items);
                $xhtml .= '</ul>';
                
                if($this->_links==0){
                    $xhtml = '';
                }

                //$cache->save($xhtml,$cache_key);            
                
            //}          
            
            if(strlen($xhtml)>0)
                $view->placeholder($navType)->set($xhtml);
        }
    }
    
    protected function _renderDropdown($key,$item,$request,$view,$dropdown){
        
        $xhtml = '';
        $active = '';
        $label = '';
        foreach($item as $subKey=>$subItem){
            if($subKey=='label') {
                $label = $subItem;
            } else {
                $li = $this->_renderLi($subKey,$subItem,$request,$view);
                if(strpos($li,'class="active"')!==false)
                    $active = 'active ';
                $xhtml .= $li;
            }
                
        }   
        if(strlen($xhtml)==0)
            return '';
            
        if($label=='')
            $label = ucwords($key);
            
        if($dropdown){
            $fa_element = $this->_renderIcon($item['label']);
            $xhtml = '<li class="'.$active.'dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">'.
            $fa_element.'<span class="menu-item-parent">'.$item['label'].'</span>'.
            '</a><ul>'.
            $xhtml.
            '</ul></li>';
        } else {
                        
        }

        return $xhtml;
    }
    
    protected function _renderLi($key,$item,$request,$view){

        if(!$this->_acl->isAllowed($this->_role,$key))
            return;

        $this->_links++;
        
        $xhtml = '';
        $route = array();
        $parts = explode('_',$key);
        $route['action'] = array_pop($parts);
        $route['controller'] = array_pop($parts);
        $route['module'] = (count($parts)==0) ? 'default' : array_pop($parts);    
        $xhtml .= '<li';
        $match = false;
        if($request->getParam('controller')==$route['controller'] &&
           $request->getParam('action')==$route['action'] &&
           $request->getParam('module')==$route['module']){
            if(isset($item['params'])){
                $match = false;
                foreach($item['params'] as $key=>$value){
                    if($request->getParam($key)==$value){
                        $match = true;
                    } else {
                        $match = false;
                    }
                }
                if($match){
                    $xhtml .= ' class="active"';
                }
            } else {
                $xhtml .= ' class="active"';
            }
        }
        if($match===false && $request->getParam('module')==$route['module'] && $request->getParam('controller')==$route['controller']){
            //second best option
            $xhtml .= ' class="active"';
        }
        if(isset($item['params'])){
            $route = array_merge($route,$item['params']);
        }
        $fa_element = $this->_renderIcon($item['label']);
        $xhtml .= '><a href="'.$view->url($route,null,true).'">'.$fa_element.'<span class="menu-item-parent">'.$item['label'].'</span></a></li>';
        return $xhtml;
    }
    
    protected function _renderIcon($label){
        $i_element = '';
        if($label):
            $label = strtolower($label);
            $fa_class = '';
            //check module config module.ini file
            foreach (\Ia\Config::get('modules') as $moduleKey => $moduleConfig){
                if($moduleKey == $label):
                    $fa_class = !empty($moduleConfig['icon']['class']) ? $moduleConfig['icon']['class'] : '';
                endif;
            }
            if(!empty($fa_class)):
                $i_element = '<i class="fa fa-lg fa-fw '.$fa_class.'"></i>';
            else:
                switch($label):
					case 'dashboard':
                    case 'home':
                        $i_element = '<i class="fa fa-lg fa-fw fa-home"></i>';
                        break;
                    case 'users':
                        $i_element = '<i class="fa fa-lg fa-fw fa-user"></i>';
                        break;
                    case 'company':
                        $i_element = '<i class="fa fa-lg fa-fw fa-desktop"></i>';
                        break;
                    case 'project':
                        $i_element = '<i class="fa fa-lg fa-fw fa-pencil-square-o"></i>';
                        break;
                    case 'vendor':
                        $i_element = '<i class="fa fa-lg fa-fw fa-windows"></i>';
                        break;
                endswitch;
            endif;
        endif;
        return $i_element;
    }
        
}
