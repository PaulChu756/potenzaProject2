<?php
namespace Ia\Observer;

/**
 *
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class EmailMessages extends ObserverAbstract implements ObserverInterface
{

	public function execute()
	{
    	/* only send 49 per batch */
        $success = [];
        $failure = [];
        $unemailed_messages = $this->getEntityManager()->getRepository('\Ia\Entity\Message')->getMessagesByEmail(true,49);
        if($unemailed_messages){  
            $sent = 0;
            foreach($unemailed_messages as $unemailed_message){   
                try{
                    $unemailed_message->sendEmail();
                    $unemailed_message->email = false;
                    $unemailed_message->email_instantly = false;
                    $this->getEntityManager()->persist($unemailed_message);
                    $success[] = $unemailed_message->id;
                } catch (\Exception $e) {
                    $unemailed_message->error = true;
                    $unemailed_message->error_message = $e->getMessage();
                    $this->getEntityManager()->persist($unemailed_message);
                    $failure[] = $unemailed_message->id;
                }
                $sent++;
                $this->setPercentComplete(floor(($sent / count($unemailed_messages)) * 100));
            }
            $this->getEntityManager()->flush();
            return array(sizeof($success) . ' messages emailed, '.sizeof($failure).' failed.',array('success'=>$success,'failure'=>$failure));
        } else {
            return false;
        }
	}

}
