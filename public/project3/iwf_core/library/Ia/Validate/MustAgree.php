<?php

class Ia_Validate_MustAgree extends Zend_Validate_Abstract
{
    /**
     * Error codes
     * @const string
     */
    const MUST_AGREE      = 'mustAgree';

    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::MUST_AGREE      => "You must agree to the terms and conditions of this Web site.",
    );
    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if only if value is true
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if(intval($value)==1){
            return true;
        } else {
            $this->_error(self::MUST_AGREE);
        }
    }
}
