<?php

namespace PhpExtended\Mega;

class MegaEncryptedFileLocation
{
	
	private $_s = null;
	
	private $_at = null;
	
	private $_msd = null;
	
	private $_tl = null;
	
	private $_g = null;
	
	private $_fa = null;
	
	/**
	 * Builds a new Mega Encrypted File Location object.
	 *
	 * @param array $json_data
	 * @throws MegaException if the data is unknown.
	 */
	public function __construct(array $json_data)
	{
		foreach($json_data as $key => $value)
		{
			switch($key)
			{
				case 's':
					$this->_s = $value;
					break;
				case 'at':
					$this->_at = $value;
					break;
				case 'msd':
					$this->_msd = $value;
					break;
				case 'g':
					$this->_g = $value;
					break;
				case 'tl':
					$this->_tl = $value;
					break;
				case 'fa':
					$this->_fa = $value;
					break;
				default:
					throw new MegaException(strtr('Unknown attribute property "{key}" with value "{val}".',
						array('{key}' => $key, '{val}' => $value)), MegaException::EINTERNAL);
			}
		}
	}
	
	public function s()
	{
		return $this->_s;
	}
	
	public function at()
	{
		return $this->_at;
	}
	
	public function msd()
	{
		return $this->_msd;
	}
	
	public function g()
	{
		return $this->_g;
	}
	
	public function tl()
	{
		return $this->_tl;
	}
	
	public function fa()
	{
		return $this->_fa;
	}
	
	
}
