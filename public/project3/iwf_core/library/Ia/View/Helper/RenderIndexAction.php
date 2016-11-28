<?php

class Ia_View_Helper_RenderIndexAction extends Zend_View_Helper_Abstract
{
    public function renderIndexAction($action,$item,$type='link'){
    
        $xhtml = '';
        $condition = false; 

        if(isset($action['condition'])){
            eval('$condition = '.str_replace('eval:','',$action['condition']).';');
            if($condition){
                $action = $action['true'];
            }else{
                $action = $action['false'];
            }
        }

        $url = array();
        if(isset($action['url']) && !is_array($action['url'])){
            if(strpos($action['url'],'eval:')!==false){
                eval('$url = '.str_replace('eval:','',$action['url']).';');
            }else{
               $url = $action['url'];
            }
        }else if(isset($action['url'])) {
            foreach($action['url'] as $key=>$value){
                if(strpos($value,'eval:')!==false){
                    eval('$url[$key] = '.str_replace('eval:','',$value).';');
                } else{
                    $url[$key] = $value;
                }
            }
            $url = $this->view->url($url, null, false, false);
        }
        
        if($this->view->acl($url)){
            if(isset($action['prefix']))
                $xhtml .= $action['prefix'];
            $xhtml .= '<a '.((isset($action['onclick'])) ? 'onclick="'.$action['onclick'].'"' : '').' title="'.$action['label'].'" href="'.$url.'" '.(($type=='btn') ? ' class="btn btn-default" style="vertical-align:top;"' : 'class="'.$type.'"').'><i class="'.$action['icon'].'"></i>'.(($type=='btn') ? (' '.$action['label']) : '').'</a>';
            if(isset($action['suffix']))
                $xhtml .= $action['suffix'];
        }
        return $xhtml;
    }
}
