<?php

namespace PhpExtended\Mega;

/**
 * MegaKeyAes256Array32 class file.
 *
 * This class represents a 256 bits AES key, stored into integer array format.
 *
 * @author Anastaszor
 */
class MegaKeyAes256Array32 implements IMegaKeyAes256
{
	
	/**
	 * The integer values, packed.
	 *
	 * @var integer[8]
	 */
	private $_values = array();
	
	/**
	 * Builds a new MegaKeyAes256Array32 with the given integer data.
	 *
	 * @param integer[8] $values
	 * @throws MegaException
	 */
	public function __construct(array $values)
	{
		if(count($values) !== 8)
			throw new MegaException(strtr('Impossible to pack key with "{k}" values in array, must be 8.',
				array('{k}' => count($values))));
		
		foreach($values as $k => $value)
			if(!is_int($value))
				throw new MegaException(strtr('Impossible to read the {k}th value, not an integer.',
					array('{k}' => $k)));
		
		$this->_values = $values;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::toArray32()
	 */
	public function toArray32()
	{
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::toRawString()
	 */
	public function toRawString()
	{
		return new MegaKeyAes256String(call_user_func_array('pack', array_merge(array('N*'), $this->_values)));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::reduceAes128()
	 */
	public function reduceAes128()
	{
		return new MegaKeyAes128Array32(array(
			$this->_values[0] ^ $this->_values[4],
			$this->_values[1] ^ $this->_values[5],
			$this->_values[2] ^ $this->_values[6],
			$this->_values[3] ^ $this->_values[7],
		));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::getInitializationVector()
	 */
	public function getInitializationVector()
	{
		return new MegaKeyAes128Array32(array($this->_values[4], $this->_values[5], 0, 0));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::getMetaMac()
	 */
	public function getMetaMac()
	{
		return new MegaKeyAes64Array32(array($this->_values[6], $this->_values[7]));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes256::__toString()
	 */
	public function __toString()
	{
		return serialize($this->_values);
	}
	
}
