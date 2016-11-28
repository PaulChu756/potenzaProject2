<?php

class Ia_Validate_Match extends Zend_Validate_Abstract
{
    /**
     * Error codes
     * @const string
     */
    const NOT_SAME      = 'notSame';

    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_SAME      => "The values you entered do not match",
    );
    
    /**
     * Element to match
     * @var string
     */    
    protected $_elementToMatch;

    /**
     * Sets validator options
     *
     * @param  mixed $token
     * @return void
     */
    public function __construct($elementToMatch)
    {
        $this->_elementToMatch = $elementToMatch;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if($value != $context[$this->_elementToMatch]){
            $this->_error(self::NOT_SAME);
            return false;
        }
        
        return true;
    }
}
