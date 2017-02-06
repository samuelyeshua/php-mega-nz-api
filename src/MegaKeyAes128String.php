<?php

namespace PhpExtended\Mega;

/**
 * MegaKeyAes128String class file.
 *
 * This class represents a 128 bits AES key, stored into raw string format.
 *
 * @author Anastaszor
 */
class MegaKeyAes128String implements IMegaKeyAes128
{
	
	/**
	 * The key value, packed in string format.
	 *
	 * @var string
	 */
	private $_value = null;
	
	/**
	 * Builds a new MegaKey for AES 128 bits with the given string.
	 *
	 * @param string $string
	 * @throws MegaException
	 */
	public function __construct($string)
	{
		if(strlen($string) !== 16)
			throw new MegaException(strtr('The length of the given string is not 128 bits but "{n}" bits ({c} chars).',
				array('{n}' => 8 * strlen($string), '{c}' => strlen($string))));
		$this->_value = $string;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes128::toArray32()
	 */
	public function toArray32()
	{
		$string = $this->_value;
		$lenb = strlen($string);
		$padding = ((($lenb + 3) >> 2) * 4) - $lenb;
		if($padding > 0) $string .= str_repeat("\0", $padding);
		return new MegaKeyAes128Array32(array_values(unpack('N*', $string)));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes128::toRawString()
	 */
	public function toRawString()
	{
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes128::__toString()
	 */
	public function __toString()
	{
		return $this->_value;
	}
	
}
