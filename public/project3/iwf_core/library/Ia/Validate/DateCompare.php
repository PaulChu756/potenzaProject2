<?php
class Ia_Validate_DateCompare extends Zend_Validate_Abstract
{
    /**
     * Error codes
     * @const string
     */
    const EQUAL_OR_AFTER = 'EqualsOrAfter';
    const EQUAL_OR_EARLIER = 'EqualsOrEarlier';
    const MISSING_TOKEN = 'missingToken';

    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::EQUAL_OR_AFTER    => "The date must be equal or greater than '%token%'",
        self::MISSING_TOKEN     => 'No date was provided to match against',
        self::EQUAL_OR_EARLIER    => "The date must be equal or earlier than '%token%'"
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'token' => '_tokenString',
        'token_value' => '_tokenValue'
    );

    /**
     * Original token against which to validate
     * @var string
     */
    protected $_tokenString;
    protected $_tokenValue;
    protected $_token;
    protected $_compare;

    /**
     * Sets validator options
     *
     * @param  mixed $token
     * @param  mixed $compare
     * @return void
     */
    public function __construct($validate_condition)
    {   
        if(is_array($validate_condition)){
            foreach($validate_condition as $key => $inputs){
                $this->setToken($key, $inputs['element']);
                $this->setCompare($key, $inputs['check_operator']);
            }
        }
    }

    /**
     * Set token against which to compare
     *
     * @param  mixed $token
     * @return Zend_Validate_Identical
     */
    public function setToken($count, $token)
    {
        $this->_token[$count] = $token;
        return $this;
    }

    /**
     * Retrieve token
     *
     * @return string
     */
    public function getToken($count)
    {
        return $this->_token[$count];
    }

    /**
     * Set compare against which to compare
     *
     * @param  mixed $compare
     * @return Zend_Validate_Identical
     */
    public function setCompare($count, $compare)
    {
        $this->_compare[$count] = $compare;
        return $this;
    }

    /**
     * Retrieve compare
     *
     * @return string
     */
    public function getCompare($count)
    {
        return $this->_compare[$count];
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        foreach($this->_token as $count => $token){
            
            $token_value = $context[$token];

            if (empty($token_value)) {
                $this->_error(self::MISSING_TOKEN);
                return false;
            }
            
            $date2 = new Zend_Date($token_value);
            $compare_check = $this->getCompare($count);
            $this->_tokenString = $token;
            $this->_tokenValue = $date2;
            
            if($compare_check == "DateRangeEqualsOrEarlier"){
                if(is_array($value)){
                    foreach($value as $key => $date_value){
                        if(!empty($date_value) && $date_value != "undefined"){
                            $date1 = new Zend_Date($date_value);
                            $equals = $date1->equals($date2);
                            $earlier = $date1->isEarlier($date2);
                            if(!$equals && !$earlier){
                                $this->_error(self::EQUAL_OR_EARLIER);
                                return false;
                            }
                        }
                    }
                } else {
                    $date1 = new Zend_Date($value);
                    $equals = $date1->equals($date2);
                    $earlier = $date1->isEarlier($date2);
                    if(!$equals && !$earlier){
                        $this->_error(self::EQUAL_OR_EARLIER);
                        return false;
                    }
                }
            } else {
                $date1 = new Zend_Date($value);
                if ($compare_check == "EqualsOrAfter"){
                    $equals = $date1->equals($date2);
                    $after = $date1->isLater($date2);
                    if(!$equals && !$after){
                        $this->_error(self::EQUAL_OR_AFTER);
                        return false;
                    }
                } else if ($compare_check == "EqualsOrEarlier"){
                    $equals = $date1->equals($date2);
                    $earlier = $date1->isEarlier($date2);
                    if(!$equals && !$earlier){
                        $this->_error(self::EQUAL_OR_EARLIER);
                        return false;
                    }
                } 
            }
        }
        return true;
    }
}