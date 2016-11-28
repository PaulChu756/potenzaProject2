<?php

namespace Ia;
/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Config
{
    
    public static function get($var){

        $frontendOptions = array(
            'lifetime' => 3600, // cache lifetime of approximately 1 hour
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir' => APPLICATION_PATH.'/cache/' // Directory where to put the cache files
        );

        // getting a Zend_Cache_Core object
        $cache = \Zend_Cache::factory('Core',
                                 'File',
                                 $frontendOptions,
                                 $backendOptions);

        if(($options=$cache->load('options'))===false) {
            $front = \Zend_Controller_Front::getInstance();
            $bootstrap = $front->getParam("bootstrap");
            if(!is_object($bootstrap))
                throw new \Zend_Exception('No valid bootstrap object');
            $options = $bootstrap->getOptions();
            $cache->save($options,'options');            
        }
                
        if(strpos($var,'/')!==false){
            $parts = explode('/',$var);
        } else {
            $parts = array($var);
        }
        $piece = $options;
        while(sizeof($parts)>0){
            $key = array_shift($parts);
            $piece = (isset($piece[$key])) ? $piece[$key] : false;
        }
        return $piece;
    }
    
}