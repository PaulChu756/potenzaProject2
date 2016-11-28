<?php

namespace Ia\Auth;

class Adapter implements \Zend_Auth_Adapter_Interface
{
    /**
     * $_entity - the table name to check
     *
     * @var string
     */
    protected $_entity = null;

    /**
     * $_identityVar - the column to use as the identity
     *
     * @var string
     */
    protected $_identityVar = null;

    /**
     * $_credentialVars - columns to be used as the credentials
     *
     * @var string
     */
    protected $_credentialVar = null;

    /**
     * $_identity - Identity value
     *
     * @var string
     */
    protected $_identity = null;

    /**
     * $_credential - Credential values
     *
     * @var string
     */
    protected $_credential = null;

    /**
     * $_credentialTreatment - Treatment applied to the credential, such as MD5() or PASSWORD()
     *
     * @var string
     */
    protected $_credentialTreatment = null;

    /**
     * $_authenticateResultInfo
     *
     * @var array
     */
    protected $_authenticateResultInfo = null;

    /**
     * $_resultRow - Results of database authentication query
     *
     * @var array
     */
    protected $_resultRow = null;

    /**
     * $_ambiguityIdentity - Flag to indicate same Identity can be used with
     * different credentials. Default is FALSE and need to be set to true to
     * allow ambiguity usage.
     *
     * @var boolean
     */
    protected $_ambiguityIdentity = false;

    /**
     * __construct() - Sets configuration options
     *
     * @param  Zend_Db_Adapter_Abstract $zendDb If null, default database adapter assumed
     * @param  string                   $tableName
     * @param  string                   $identityVar
     * @param  string                   $credentialVar
     * @param  string                   $credentialTreatment
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * setTableName() - set the table name to be used in the select query
     *
     * @param  string $entity
     * @return Ia\Auth\Adapter Provides a fluent interface
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * setIdentityVar() - set the column name to be used as the identity column
     *
     * @param  string $identityVar
     * @return Ia\Auth\Adapter Provides a fluent interface
     */
    public function setIdentityVar($identityVar)
    {
        $this->_identityVar = $identityVar;
        return $this;
    }

    /**
     * setCredentialVar() - set the column name to be used as the credential column
     *
     * @param  string $credentialVar
     * @return Ia\Auth\Adapter Provides a fluent interface
     */
    public function setCredentialVar($credentialVar)
    {
        $this->_credentialVar = $credentialVar;
        return $this;
    }

    /**
     * setCredentialTreatment() - allows the developer to pass a parameterized string that is
     * used to transform or treat the input credential data.
     *
     * In many cases, passwords and other sensitive data are encrypted, hashed, encoded,
     * obscured, or otherwise treated through some function or algorithm. By specifying a
     * parameterized treatment string with this method, a developer may apply arbitrary SQL
     * upon input credential data.
     *
     * Examples:
     *
     *  'PASSWORD(?)'
     *  'MD5(?)'
     *
     * @param  string $treatment
     * @return Ia\Auth\Adapter Provides a fluent interface
     */
    public function setCredentialTreatment($treatment)
    {
        $this->_credentialTreatment = $treatment;
        return $this;
    }

    /**
     * setIdentity() - set the value to be used as the identity
     *
     * @param  string $value
     * @return Ia\Auth\Adapter Provides a fluent interface
     */
    public function setIdentity($value)
    {
        $this->_identity = $value;
        return $this;
    }

    /**
     * setCredential() - set the credential value to be used, optionally can specify a treatment
     * to be used, should be supplied in parameterized form, such as 'MD5(?)' or 'PASSWORD(?)'
     *
     * @param  string $credential
     * @return Ia\Auth\Adapter Provides a fluent interface
     */
    public function setCredential($credential)
    {
        $this->_credential = $credential;
        return $this;
    }

    /**
     * setAmbiguityIdentity() - sets a flag for usage of identical identities
     * with unique credentials. It accepts integers (0, 1) or boolean (true,
     * false) parameters. Default is false.
     *
     * @param  int|bool $flag
     * @return Ia\Auth\Adapter
     */
    public function setAmbiguityIdentity($flag)
    {
        if (is_integer($flag)) {
            $this->_ambiguityIdentity = (1 === $flag ? true : false);
        } elseif (is_bool($flag)) {
            $this->_ambiguityIdentity = $flag;
        }
        return $this;
    }
    /**
     * getAmbiguityIdentity() - returns TRUE for usage of multiple identical
     * identies with different credentials, FALSE if not used.
     *
     * @return bool
     */
    public function getAmbiguityIdentity()
    {
        return $this->_ambiguityIdentity;
    }

    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $this->_authenticateSetup();
        //$record = $this->_entity->em->getRepository(get_class($this->_entity))->findOneBy(array($this->_identityVar => $this->_identity));
        $record = \Zend_Registry::get('doctrine')
                    ->getEntityManager()
                    ->getRepository(get_class($this->_entity))
                    ->findOneBy(array(
                    $this->_identityVar => $this->_identity,
                    $this->_credentialVar => $this->_credential,
                    'active' => true
                    ),array('id'=>'DESC'));
        if(!$record || $record->deletedAt){ 
            $this->_authenticateResultInfo['code'] = \Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            \Ia\Log::write('Unsuccessful login attempt for '.$this->_identity,null,null,'WARNING');
            return $this->_authenticateCreateAuthResult();        
        } elseif($record->active==0) {
            $this->_authenticateResultInfo['code'] = \Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            if($record->email_confirmed==0)
                $this->_authenticateResultInfo['messages'][] = 'You must confirm your E-mail before you can log in.';
            else
                $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            \Ia\Log::write('Unsuccessful login attempt for '.$this->_identity,null,null,'WARNING');
            return $this->_authenticateCreateAuthResult();        
        }
        \Ia\Log::write('Successful login',null,$record->id,'SUCCESS');  //log normally gets auth user automatically, but in this case the auth object has not yet been placed in registry
        $this->_authenticateResultInfo['code'] = \Zend_Auth_Result::SUCCESS;
        $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->_authenticateCreateAuthResult();
    }

    /**
     * _authenticateSetup() - This method abstracts the steps involved with
     * making sure that this adapter was indeed setup properly with all
     * required pieces of information.
     *
     * @throws Zend_Auth_Adapter_Exception - in the event that setup was not done properly
     * @return true
     */
    protected function _authenticateSetup()
    {
        $exception = null;

        if ($this->_entity == '') {
            $exception = 'A table must be supplied for the Ia\Auth\Adapter authentication adapter.';
        } elseif ($this->_identityVar == '') {
            $exception = 'An identity column must be supplied for the Ia\Auth\Adapter authentication adapter.';
        } elseif ($this->_credentialVar == '') {
            $exception = 'A credential column must be supplied for the Ia\Auth\Adapter authentication adapter.';
        } elseif ($this->_identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with Ia\Auth\Adapter.';
        } elseif ($this->_credential === null) {
            $exception = 'A credential value was not provided prior to authentication with Ia\Auth\Adapter.';
        }

        if (null !== $exception) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception($exception);
        }

        $this->_authenticateResultInfo = array(
            'code'     => \Zend_Auth_Result::FAILURE,
            'identity' => $this->_identity,
            'messages' => array()
            );

        return true;
    }

    /**
     * _authenticateCreateAuthResult() - Creates a Zend_Auth_Result object from
     * the information that has been collected during the authenticate() attempt.
     *
     * @return Zend_Auth_Result
     */
    protected function _authenticateCreateAuthResult()
    {
        return new \Zend_Auth_Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity'],
            $this->_authenticateResultInfo['messages']
            );
    }
    
}