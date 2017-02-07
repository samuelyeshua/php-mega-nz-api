<?php

namespace PhpExtended\Mega;

/**
 * MegaKeyAes256String class file.
 *
 * This class represents a 256 bits AES key, stored into raw string format.
 *
 * @author Anastaszor
 */
class MegaKeyAes256String implements IMegaKeyAes256
{
	
	/**
	 * The key value, packed in string format.
	 *
	 * @var string
	 */
	private $_value = null;
	
	/**
	 * Builds a new MegaKey for AES 256 bits with the given string.
	 *
	 * @param string $string
	 * @throws MegaException
	 */
	public function __construct($string)
	{
		if(strlen($string) !== 32)
			throw new MegaException(strtr('The length of the given string is not 256 bits but "{n}" bits ({c} chars).',
				array('{n}' => 8 * strlen($string), '{c}' => strlen($string))));
		$this->_value = $string;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::toArray32()
	 */
	public function toArray32()
	{
		$string = $this->_value;
		$lenb = strlen($string);
		$padding = ((($lenb + 3) >> 2) * 4) - $lenb;
		if($padding > 0) $string .= str_repeat("\0", $padding);
		return new MegaKeyAes256Array32(array_values(unpack('N*', $string)));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::toRawString()
	 */
	public function toRawString()
	{
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::reduceAes128()
	 */
	public function reduceAes128()
	{
		return $this->toArray32()->reduceAes128();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::getInitializationVector()
	 */
	public function getInitializationVector()
	{
		return $this->toArray32()->getInitializationVector();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::getMetaMac()
	 */
	public function getMetaMac()
	{
		return $this->toArray32()->getMetaMac();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::__toString()
	 */
	public function __toString()
	{
		return $this->_value;
	}
	
}
