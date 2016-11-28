<?php

class Ia_Validate_Unique extends Zend_Validate_Abstract
{
	/**
	 * Error codes
	 * @const string
	 */
	const UNIQUE = 'unique';

	/**
	 * Ia_Validate_Unique constructor.
	 *
	 * @param $options - entity class, attribute and record id
	 */
	public function __construct($options){
		$this->_entityClass = $options['entityClass'];
		$this->_attribute = $options['attribute'];
		$this->_entityId = $options['id'];
	}

	/**
	 * Error messages
	 * @var array
	 */
	protected $_messageTemplates = [
		self::UNIQUE => "The value must be unique, duplicate found in database.",
	];

	/**
	 * Defined by Zend_Validate_Interface
	 *
	 * Returns true if there are no unique rows with same attribute
	 *
	 * @param  mixed $value
	 * @param  array $context
	 * @return boolean
	 */
	public function isValid($value, $context = null)
	{
		$em = \Zend_Registry::get('doctrine');
		$em = $em->getEntityManager();

		$uniqueMatch = $em->getRepository($this->_entityClass)->findOneBy([$this->_attribute => $value]);
		if(empty($uniqueMatch)) {
			return true;
		} else {
			if($this->_entityId != $uniqueMatch->id) {
				$this->_error(self::UNIQUE);
			} else {
				return true;
			}
		}

	}
}
