<?php

namespace Ia\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 *
 * @ORM\Table(name="users",indexes={@ORM\Index(name="search_idx", columns={"email_address"})})
 * @ORM\HasLifecycleCallbacks 
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"user" = "User"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @author Aaron Lozier <aaron@informationarchitech.com>
 */
class User
{
    /**
     *
     * @var integer $id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $first_name;    
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $last_name;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $company_name;    

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $phone;

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $address_line_1; 

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $address_line_2;
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $city;    
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=2,nullable=true)
     */
    protected $state;

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=60,nullable=true)
     */
    protected $country = 'US';          

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=10,nullable=true)
     */
    protected $zip;

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $geocode_key;

    /**
     *
     * @var float
     * @ORM\Column(type="float",nullable=true)
     */
    protected $latitude; 

    /**
     *
     * @var float
     * @ORM\Column(type="float",nullable=true)
     */
    protected $longitude;         
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $email_address;

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=1024,nullable=true)
     */
    protected $email_preferences;    
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $token; 
    
    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $password;     

    /**
     *
     * @var string
     * @ORM\Column(type="string",length=150,nullable=true)
     */
    protected $role;
    
    /**
     * @ORM\OneToMany(targetEntity="Message",mappedBy="recipient_user",cascade="remove") 
     */
    protected $inbox;
    
    /**
     * @ORM\OneToMany(targetEntity="Message",mappedBy="origin_user",cascade="remove") 
     */
    protected $sent_items;
    
    /**
     * @ORM\OneToMany(targetEntity="Log",mappedBy="user",cascade="remove") 
     */
    protected $log;
    
    /**
     *
     * @var string
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $pw_reset_required;    
    
    /**
     *
     * @var string
     * @ORM\Column(type="boolean",nullable=false)
     */
    protected $active;

    /**
     *
     * @var string
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $email_confirmed = 0;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     *
     * @var boolean
     * @ORM\Column(type="boolean",length=1,nullable=true)
     */
    protected $synced = 0;    

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @var datetime $updated_at
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;        
        
    /* Public methods */

    public static function handleLogin(\Ia\Entity\User $user,$values,$encrypt=true)
    {
        $auth = \Zend_Auth::getInstance();    
        $authAdapter = new \Ia\Auth\Adapter();
        $authAdapter->setEntity($user)
            ->setIdentityVar('email_address')
            ->setCredentialVar('password');    

        // Set the input credential values
        $authAdapter->setIdentity($values['email_address']);
        if($encrypt)
            $authAdapter->setCredential(md5($values['password']));     
        else       
            $authAdapter->setCredential($values['password']);   

        $result = $auth->authenticate($authAdapter);            
        if (!$result->isValid()) {
            foreach ($result->getMessages() as $message) {
                \Ia_View_Helper_Alert::addAlert($message,'error');
            }
        } else {
            if($persistent = \Ia\Config::get('sessions/persistent')){
                $length = \Ia\Config::get('sessions/length');
                if(!$length)
                    $length = (365 * 24 * 60 * 60);
        		ini_set('session.gc_maxlifetime', $length);
        		session_set_cookie_params($length);
                \Zend_Session::rememberMe($length);
            }
            
            $user = $user->getUserByEmail($values['email_address'],true);

            $logged_in_user = new \Zend_Session_Namespace('logged_in_user');
            $logged_in_user->id = $user->id;

            $redirect = new \Zend_Session_Namespace('auth_login_redirect');
            $redirector = new \Zend_Controller_Action_Helper_Redirector;                
            if(isset($redirect->to)){
                $route = unserialize($redirect->to);
                unset($redirect->to);
                $redirector->gotoRoute($route);
            } else {
                $home_page = \Ia\Config::get('acl/home_page/'.$user->role);
                if($home_page){
                    $redirector->gotoUrl($home_page);
                } else {
                    $redirector->gotoRoute(array('module'=>'default','controller'=>'index','action'=>'index'));
                }
            }
        }
    }

    public function getEmailPreferencesToken()
    {
        return md5($this->email_address.'_IWF_CORE_EMAIL_PREFS_'.gethostname());
    }

    public function getEmailPreferences()
    {
        $allTypes = \Ia\Entity\Message::staticGetAllTypeOptions();
        $email_preferences = unserialize($this->email_preferences);
        if(!is_array($email_preferences)){
            $email_preferences = array();
        }
        foreach($allTypes as $type=>$info){
            if(!isset($email_preferences[$type])){
                $email_preferences[$type] = array('title'=>$info['title'],'optin'=>true);
            }
        }
        return $email_preferences;
    }
    
    public function getRoles()
    {
        return \Ia\Config::get('acl/roles');
    }
    
    public function getUserByEmail($email=null,$activeOnly=false){
        if($email==false){
            throw new \Zend_Exception('Function missing required parameter');
        }
        if($activeOnly)
            $dql = "select u from Ia\Entity\User u WHERE u.active = 1 AND u.email_address='".$email."' AND u.deletedAt IS NULL";
        else
            $dql = "select u from Ia\Entity\User u WHERE u.email_address='".$email."' AND u.deletedAt IS NULL";
        $records = $this->getEntityManager()->createQuery($dql)->execute();
        if(sizeof($records)>0){
            $return = $records[0];
        } else {
            $return = false;
        }    
        return $return;
    } 
    
    /**
    * Returns user for provided E-mail address
    *
    * @return mixed
    */
    public function regenerateToken($id=null){
        $token = md5($id.'_'.rand(100000,999999));
        $user = $this->getEntityManager()->find('Ia\Entity\User', $id);
        $user->token = $token;
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
        return $user;
    }  
    
    
    /**
    * Change user password
    *
    * @return mixed
    */
    public function updatePassword($id=null,$password=null){
        if($id==null || $password==null)
            throw new Zend_Exception('Function missing required parameter');
    
        //update token at same time
        $token = md5(rand(100000,999999));
        
        $user = $this->getEntityManager()->find('Ia\Entity\User', $id);
        if($user){
            $user->pw_reset_required = 0;
            $user->password = md5($password);
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }
        return $user;
    }  
    
     /**
     *
     * \Doctrine\Entity\Manager
     */
    public $em = null;

    /**
     * Get Doctrine Entity Manager
     * @return \Doctrine\Entity\Manager
     */    
    public function getEntityManager() {
        if($this->em===null){
            $dc = \Zend_Registry::get('doctrine');
            $this->em = $dc->getEntityManager();        
        }
        return $this->em;        
    }
    
    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if($property=='name'){
            return $this->first_name.' '.$this->last_name;
        }
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        $this->$property = $value;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function toArray() {
        $vars = get_object_vars($this);
        return $vars;
    }

    /**
     * @ORM\PrePersist 
     * @ORM\PreUpdate
     */
    public function geocode()
    {
        if($this->address_line_1){
            $geocode_key = $this->_generateGeocodeKey();
            if($geocode_key != $this->geocode_key){
                try {
                    $client = new \Zend_Http_Client('http://maps.googleapis.com/maps/api/geocode/json');
                    $urlencodedAddress = urlencode($this->_getAddressString());
                    $client->setParameterGet('sensor', 'false'); // Do we have a GPS sensor? Probably not on most servers.
                    $client->setParameterGet('address', $urlencodedAddress); // Should now be '1600+Amphitheatre+Parkway,+Mountain+View,+CA'
                    $response = $client->request('GET'); // We must send our parameters in GET mode, not POST
                    $this->latitude = json_decode($response->getBody())->results[0]->geometry->location->lat;
                    $this->longitude = json_decode($response->getBody())->results[0]->geometry->location->lng;
                    $this->geocode_key = $geocode_key;
                } catch(Exception $e){
                    $this->geocode_key = null;
                }
            }
        }
    }

    public function getAddressString()
    {
        return $this->_getAddressString();
    }

    protected function _getAddressString()
    {
        return $this->address_line_1.' '.$this->address_line_2.' '.$this->city.' '.$this->state.' '.$this->zip.' '.$this->country;
    }

    protected function _generateGeocodeKey()
    {
        return md5($this->_getAddressString());
    }    
    
    /**
     * Create an entity with the given data
     *
     * @param array $data
     * @return object 
     */
    public function createEntity(array $data)
    {    
        $metadata = $this->getEntityManager()->getClassMetadata(get_class($this));
        if(isset($data['id'])){
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        }
        $entity = $metadata->newInstance();
        return $this->updateEntity($entity,$data);
    } 

    /**
     * Update an entity with the given data
     *
     * @param array $data
     * @return object 
     */
    public function updateEntity($entity, array $data)
    {    
        $metadata = $this->getEntityManager()->getClassMetadata(get_class($this));
        foreach($data as $property => $value){
            if(!$metadata->reflClass->hasProperty($property))
                continue;                
            $metadata->setFieldValue($entity, $property, $value);
        }
        return $entity;
    }    
    
    public function getAllOptions($role = false){
        $options = array();
        $dql = "SELECT e FROM ".get_class($this)." e WHERE e.active=1";
        if(!empty($role)){
            $dql .= " AND e.role = '$role'";
        }
        $users = $this->getEntityManager()->createQuery($dql)->getResult();
        foreach($users as $user){
            $options[$user->id] = $user->last_name.', '.$user->first_name;
        }
        return $options;
    }

    /** 
     * @ORM\PreUpdate 
     */
    public function unsynced()
    {
        $this->synced = 0;
    } 
       
}
