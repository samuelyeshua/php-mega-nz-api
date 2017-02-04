<?php

namespace PhpExtended\Mega;

/**
 * Mega class file.
 *
 * This class represents the API to get resources and contents from Mega urls.
 *
 * @author Anastaszor
 */
class Mega
{
	
	const SERVER_GLOBAL = 'g.api.mega.co.nz';
	const SERVER_EUROPE = 'eu.api.mega.co.nz';
	
	/**
	 * The default server, for unspecified new Mega objects.
	 *
	 * @var string one of Mega::SERVER_* constants.
	 */
	private static $_default_server = self::SERVER_GLOBAL;
	
	/**
	 * Sets the default server for each time a new connection is required.
	 *
	 * @param string $server one of Mega::SERVER_* constants.
	 */
	public static function setDefaultServer($server)
	{
		switch($server)
		{
			case self::SERVER_EUROPE:
			case self::SERVER_GLOBAL:
				self::$_default_server = $server;
		}
	}
	
	/**
	 * Which server to use.
	 *
	 * @var string one of the Mega::SERVER_* constants.
	 */
	private $_server = null;
	
	/**
	 * The sequence number.
	 *
	 * @var integer
	 */
	private $_seqno = null;
	
	/**
	 * Builds a new Mega client API.
	 */
	public function __construct($server = null)
	{
		$this->_seqno = rand(0, PHP_INT_MAX);
		$this->setServer($server);
	}
	
	/**
	 * Sets the default server for this instance of Mega.
	 *
	 * @param string $server one of Mega::SERVER_* constants.
	 */
	public function setServer($server)
	{
		switch($server)
		{
			case self::SERVER_EUROPE:
			case self::SERVER_GLOBAL:
				$this->_server = $server;
				break;
			case null:
				$this->_server = self::$_default_server;
				break;
		}
	}
	
	/**
	 * Gets the fragments parts from given url
	 *
	 * @param string $url
	 * @return MegaFragment
	 * @throws MegaException
	 */
	protected function parseMegaUrl($url)
	{
		if(!is_string($url))
			throw new MegaException('The given url is not a string.', MegaException::EARGS);
		
		$fragment = parse_url($url, PHP_URL_FRAGMENT);
		if($fragment === false)
			throw new MegaException(strtr('Impossible to parse fragment from url "{url}".',
				array('{url}' => $url)), MegaException::EARGS);
		
		return new MegaFragment($fragment);
	}
	
}
