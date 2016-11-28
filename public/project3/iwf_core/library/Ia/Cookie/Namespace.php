<?php
/**
 * Ia_Cookie_Namespace
 */
class Ia_Cookie_Namespace
{
	protected $_namespace;
	protected $_session;
	protected $_domainName;

    public function __construct($namespace = 'Default')
    {
	
		if(!isset($_SERVER['HTTP_HOST']))
			throw new Zend_Exception('Cannot use Cookie storage when request object not present');
			
		$this->_namespace = $namespace;
		$this->_session = new Zend_Session_Namespace('Ia_Cookie_'.$namespace);
		
		$parts = explode(".",$_SERVER['HTTP_HOST']);
		$length = count($parts);
		$this->_domainName = $parts[$length-2].".".$parts[$length-1];
		
        
		if(isset($this->_session->cookie[$this->_namespace]['unset_queue'])){
			foreach($this->_session->cookie[$this->_namespace]['unset_queue'] as $key=>$value){
				setcookie($this->_namespace.'['.$key.']', null, time() - 60, "/", ".".$this->_domainName, 0); // 86400 = 1 day
				unset($_COOKIE[$this->_namespace][$key]);
			}
		}
		
		$this->_session->cookie = $_COOKIE;

    }

    public function __get($name)
    {
        return unserialize(base64_decode($this->_session->cookie[$this->_namespace][$name]));
    }

    public function __set($name, $value)
    {
        setcookie($this->_namespace.'['.$name.']', base64_encode(serialize($value)), 
            time() + ((86400 * 365)), "/", ".".$this->_domainName, 0); // 86400 = 1 day
        $this->_session->cookie[$this->_namespace][$name] = base64_encode(serialize($value));	
    }

    public function __isset($name)
    {
        return isset($this->_session->cookie[$this->_namespace][$name]);
    }

    public function __unset($name)
    {
        $this->_session->cookie[$this->_namespace][$name] = null;
        $this->_session->cookie[$this->_namespace]['unset_queue'][$name] = null;
    }

}