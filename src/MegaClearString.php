<?php

namespace PhpExtended\Mega;

/**
 * MegaClearString class file.
 *
 * This class represents a string in pure format.
 *
 * @author Anastaszor
 */
class MegaClearString implements IMegaString
{
	
	/**
	 * The value, a clear string.
	 *
	 * @var string
	 */
	private $_value = null;
	
	/**
	 * Builds a new MegaClearString
	 *
	 * @param string $string
	 */
	public function __construct($string)
	{
		$this->_value = $string;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaString::toBase64()
	 */
	public function toBase64()
	{
		return new MegaBase64String(base64_encode($this->_value));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaString::toClear()
	 */
	public function toClear()
	{
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaString::__toString()
	 */
	public function __toString()
	{
		return $this->_value;
	}
	
}
