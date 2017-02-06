<?php

namespace PhpExtended\Mega;

/**
 * MegaKeyAes128Array32 class file.
 *
 * This class represents a 128 bits AES key, stored into integer array format.
 *
 * @author Anastaszor
 */
class MegaKeyAes128Array32 implements IMegaKeyAes128
{
	
	/**
	 * The integer values, packed.
	 *
	 * @var integer[4]
	 */
	private $_values = array();
	
	/**
	 * Builds a new MegaKeyAes128Array32 with the given integer data.
	 *
	 * @param integer[4] $values
	 * @throws MegaException
	 */
	public function __construct(array $values)
	{
		if(count($values) !== 4)
			throw new MegaException(strtr('Impossible to pack key with "{k}" values in array, must be 4.',
				array('{k}' => count($values))));
		
		foreach($values as $k => $value)
			if(!is_int($value))
				throw new MegaException(strtr('Impossible to read the {k}th value, not an integer.',
					array('{k}' => $k)));
		
		$this->_values = $values;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes128::toArray32()
	 */
	public function toArray32()
	{
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes128::toRawString()
	 */
	public function toRawString()
	{
		return new MegaKeyAes128String(call_user_func_array('pack', array_merge(array('N*'), $this->_values)));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \PhpExtended\Mega\IMegaKeyAes128::__toString()
	 */
	public function __toString()
	{
		return serialize($this->_values);
	}
	
}
