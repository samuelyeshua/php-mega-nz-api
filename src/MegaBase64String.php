<?php

namespace PhpExtended\Mega;

/**
 * MegaBase64String class file.
 *
 * This class represents a string in base64 format.
 *
 * @author Anastaszor
 */
class MegaBase64String implements IMegaString
{
	
	/**
	 * The value, a string encoded base64.
	 *
	 * @var string
	 */
	private $_value = null;
	
	/**
	 * Builds a new MegaBase64String base64
	 *
	 * @param string $string
	 * @throws MegaException
	 */
	public function __construct($string)
	{
		if(empty($string))
			throw new MegaException(strtr('The given value is empty.'));
		if(!is_string($string))
			throw new MegaException(strtr('The given value is not a string, but a {thing}',
				array('{thing}' => gettype($string))), MegaException::EARGS);
		// mega specific additional encoding
		$string = str_replace(array('-', '_', ','), array('+', '/', ''), $string);
		$modlen = strlen($string) % 4;
		if($modlen === 2) $string .= '==';
		if($modlen === 3) $string .= '=';
		if(!preg_match('#^([A-Za-z0-9+/]{4})+([A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$#', $string))
			throw new MegaException(strtr('The given value is not base64 encoded ("{val}").',
				array('{val}' => $string)));
		
		$this->_value = $string;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaString::toBase64()
	 */
	public function toBase64()
	{
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaString::toClear()
	 */
	public function toClear()
	{
		return new MegaClearString(base64_decode($this->_value));
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
