<?php
/**
 * Information ArchiTECH, LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@informationarchitech.com so we can send you a copy immediately.
 *
 *
 * @copyright  Copyright (c) 2014 Information ArchiTECH, LLC (http://www.informationarchitech.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Information ArchiTECH <contact@informationarchitech.com>
 */
 
class Ia_Controller_Plugin_Assets extends Zend_Controller_Plugin_Abstract
{
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {

        $cache = Zend_Registry::get('cache');
        $cache_key = 'modass_'.md5($request->module.'_'.$request->controller.'_'.$request->action);
        if(($files=$cache->load($cache_key))===false) {
            $files = $this->_retrieveModuleAssets($request);
            $cache->save($files,$cache_key);            
        }

        foreach($files as $type=>$assets){
            foreach($assets as $asset){
                $this->_includeAsset($type,$asset);
            }
        }
    }

    protected function _includeAsset($type,$file){
        switch($type){
            case 'js':
                Zend_Layout::getMvcInstance()->getView()->headScript()->appendFile($file);
                break;
            case 'css':
                Zend_Layout::getMvcInstance()->getView()->headLink()->appendStylesheet($file,'all');
                break;
            default:
                //fail silently
                break;
        }
    }

    protected function _retrieveTypes()
    {
        return array('js','css'); //exts
    }

    protected function _retrieveModuleAssets(Zend_Controller_Request_Abstract $request)
    {
        $files = array();
        if($modules = scandir(APPLICATION_PATH . '/modules/')){
            $types = $this->_retrieveTypes();
            foreach($types as $type){
                $files[$type] = array();
                foreach($modules as $module){
                    if(strpos($module,'.')===false){
                        $dir = APPLICATION_PATH . '/modules/' . $module . '/assets/'.$type.'/';
                        //global
                        if(file_exists($dir.'global.'.$type)){
                            $files[$type][] = '/asset/load/mod/'.$module.'/file/global.'.$type;
                        }
                        //module specific
                        if($request->getParam('module') == $module){
                            if(file_exists($dir.'module.'.$type)){
                                $files[$type][] = '/asset/load/mod/'.$module.'/file/module.'.$type;
                            }
                            if(file_exists($dir.$request->getParam('controller').'/controller.'.$type)){
                                $files[$type][] = '/asset/load/mod/'.$module.'/cnt/'.$request->getParam('controller').'/file/controller.'.$type;
                            }
                            if(file_exists($dir.$request->getParam('controller').'/'.$request->getParam('action').'.'.$type)){
                                $files[$type][] = '/asset/load/mod/'.$module.'/cnt/'.$request->getParam('controller').'/file/'.$request->getParam('action').'.'.$type;
                            }
                        }
                    }
                }
            }
        }
        return $files;
    }
        
}