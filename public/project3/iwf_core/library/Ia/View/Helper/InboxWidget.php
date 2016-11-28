<?php

class Ia_View_Helper_InboxWidget extends Zend_View_Helper_Abstract
{
    
    public function inboxWidget()
    {
        $xhtml = '';
        if(Zend_Registry::isRegistered('auth') && Zend_Registry::get('auth') instanceOf Ia\Entity\User){
            ob_start();
            $user = Zend_Registry::get('auth');
            $counts = array();
            foreach(\Ia\Entity\Message::getFolders() as $folder)
                $counts[$folder] = $this->getEntityManager()->getRepository('\Ia\Entity\Message')->getMessagesQueryByFolder($user,$folder,true)->getSingleScalarResult();
            ?>
            <span id="activity" class="activity-dropdown"> <i class="fa fa-envelope-o"></i> <b class="badge"><?=$counts['new'];?></b></span>
            <!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
            <div class="ajax-dropdown">
                <!-- the ID links are fetched via AJAX to the ajax container "ajax-notifications" -->
                <div class="btn-group btn-group-justified" data-toggle="buttons">
                    <label class="btn btn-default active show_new_messages">
                        <input type="radio" name="activity" class="show_new_messages" id="/message/view-messages/view_message_type/new">
                            New Msgs (<?=$counts['new'];?>) 
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="activity" id="/message/view-messages/view_message_type/inbox">
                        Inbox (<?=$counts['inbox'];?>) 
                    </label>
                    <label class="btn btn-default">
                        <input type="radio" name="activity" id="/message/view-messages/view_message_type/sent">
                        Sent (<?=$counts['sent'];?>) 
                    </label>
                </div>
                <!-- notification content -->
                <div class="ajax-notifications custom-scroll">
                </div>
                <!-- end notification content -->          
                <!-- footer: refresh area -->
                <span> 
                    <?php if($this->view->acl(null,'message_index')): ?>
                    <a href="<?php echo $this->view->url(array('controller'=>'message','action'=>'index'),NULL,TRUE); ?>" class="pull-left"><i class="fa fa-home"></i> Message Home</a>
                    <?php endif; ?>
                    <?php if($this->view->acl(null,'message_dismiss-all')): ?>
                    <a style="margin-left:10px" href="<?php echo $this->view->url(array('controller'=>'message','action'=>'dismiss-all'),NULL,TRUE); ?>" class="pull-left"><i class="fa fa-eye-slash"></i> Dismiss All</a>
                    <?php endif; ?>
                    <?php if($this->view->acl(null,'message_archive-all')): ?>
                    <a style="margin-left:10px" href="<?php echo $this->view->url(array('controller'=>'message','action'=>'archive-all'),NULL,TRUE); ?>" class="pull-left"><i class="glyphicon glyphicon-folder-open"></i> Archive All</a>
                    <?php endif; ?>
                </span>
                <!-- end footer -->
            </div>
            <!-- END AJAX-DROPDOWN -->
            <?php
            $xhtml .= ob_get_contents();
            ob_end_clean();
        }
        return $xhtml;
    }

    protected $_em = null;

    protected $_dc = null;

    public function getEntityManager()
    {
        if($this->_dc === null){
            $this->_dc = \Zend_Registry::get('doctrine');
        }
        if($this->_em == null){
            $this->_em = $this->_dc->getEntityManager();
        }
        if(!$this->_em->isOpen()){
            $this->_em = $this->_dc->resetEntityManager();
        }
        return $this->_em;
    }

}