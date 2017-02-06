<?php

namespace PhpExtended\Mega;

/**
 * MegaResponseKey class file.
 *
 * This class represents the key and the node id of the node that contains
 * the key to decode this node's key.
 *
 * @author Anastaszor
 */
class MegaResponseKey
{
	
	/**
	 * The id of the node which contains the key to decode this node's key.
	 *
	 * @var MegaNodeId
	 */
	private $_node_id = null;
	
	/**
	 * The encrypted key, in base64 string.
	 *
	 * @var MegaBase64String
	 */
	private $_node_key = null;
	
	/**
	 * Builds a new MegaResponseKey with the string given by the API.
	 *
	 * @param string $string
	 * @throws MegaException
	 */
	public function __construct($string)
	{
		$pos = strpos($string, ':');
		if($pos === false)
			throw new MegaException(strtr('Impossible to find semicolon from string "{key}".',
				array('{key}' => $string)));
		
		$this->_node_id = new MegaNodeId(substr($string, 0, $pos));
		$this->_node_key = new MegaBase64String(substr($string, $pos+1));
	}
	
	/**
	 * Gets the id of the node able to decrypt this node's key.
	 *
	 * @return MegaNodeId
	 */
	public function getNodeId()
	{
		return $this->_node_id;
	}
	
	/**
	 * Gets the node encrypted key, base64.
	 *
	 * @return MegaBase64String
	 */
	public function getNodeKey()
	{
		return $this->_node_key;
	}
	
}
