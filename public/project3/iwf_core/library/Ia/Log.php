<?php

namespace Ia;
/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Log
{

    public static function write($message,$data=null,$user_id=null,$type='INFORMATION'){
        $logModel = new \Ia\Entity\Log;
        $em = self::getEntityManager();
        $logModel->message = $message;
        $logModel->data = $data;
        if($user_id!=null){
            $user = $em->getRepository('\Ia\Entity\User')->find($user_id);
            $logModel->user = $user;
        } else {
            if(\Zend_Registry::isRegistered('auth') && $user = \Zend_Registry::get('auth')){
                $user = $em->getRepository('\Ia\Entity\User')->find($user->id);
                $logModel->user = $user;
            }        
        }
        $logModel->type = $type;
        $em->persist($logModel);
        $em->flush();  
    }

    public static function getEntityManager()
    {
        $dc = \Zend_Registry::get('doctrine');
        $em = $dc->getEntityManager();
        if(!$em->isOpen()){
            $em = $dc->resetEntityManager();
        }
        return $em;
    }    
    
}