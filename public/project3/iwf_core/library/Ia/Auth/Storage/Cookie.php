<?php

class Ia_Auth_Storage_Cookie extends Ia_Auth_Storage_Session
{
    /**
     * Default session namespace
     */
    const NAMESPACE_DEFAULT = 'Ia_Auth';

    /**
     * Default session object member name
     */
    const MEMBER_DEFAULT = 'storage';

    /**
     * Object to proxy $_SESSION storage
     *
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     * Session namespace
     *
     * @var mixed
     */
    protected $_namespace;

    /**
     * Session object member
     *
     * @var mixed
     */
    protected $_member;

    /**
     * Sets session storage options and initializes session namespace object
     *
     * @param mixed $namespace
     * @param mixed $member
     */
    public function __construct($namespace = self::NAMESPACE_DEFAULT, $member = self::MEMBER_DEFAULT)
    {
        $this->_namespace = $namespace;
        $this->_member    = $member;
        $this->_session   = $this->getCookie($this->_namespace);
    }
	
	public function getCookie($namespace){
	
		if(!isset($_SERVER['HTTP_HOST']))
			return new Zend_Session_Namespace($namespace);
		else
			return new Ia_Cookie_Namespace($namespace);
	}

    /**
     * Returns the session namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Returns the name of the session object member
     *
     * @return string
     */
    public function getMember()
    {
        return $this->_member;
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return !isset($this->_session->{$this->_member});
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return mixed
     */
    public function read()
    {
        return $this->_session->{$this->_member};
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $this->_session->{$this->_member} = $contents;
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return void
     */
    public function clear()
    {
        if(isset($this->_session->{$this->_member})){
            unset($this->_session->{$this->_member});
            header('Location: '.(($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/'));
            exit;
        }
    }
}