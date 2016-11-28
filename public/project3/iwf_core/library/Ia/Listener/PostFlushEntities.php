<?php

namespace Ia\Listener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

/** 
 * This can be used to persist and flush entities modified by other listners
 * Use with caution as infinite loops can be created
 */
class PostFlushEntities
{

    public function postFlush(PostFlushEventArgs $event)
    {
        if(\Zend_Registry::isRegistered('postFlushEntities')){
            $postFlushEntities = \Zend_Registry::get('postFlushEntities');
            if(is_array($postFlushEntities)){
                $em = $event->getEntityManager();
                foreach ($postFlushEntities as $postFlushEntity) {
                    //echo get_class($postFlushEntity).' - #'.$postFlushEntity->id.'!'.$postFlushEntity->percent_shopped;exit;
                    $em->persist($postFlushEntity);
                }
                \Zend_Registry::set('postFlushEntities',array());
                $em->flush();
            }
        }
    } 

}