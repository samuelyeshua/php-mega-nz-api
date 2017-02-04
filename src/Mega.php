<?php

namespace PhpExtended\Mega;

use PhpExtended\RootCacert\CacertBundle;

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
	 * The user session id.
	 *
	 * @var string
	 */
	private $_user_session_id = null;
	
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
	
	public function getFileInfo($url)
	{
		$fragment = $this->parseMegaUrl($url);
		
		return $this->getFileInfoFromFragment($fragment);
	}
	
	protected function getFileInfoFromFragment(MegaFragment $fragment)
	{
		$args = array(
			'a' => 'g',
			'p' => $fragment->getHandle(),
			'ssl' => '1',
		);
		
		$payload = json_encode($args);
		$url = 'https://'.$this->_server.'/cs?id='.($this->_seqno++);
		if(!empty($this->_user_session_id))
			$url .= '&sid='.$this->_user_session_id;
		
		$json_response = $this->request($url, $payload);
		
		$response = json_decode($json_response);
		if($response === false)
			throw new MegaException('Impossible to decode the json response from the server.', MegaException::EINTERNAL);
		
		var_dump($response);die();
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
	
	/**
	 * Executes a given request to megaupload via cURL.
	 *
	 * @param string $url
	 * @param string $payload
	 * @return string the contents
	 * @throws MegaException
	 */
	protected function request($url, $payload)
	{
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		
		$cacert_path = CacertBundle::getFilePath();
		
		curl_setopt($ch, CURLOPT_CAINFO, $cacert_path);
		curl_setopt($ch, CURLOPT_CAPATH, $cacert_path);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/json'));
		
		$contents = curl_exec($ch);
		if($contents === false)
		{
			$code = curl_errno($ch);
			$message = curl_error($ch);
			curl_close($ch);
			throw new MegaException($message, $code);
		}
		
		curl_close($ch);
		
		return $contents;
	}
	
}
