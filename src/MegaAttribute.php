<?php

namespace PhpExtended\Mega;

/**
 * MegaAttribute class file.
 *
 * This class represents the attributes of a node.
 *
 * @author Anastaszor
 */
class MegaAttribute
{
	
	/**
	 * The name of the node.
	 *
	 * @var string
	 */
	private $_name = null;
	
	/**
	 * unknown data
	 *
	 * @var string
	 */
	private $_c = null;
	
	/**
	 * Builds a new attribute bag for given node.
	 *
	 * @param array $json_data
	 * @throws MegaException if the data is unknown
	 */
	public function __construct(array $json_data)
	{
		foreach($json_data as $key => $value)
		{
			switch($key)
			{
				case 'n':
					$this->_name = $value;
					break;
				case 'c':
					// TODO what is this thing ?
					$this->_c = $value;
					break;
				default:
					throw new MegaException(strtr('Unknown attribute property "{key}" with value "{val}".',
						array('{key}' => $key, '{val}' => $value)), MegaException::EINTERNAL);
			}
		}
	}
	
	/**
	 * Gets the name of the node.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}
	
}
