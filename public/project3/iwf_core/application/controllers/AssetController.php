<?php

class AssetController extends Zend_Controller_Action
{

    public function uploadAction()
    {
        $options = array();
        if($this->getRequest()->getParam('accepts')){
            if(strpos($this->getRequest()->getParam('accepts'), '.')!==false){
                if(strpos($this->getRequest()->getParam('accepts'), ',')!==false){
                    $extensions = explode(',',$this->getRequest()->getParam('accepts'));
                } else {
                    $extensions = array($this->getRequest()->getParam('accepts'));
                }
            }
            foreach($extensions as $key=>$extension){
                $extensions[$key] = str_replace('.','',$extension);
            }
            $options['accept_file_types'] = '/\.('.implode('|',$extensions).')$/i';
        }
        $upload_handler = new Ia_UploadHandler($options);
        exit;
    }
 
    public function loadAction()
    {
        $file = $this->getRequest()->getParam('file');
        $folder = false;
        $ext = array_pop(explode('.',$file));
        switch ($ext){
            case 'js':
                $folder = 'js';
                $contentType = 'application/javascript';
                break;
            case 'css':
                $folder = 'css';
                $contentType = 'text/css';
                break;
            case 'gif':
                $folder = 'img';
                $contentType = 'image/gif';
                break;
            case 'jpg':
                $folder = 'img';
                $contentType = 'image/jpeg';
                break;
            case 'png':
                $folder = 'img';
                $contentType = 'image/png';
                break;
            case 'otf':
                $folder = 'fonts';
                $contentType = "font/opentype";
                break;
        }

        if($folder){

            $module = $this->getRequest()->getParam('mod');
            $controller = $this->getRequest()->getParam('cnt');

            if($module && !$controller)
                $path = APPLICATION_PATH . '/modules/' . $this->getRequest()->getParam('mod') . '/assets/'.$folder.'/' . $file;
            elseif($module && $controller)
                $path = APPLICATION_PATH . '/modules/' . $this->getRequest()->getParam('mod') . '/assets/'.$folder.'/' . $controller . '/' . $file;
            else
                $path = false;

            if($path){
                if(realpath($path)){
                    $maxAge = (60 * 60 * 24);
                    $content = file_get_contents(realpath($path));
                    $this->getResponse()
                        ->setHeader('Cache-Control', 'max-age='.$maxAge)
                        ->setHeader('Content-Type', $contentType)
                        ->appendBody($content)
                        ->sendResponse();
                    exit;
                }

            }
        }

        $this->getResponse()->setHttpResponseCode(404)->sendResponse();
        exit;
    }   
    
}