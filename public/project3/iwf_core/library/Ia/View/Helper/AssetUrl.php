<?php

class Ia_View_Helper_AssetUrl extends Zend_View_Helper_Abstract
{
    
    public function assetUrl($module=null,$controller=null,$filename)
    {
        if($module && $controller)
        	return '/asset/load/mod/'.$module.'/cnt/'.$controller.'/file/'.$filename;
        elseif($module)
        	return '/asset/load/mod/'.$module.'/file/'.$filename;
        else
        	return '';
    }

}