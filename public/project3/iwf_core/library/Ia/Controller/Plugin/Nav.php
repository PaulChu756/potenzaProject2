<?php
/*
 * This could always be replaced with something more sophisticated later on, if needed.
 */
 
class Ia_Controller_Plugin_Nav extends Zend_Controller_Plugin_Abstract
{

    protected $_acl = null;

    protected $_role = null;

    protected $_links = 0;

    protected $_nav = array();

    protected $_icons = array();

    protected $_request = null;

    protected $_view = null;

    protected $_navTitle = '';

    protected $_navHeading = '';

    protected $_crumbTrail = array();

    protected $_stack = array();

    /**
     *
     * \Doctrine\Entity\Manager
     */
    protected $_em = null;

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
        
        //if we are clearing the cache on this request we skip the acl check entirely (so it can actually complete its action)
        if($request->getParam('controller')=='settings' && $request->getParam('action')=='clear-cache')
            return;
                    
        $view = $this->_view = Zend_Layout::getMvcInstance()->getView();  

        $auth = Zend_Auth::getInstance();
        $acl = $this->_acl = Zend_Registry::get('acl');   
        
        foreach($this->_getNavTypes() as $navType){

            $this->_links = 0;

            $param_keys = array();
            foreach($request->getParams() as $key=>$value){
                $param_keys[] = $value;
            }
            $param_keys[] = $this->_role;

            $icons = array();

            $nav = $this->_getNavTreeByType($navType);
            $icons = $this->_getIconsByType($navType);
            $xhtml = $this->_renderNav($nav,$icons);
        
            $view->placeholder($navType)->append($xhtml);
        }

