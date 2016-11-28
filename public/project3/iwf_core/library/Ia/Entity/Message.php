<?php
namespace Ia\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="messages")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="MessageRepository")
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class Message extends \Ia\Model
{
    
    const TYPE_SYSTEM = 'system';

    const TYPE_USER_MESSAGE = 'user';

    const FORMAT_TEXT = 'text';

    const FORMAT_HTML = 'html';

    /**
     *
     * @var integer $id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="origin_user_id", referencedColumnName="id")
     **/
    private $origin_user;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="recipient_user_id", referencedColumnName="id")
     **/
    private $recipient_user;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $format = self::FORMAT_TEXT;
    
    /**
     * Allows us to override the name in the recipient user entity
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $recipient_user_name;

    /**
     *
     * Allows us to override the email address in the recipient user entity
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $recipient_user_email;

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $type = self::TYPE_SYSTEM;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $link;    
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $http_host = null; 

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $subject;

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $follow_link_text;    

    /**
     *
     * @var  string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $follow_link_icon;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $from_name;

    /**
     *
     * @var  string
     * @ORM\Column(type="boolean",nullable=false)
     */
    private $anonymous = false;

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $reply_to_email;

    /**
     *
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private $message;

    /**
     *
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private $attachments;  
    
    /**
     *
     * @var string
     * @ORM\Column(type="boolean",nullable=false)
     */
    private $active = true;

    /**
     *
     * @var string
     * @ORM\Column(type="boolean",nullable=false)
     */
    private $error = false;

    /**
     *
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private $error_message;  

    /**
     *
     * @var string
     * @ORM\Column(type="boolean",nullable=false)
     */
    private $email = true;

    /**
     *
     * @var string
     * @ORM\Column(type="boolean",nullable=false)
     */
    private $email_instantly = false;
    
    /**
     *
     * @var string
     * @ORM\Column(type="boolean",nullable=false)
     */
    private $dismissed = false;

     /**
     * @var datetime $created_at
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at; 

     /**
     * @var datetime $created_at
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at; 

    public function getType()
    {
        if(!$this->type){
            return self::TYPE_SYSTEM;
        } else {
            return $this->type;
        }
    }
    
    public function getAllTypeOptions()
    {
        return self::staticGetAllTypeOptions();
    }
    
    public static function staticGetAllTypeOptions()
    {
        $builtInTypes = array(
            self::TYPE_SYSTEM => array('title'=>'System messages related to your user account.','optout'=>false),
            self::TYPE_USER_MESSAGE => array('title'=>'Internal messages from other users of this application','optout'=>true)
            );
        $customTypes = array();
        $modulesConfig = \Ia\Config::get('modules');
        if($modulesConfig){
            foreach($modulesConfig as $moduleKey=>$data){
                if(isset($data['message']['types'])){
                    foreach($data['message']['types']['titles'] as $typeKey=>$title){
                        $customTypes[$typeKey] = array('title'=>$title,'optout'=>((isset($data['message']['types']['optout'][$typeKey])) ? $data['message']['types']['optout'][$typeKey] : true));
                    }
                }
            }
        }
        return ($builtInTypes + $customTypes);
    }    


    /**
     * return array folders
     */
    public static function getFolders()
    {
        return array('new','inbox','archived','sent');
    }

    /** @ORM\PrePersist */
    public function prePersist()
    {
        if(!$this->email_instantly || !$this->email)
            return;
        $this->sendEmail();
        $this->email = false;
        $this->email_instantly = false;
    }

    public function getAttachmentFullPaths()
    {
        $full_paths = array();
        if($this->attachments && $attachments = unserialize($this->attachments)){
            foreach($attachments as $attachment){
                $full_paths[] = $attachment;
            }
        }
        return $full_paths;
    }      

    public function getRecipientUserName()
    {
        if($this->recipient_user_name)
            return $this->recipient_user_name;
        elseif($this->recipient_user)
            return $this->recipient_user->first_name.' '.$this->recipient_user->last_name;
        else
            return 'System';
    }

    public function getRecipientEmailAddress()
    {
        if($this->recipient_user_email)
            return $this->recipient_user_email;
        elseif($this->recipient_user)
            return $this->recipient_user->email_address;
        else
            return 'N/A';
    }

    public function sendEmail()
    {
        $type = $this->getType();
        if(!$this->recipient_user){
            return;
        }
        $recipient_user_id = $this->recipient_user->id;
        $emailPreferences = $this->recipient_user->getEmailPreferences();
        if(isset($emailPreferences[$type]) && !$emailPreferences[$type]['optin']){
            return;
        }

        $options = \Ia\Config::get('resources/mail');
        $mail = new \Zend_Mail();
        
        if(\Zend_Registry::isRegistered('acl')){
            $acl = \Zend_Registry::get('acl');
        }

        $front = \Zend_Controller_Front::getInstance();
        $http_host = ($this->http_host) ? $this->http_host : $front->getRequest()->getHttpHost();

        if($acl->has('message_follow-link')) //the new method
            $messageUrl = 'http://'.$http_host.(($this->link) ? '/message/follow-link/id/'.$this->id : '/message/index');
        elseif(!isset($options['linkToMessagesIfNoLink']) || $options['linkToMessagesIfNoLink'])
            $messageUrl = 'http://'.$http_host.(($this->link) ? $this->link : '/message/index');

        $bodyHtml = '';
        if($header_url = \Ia\Config::get('email_header_url')){
            $bodyHtml .= '<img src="'.$header_url.'" />';
        }
        $bodyHtml .= '<p>'.((!($this->format) || $this->format==self::FORMAT_TEXT) ? nl2br($this->message) : $this->message).'</p>';

        if($messageUrl)
            $bodyHtml .= '<p>Please view the details at the following link:</p>' .
                         '<p><a href="'.$messageUrl.'">'.$messageUrl.'</a></p>';

        if($acl->has('message_email-preferences')){ //the new method
            $email_prefs_url = 'http://'.$http_host.'/message/email-preferences/user/'.$this->recipient_user->id.'/token/'.$this->recipient_user->getEmailPreferencesToken();
            $bodyHtml .= '<p></p><p><small>You are receiving this email because you are a user on the website '.$http_host.'. 
            <a href="'.$email_prefs_url.'">Manage your email preferences</a>.</small><p>';
        }

        $mail->setBodyHtml($bodyHtml);

        $alwaysUseDefaultAsSender = (isset($options['alwaysUseDefaultAsSender']) && $options['alwaysUseDefaultAsSender']==true);

        if(isset($this->origin_user) && !($alwaysUseDefaultAsSender)){
            if(!isset($this->from_name)) {
                if($this->anonymous) {
                    $mail->setFrom($options['defaultFrom']['email'], $this->origin_user->first_name.' '.$this->origin_user->last_name[0]);
                } else {
                    $mail->setFrom($options['defaultFrom']['email'], $this->origin_user->first_name.' '.$this->origin_user->last_name);
                }
            } else {
                $mail->setFrom($options['defaultFrom']['email'], $this->from_name);
            }

            if(!$this->reply_to_email) {
                if($this->anonymous){
                    $mail->setReplyTo($options['defaultFrom']['email'],$this->origin_user->first_name.' '.$this->origin_user->last_name[0]);
                } else {
                    $mail->setReplyTo($this->origin_user->email_address,$this->origin_user->first_name.' '.$this->origin_user->last_name);
                }
            }

        } else if (isset($this->from_name) && isset($options['excludeDefaultFrom']) || $options['excludeDefaultFrom']){
            $mail->setFrom($options['defaultFrom']['email'], $this->from_name);
        } else {
        	$mail->setFrom($options['defaultFrom']['email'], \Ia\Config::get('title'));
        }

        if($this->reply_to_email){
            $mail->setReplyTo($this->reply_to_email);
        }
        
        //do not email real users unless in production
        if(!isset($options['alwaysRouteToAdmin']) || $options['alwaysRouteToAdmin']){
            $mail->addTo($options['defaultRecipient']['email'], $this->recipient_user->first_name.' '.$this->recipient_user->last_name);
        }else{
            if($this->recipient_user_email){ //override
                $mail->addTo($this->recipient_user_email, $this->recipient_user_name);
            } else {
                $mail->addTo($this->recipient_user->email_address, $this->recipient_user->first_name.' '.$this->recipient_user->last_name);
            }
        }
            
        if(!isset($options['includeHostnameInSubject']) || $options['includeHostnameInSubject'])
            $mail->setSubject('['.$http_host.'] '.$this->subject);
        else
            $mail->setSubject($this->subject);

        $attachments = $this->getAttachmentFullPaths();
        if($attachments){
            foreach($attachments as $attachment){
                $parts = explode(DIRECTORY_SEPARATOR,$attachment);
                $filename = array_pop($parts);
                $content = file_get_contents($attachment); // e.g. ("attachment/abc.pdf")
                $attachment = new \Zend_Mime_Part($content);
                $attachment->type = 'application/pdf';
                $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
                $attachment->filename = $filename; // name of file
                $mail->addAttachment($attachment);
            }
        }

        $mail->send();
    }
        
    public function __get($property)
    {
        return $this->$property;
    }
    
    public function __set($property,$value)
    {
        $this->$property = $value;
    }  

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function toArray() {
        return get_object_vars($this);
    }

    /**
     * Update an entity with the given data
     *
     * @param array $data
     * @return object 
     */
    public function updateEntity($entity, array $data)
    {    
        $metadata = $this->em->getClassMetadata(get_class($this));
        $ignore = array('submit');
        foreach($data as $property => $value){
        
            if($property=='origin_user_id'){
                $user = $this->em->find('Ia\Entity\User', $value);
                $metadata->setFieldValue($entity, 'origin_user', $user);
                unset($user);
                continue;
            }   
            
            if($property=='recipient_user_id'){
                $user = $this->em->find('Ia\Entity\User', $value);
                $metadata->setFieldValue($entity, 'recipient_user', $user);
                unset($user);
                continue;
            }  
            
            if(in_array($property,$ignore))
                continue;
        
            if(!$metadata->reflClass->hasProperty($property)){
                throw new \Zend_Exception("'$property' doesn't exist on '$class'");
            }

            $metadata->setFieldValue($entity, $property, $value);
        }

        return $entity;
    } 

    /** @ORM\PrePersist */
    public function createdTimestamp()
    {
        $this->created_at = new \DateTime;
        $this->updated_at = new \DateTime;
    }
    
    /** @ORM\PreUpdate */
    public function updatedTimestamp()
    {
        $this->updated_at = new \DateTime;
    }      
    
}
