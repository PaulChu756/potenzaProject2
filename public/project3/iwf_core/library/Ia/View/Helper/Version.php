<?php

class Ia_View_Helper_Version extends Zend_View_Helper_Abstract
{
    /**
     * @return string
     */
    public function version()
    {
        $version = 'unknown';
        $cache = Zend_Registry::get('cache');
        if(!$version = $cache->load('version')) {
            if($txt = file_get_contents(realpath(APPLICATION_PATH.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'VERSION.txt'))){
                $lines = explode(chr(10),$txt);
                if(isset($lines[0]) && strlen($lines[0])>0){
                    $parts = explode(' ',$lines[0]);
                    if(isset($parts[0]) && strlen($parts[0])>0){
                        $version = $parts[0];
                    }
                }
            }
            $cache->save($version,'version');            
        }
        return $version;
    }

}