        $view->placeholder('nav-title')->set($this->_navTitle);
        $view->placeholder('nav-heading')->set($this->_navHeading);
        $crumbTrail = '';
        if(count($this->_crumbTrail)>0){
            $i = 0;
            $crumbTrail = '<ol class="breadcrumb">';
            $crumbTrail .= '<li'.(($i==count($this->_crumbTrail)) ? ' class="active"' : '').'><a href="/" title="Home">Home</a></li>';
            foreach($this->_crumbTrail as $_level=>$_crumbParts){
                $_resource = $_crumbParts[0];
                $_crumb = $_crumbParts[1];
                $i++;
                if($i==count($this->_crumbTrail))
                    $crumbTrail .= '<li'.(($i==count($this->_crumbTrail)) ? ' class="active"' : '').'>'.$_crumb['label'].'</li>';
                else
                    $crumbTrail .= '<li'.(($i==count($this->_crumbTrail)) ? ' class="active"' : '').'>'.$this->_renderLink($_resource,$_crumb).'</li>';
            }
            $crumbTrail .= '</ol>';
        }
        $view->placeholder('nav-breadcrumb')->set($crumbTrail);

    }

    public function getRole()
    {
        if($this->_role===null){
            $role = false;
            if(Zend_Registry::isRegistered('auth')){
                $user = Zend_Registry::get('auth');
                $role = $user->role;
            } 
            if(!$role) {
                $role = Ia\Config::get('acl/default_role');
            }
            $this->_role = $role;
        }
        return $this->_role;
    }

    /**
     * Get Doctrine Entity Manager
     * @return \Doctrine\Entity\Manager
     */    
    public function getEntityManager() {
        if($this->_em===null){
            $dc = \Zend_Registry::get('doctrine');
            $this->_em = $dc->getEntityManager();        
        }
        return $this->_em;        
    }

    protected function _getNavTypes()
    {
        return array('nav','sidebar');
    }    

    protected function _getNavTreeByType($navType)
    {
        if(!isset($_nav[$navType])){
            $nav = (Ia\Config::get($navType)) ? Ia\Config::get($navType) : array();
            foreach (Ia\Config::get('modules') as $moduleKey => $moduleConfig){
                if(isset($moduleConfig[$navType])){
                    if(!isset($nav['modules'])){
                        $nav['modules'] = array();
                    }
                    $nav['modules'][$moduleKey] = $moduleConfig[$navType];
                    if(isset($moduleConfig['icon']['class']))
                        $icons[$moduleKey] = $moduleConfig['icon']['class'];
                }
            }
            $this->_icons[$navType] = $icons;
            $this->_nav[$navType] = array('tree'=>$nav);
            switch($navType){
                case 'sidebar':
                    $this->_nav[$navType]['dropdown'] = false;
                    $this->_nav[$navType]['class'] = 'nav nav-pills nav-stacked';
                    break;
                default:
                    $this->_nav[$navType]['dropdown'] = true;
                    $this->_nav[$navType]['class'] = 'nav';
                    break;
            } 

        }
        return $this->_nav[$navType];
    }

    protected function _getIconsByType($navType)
    {
        if(!isset($this->_icons[$navType])){
            $this->_getNavTreeByType($navType);
        }
        return $this->_icons[$navType];
    }

    protected function _renderNav($nav,$icons,$level=1)
    {
        $xhtml = '<ul class="'.$nav['class'].'">';
        $dropdown = $nav['dropdown'];
        $nav = $nav['tree'];

        if(is_array($nav)){

            //this allows us to assign menu items to a different modules menu
            foreach($nav['modules'] as $moduleKey=>$resources){
                foreach($resources as $resourceKey=>$data){
                    if(is_array($data) && isset($data['module']) && $data['module'] != $moduleKey){
                        $targetModule = $data['module'];
                        unset($data['module']);
                        $nav['modules'][$targetModule][$resourceKey] = $data;
                        unset($nav['modules'][$moduleKey][$resourceKey]);
                    }
                }
            }

            foreach($nav as $key=>$item){
                if($key!=='modules') {
                    if($level==1)
                        $this->_stack = array();
                    $xhtml .= $this->_renderLi($key,$item,$nav,$level);
                } else {
                    $dropdowns = array();
                    foreach($item as $subKey=>$subItem){
                        $dropdownXhtml = '';
                        $dropdownXhtml = $this->_renderDropdown($subKey,$subItem,$dropdown,$level);
                        if(strlen($dropdownXhtml)>0){
                            $order = (isset($subItem['order'])) ? $subItem['order'] : 99999;
                            $icon = '';
                            if(isset($icons[$subKey]))
                                $icon = '<i class="fa fa-lg fa-fw '.$icons[$subKey].'"></i> ';
                            $dropdowns[] = array('icon'=>$icon,'label'=>$subItem['label'],'order'=>$order,'xhtml'=>$dropdownXhtml);
                        }
                    }
                    uasort($dropdowns,function($a,$b){ 
                        $aOrder = (isset($a['order'])) ? $a['order'] : 99999;
                        $bOrder = (isset($b['order'])) ? $b['order'] : 99999;
                        if ($aOrder == $bOrder) {
                            return 0;
                        }
                        return ($aOrder < $bOrder) ? -1 : 1;
                    });
                    foreach($dropdowns as $dropdown)
                    {
                        $dropdownXhtml = $dropdown['xhtml'];
                        if($navType=='sidebar'){
                            $xhtml .= '</ul><h4 class="text-muted">'.$dropdown['icon'].$dropdown['label'].'</h4><ul class="nav nav-pills nav-stacked">'.$dropdownXhtml;
                        } else {
                            $xhtml .= $dropdownXhtml;
                        }
                    }

                }
            }
        }
        $xhtml .= implode('',$items);
        $xhtml .= '</ul>';

        if($this->_links==0){
            $xhtml = '';
        }

        return $xhtml;
    }
    
    protected function _renderDropdown($key,$item,$dropdown,$level){
        
        $xhtml = '';
        $active = '';
        $label = '';

        $label = $item['label'];

        uasort($item,function($a,$b){ 
            $aOrder = (isset($a['order'])) ? $a['order'] : 99999;
            $bOrder = (isset($b['order'])) ? $b['order'] : 99999;
            if ($aOrder == $bOrder) {
                return 0;
            }
            return ($aOrder < $bOrder) ? -1 : 1;
        });

        foreach($item as $subKey=>$subItem){
            if($subKey=='label') {
                $label = $subItem;
            } elseif($subKey=='order') {
                $order = $subItem;
            } else {
                $this->_stack = array();
                $li = $this->_renderLi($subKey,$subItem,$nav,$level);
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
            $xhtml = '<li class="'.$active.'dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">'.
            $label.
            '<b class="caret"></b></a><ul class="dropdown-menu">'.
            $xhtml.
            '</ul></li>';
        } else {
                        
        }

        return $xhtml;
    }

    protected function _renderLink($key,$item)
    {
        $view = $this->_view;
        $route = array();
        $parts = explode('_',$key);
        $route['action'] = array_pop($parts);
        $route['controller'] = array_pop($parts);
        $route['module'] = (count($parts)==0) ? 'default' : array_pop($parts); 
        if(isset($item['params'])){
            $route = array_merge($route,$item['params']);
        }
        $xhtml = '<a ';
        if(isset($item['attribs']))
            foreach($item['attribs'] as $attribKey=>$attribVal)
                $xhtml .= $attribKey.'="'.$attribVal.'" ';
        $xhtml .= 'href="'.$view->url($route,null,true).'" title="'.((isset($item['title'])) ? $item['title'] : $item['label']).'">'.$item['label'].'</a>';
        return $xhtml;
    }
    
    protected function _renderLi($key,$item,$nav,$level){

        if(is_string($key) && strpos($key, '_') !== false){
            $parts = explode('_', $key);
            if(count($parts)>3){
                while(count($parts)>3){
                    array_pop($parts);
                }
                $key = implode('_',$parts);
            }
        }

        $this->_stack[$level] = array($key,$item); 

        $request = $this->_request;
        $view = $this->_view;

        if(!$this->_acl->isAllowed($this->_role,$key)){
            return;
        }

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
                    $this->_navTitle = (isset($item['title'])) ? $item['title'] : $item['label'];
                    $this->_navHeading = (isset($item['heading'])) ? $item['heading'] : $item['label'];
                    $this->_crumbTrail = $this->_stack;
                    $xhtml .= ' class="active"';
                }
            } else {
                $match = true;
                $this->_navTitle = (isset($item['title'])) ? $item['title'] : $item['label'];
                $this->_navHeading = (isset($item['heading'])) ? $item['heading'] : $item['label'];
                $this->_crumbTrail = $this->_stack;
                $xhtml .= ' class="active"';
            }
        }
        if(isset($item['params'])){
            $route = array_merge($route,$item['params']);
        }

        $badge = '';
        if(isset($item['badge'])){
            $badgeClass = new $item['badge'];
            $badge = $badgeClass->render($match);
        }

        $icon = '';
        if(isset($item['icon']) && isset($item['icon']['class'])){
            if(strpos($item['icon']['class'], 'fa-')!==false)
                $icon = '<i class="fa fa-lg fa-fw '.$item['icon']['class'].'"></i> ';
            else
                $icon = '<i class="glyphicon '.$item['icon']['class'].'"></i> ';
        }
        $xhtml .= '><a ';
        if(isset($item['attribs']))
            foreach($item['attribs'] as $attribKey=>$attribVal)
                $xhtml .= $attribKey.'="'.$attribVal.'" ';

        if(isset($item['attribs']) && isset($item['attribs']['data-target']))
            $xhtml .= 'href="'.$item['attribs']['data-target'].'">'.$icon.' '.$item['label'].$badge.'</a>';
        else
            $xhtml .= 'href="'.$view->url($route,null,true).'">'.$icon.' '.$item['label'].$badge.'</a>';

        if(isset($item['children']) && count($item['children'])>0){
            $subNav = array('tree'=>$item['children'],'dropdown'=>$nav['dropdown'],'class'=>$nav['class']);
            $xhtml .= $this->_renderNav($subNav,$icons,($level+1));
        }
        $xhtml .= '</li>';
        if(isset($item['showInNav']) && $item['showInNav']==false)
            return '';
        return $xhtml;
    }
        
}
