<?php

namespace PhpExtended\Mega;

/**
 * MegaFragment class file.
 *
 * This class represents the information given into an url fragment for an url
 * which targets a specific resource in Mega.
 *
 * @author Anastaszor
 * @deprecated
 */
class MegaFragment
{
	
	const TYPE_FOLDER = 'folder';
	const TYPE_FILE = 'file';
	
	/**
	 * The type of the target, either 'file' or 'folder'.
	 *
	 * @var string one of self::TYPE_* constants.
	 */
	private $_type = null;
	
	/**
	 * The file or folder name handle.
	 *
	 * @var string
	 */
	private $_handle = null;
	
	/**
	 * The key to decrypt the file or folder, null if none is provided.
	 *
	 * @var string
	 */
	private $_key = null;
	
	/**
	 * Builds a new MegaFragment object with the given fragment from url.
	 * For remainder, an http url fragment is anything after the # part.
	 *
	 * @param string $url_fragment
	 * @throws MegaException if the fragment cannot be parsed
	 */
	public function __construct($url_fragment)
	{
		if(!is_string($url_fragment))
			throw new MegaException('The given fragment is not a string.', MegaException::EARGS);
		
		$matches = array();
		if(preg_match('#(F?)!([a-zA-Z0-9]+)(!([a-zA-Z0-9_,\\-]+))?#', $url_fragment, $matches))
		{
			$this->_type = ($matches[1] === 'F') ? self::TYPE_FOLDER : self::TYPE_FILE;
			$this->_handle = ($matches[2]);
			if(isset($matches[4])) $this->_key = $matches[4];
		}
		else throw new MegaException(strtr('The given fragment could not be parsed ("{frg}").',
			array('{frg}' => $url_fragment)), MegaException::EARGS);
	}
	
	/**
	 * Gets whether this fragment targets a folder, or not (i.e. targets a file).
	 *
	 * @return boolean
	 */
	public function getIsFolder()
	{
		return $this->_type === self::TYPE_FOLDER;
	}
	
	/**
	 * Gets the file handle for this fragment.
	 *
	 * @return string
	 */
	public function getHandle()
	{
		return $this->_handle;
	}
	
	/**
	 * Gets the key for this fragment ; may be null.
	 *
	 * @return string
	 */
	public function getKey()
	{
		return $this->_key;
	}
	
	/**
	 * Gets a string representation of this url fragment, ready to be inserted
	 * in url http fragment part.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$str = $this->getIsFolder() ? 'F' : '';
		$str .= '!'.$this->getHandle();
		if(!empty($this->getKey()))
			$str .= '!'.$this->getKey();
		return $str;
	}
	
}
