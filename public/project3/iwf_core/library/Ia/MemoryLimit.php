<?php

namespace Ia;
/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class MemoryLimit
{
    
    public static function setMax(){
        $host = $_SERVER['HTTP_HOST'];
        $currentMemoryLimit = ini_get('memory_limit');
        $newMemoryLimit = ini_get('memory_limit');
        /* Worker servers can have memory limit set higher */
        if($host==\Ia\Config::get('worker_domain') && \Ia\Config::get('php/max_memory_limit_worker')){
            $newMemoryLimit = \Ia\Config::get('php/max_memory_limit_worker');
        } else {
            if(\Ia\Config::get('php/max_memory_limit')){
                $newMemoryLimit = \Ia\Config::get('php/max_memory_limit');
            } else {
                $newMemoryLimit = '512M';
            }
        } 
        if(self::convertUserStrToBytes($newMemoryLimit) > self::convertUserStrToBytes($currentMemoryLimit)){
            ini_set('memory_limit',$newMemoryLimit);
        }
    }

    public static function convertUserStrToBytes($str)
    {
        $str = trim($str);
        $num = (double)$str;
        if (strtoupper(substr($str, -1)) == "B")  $str = substr($str, 0, -1);
        switch (strtoupper(substr($str, -1)))
        {
            case "P":  $num *= 1024;
            case "T":  $num *= 1024;
            case "G":  $num *= 1024;
            case "M":  $num *= 1024;
            case "K":  $num *= 1024;
        }

        return $num;
    }
    
}