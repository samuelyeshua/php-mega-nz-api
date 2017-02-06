<?php

namespace PhpExtended\Mega;

/**
 * MegaUserId class file.
 *
 * This class represents the id in which all requests are.
 *
 * @author Anastaszor
 */
class MegaUserId
{
	
	/**
	 * The value of the id.
	 *
	 * @var string
	 */
	private $_value = null;
	
	/**
	 * Builds a new MegaFolderId from the given string.
	 *
	 * @param string $id
	 * @throws MegaException if the given id is not recevable.
	 */
	public function __construct($id)
	{
		if(!preg_match('#^[a-zA-Z0-9]{11}$#', $id))
			throw new MegaException(strtr('Invalid user id "{id}".',
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
	
}
