<?php

namespace PhpExtended\Mega;

/**
 * MegaNodeId class file.
 *
 * This class represents the id in which all requests are.
 *
 * @author Anastaszor
 */
class MegaNodeId
{
	
	/**
	 * The value of the id.
	 *
	 * @var string
	 */
	private $_value = null;
	
	/**
	 * Builds a new MegaNodeId from the given string.
	 *
	 * @param string $id
	 * @throws MegaException if the given id is not recevable.
	 */
	public function __construct($id)
	{
		if(!preg_match('#^[a-zA-Z0-9]{8}$#', $id))
			throw new MegaException(strtr('Invalid folder id "{id}".',
				array('{id}' => $id)));
		$this->_value = $id;
	}
	
	/**
	 * Gets the value of the id.
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->_value;
	}
	
	/**
	 * Gets whether two ids are equals. They are equals if they share the same
	 * id value.
	 *
	 * @param MegaNodeId $other
	 */
	public function equals(MegaNodeId $other)
	{
		return 0 === strcmp($this->getValue(), $other->getValue());
	}
	
	/**
	 * Returns a string representation of this node id : the id value.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->_value;
	}
	
}
