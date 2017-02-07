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
	 * The folder id where the connection lands.
	 *
	 * @var MegaNodeId
	 */
	private $_container_id = null;
	
	/**
	 * The decryption key where for the folder.
	 *
	 * @var IMegaKeyAes128
	 */
	private $_container_key = null;
	
	/**
	 * The node hierarchy for this target folder.
	 *
	 * @var MegaNodeHierarchy
	 */
	private $_node_hierarchy = null;
	
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
	 *
	 * @param string $target_folder_url
	 * @throws MegaException if the target folder url cannot be parsed
	 */
	public function __construct($target_folder_url, $username = null, $password = null)
	{
		if(!extension_loaded('mcrypt'))
			throw new MegaException('The mcrypt extension should be enabled.', MegaException::EINTERNAL);
		if(!extension_loaded('curl'))
			throw new MegaException('The curl extension should be enabled.', MegaException::EINTERNAL);
		if(!extension_loaded('openssl'))
			throw new MegaException('The openssl extension should be enabled.', MegaException::EINTERNAL);
		
		if(!is_string($target_folder_url))
			throw new MegaException('The given url is not a string.', MegaException::EARGS);
		
		$parts = parse_url($target_folder_url);
		if(isset($parts['host']))
			$this->setServer($parts['host']);
		if(!isset($parts['fragment']))
			throw new MegaException(strtr('Impossible to parse fragment from url "{url}".',
				array('{url}' => $target_folder_url)), MegaException::EARGS);
		
		$matches = array();
		if(preg_match('#^F!([a-zA-Z0-9]{8})!([a-zA-Z0-9_,\\-]{22})#', $parts['fragment'], $matches))
		{
			$this->_container_id = new MegaNodeId($matches[1]);
			$key_b64 = new MegaBase64String($matches[2]);
			$this->_container_key = new MegaKeyAes128String($key_b64->toClear()->__toString());
		}
		else throw new MegaException(strtr('Impossible to parse fragment values "{frg}".',
			array('{frg}' => $parts['fragment'])), MegaException::EARGS);
		
		$this->_seqno = rand(0, PHP_INT_MAX);
	}
	
	/**
	 * Initializes the known folders and the root folder by fetching information
	 * from the api.
	 */
	protected function init()
	{
		if($this->_node_hierarchy !== null) return;
		// protection anti recursion, like ensure init
		$this->_node_hierarchy = false;
		$this->getFileInfo($this->_container_id);
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
			default:
				$this->_server = self::$_default_server;
				break;
		}
	}
	
	public function login($username, $password = null)
	{
		if($username === null) $password = null;
		$password_aes = $this->a32ToStr($this->prepareKey($this->strToA32($password)));
		$userhash = $this->stringHash(strtolower($username), $password_aes);
		
		$args = array(
			'a' => 'us',
			'user' => $username,
			'uh' => $userhash,
		);
		
		$payload = json_encode(array($args));
		$url = 'https://'.$this->_server.'/cs?id='.($this->_seqno++);
		if(!empty($this->_user_session_id))
			$url .= '&sid='.$this->_user_session_id;
		
		var_dump($url, $payload);
		$json_response = $this->request($url, $payload);
		var_dump($json_response);
		
		$response = json_decode($json_response);
		if($response === false)
			throw new MegaException('Impossible to decode the json response from the server.', MegaException::EINTERNAL);
		
		var_dump($response);
		
		if(isset($response[0]) && $response[0] !== 0)
			throw new MegaException(null, $response[0]);
		
	}
	
	/**
	 * Gets the root node of this folder.
	 *
	 * @return MegaNode
	 * @throws MegaException
	 */
	public function getRootNodeInfo()
	{
		$this->init();
		
		return $this->_node_hierarchy->getRoot();
	}
	
	/**
	 * Gets file information from
	 *
	 * @param MegaNodeId $node_id the id of target node.
	 */
	public function getFileInfo(MegaNodeId $node_id)
	{
		$this->init();
		
		// inner hierarchy cache
		if($this->_node_hierarchy !== null && $this->_node_hierarchy !== false)
		{
			$retrieved_node = $this->_node_hierarchy->retrieve($node_id);
			if($retrieved_node !== null)
				return $retrieved_node;
		}
		
		// not found in cache : get the folders data
		
		$args = array(
			'a' => 'f',	// folder?
			'c' => 1,	// ???
			'r' => 0,	// recursive
			'ca' => 0,
		);
		
		// dont need the node id the first time we want the root folder
		if(!$this->_container_id->equals($node_id))
			$args['n'] = $node_id->getValue();
// 		$args = array(
// 			'a' => 'g',
// 			'p' => $fragment->getHandle(),
// 			'ssl' => '1',
// 		);
		
		$payload = json_encode(array($args));
		$url = 'https://'.$this->_server.'/cs?id='.($this->_seqno++);
		if(!empty($this->_user_session_id))
			$url .= '&sid='.$this->_user_session_id;
		$url .= '&n='.$this->_container_id->getValue();
		$url .= '&lang=en&domain=meganz';
		
		$json_response = $this->request($url, $payload);
		if(is_numeric($json_response))
			throw new MegaException(null, $json_response);
		
		$response = json_decode($json_response, true);
		if($response === false)
			throw new MegaException('Impossible to decode the json response from the server.', MegaException::EINTERNAL);
		
		if(!isset($response[0]))
			throw new MegaException('Impossible to decode the json data from the server.', MegaException::EINTERNAL);
		
		if(is_int($response[0]))
			throw new MegaException(null, $response[0]);
		
		$response = new MegaResponseNodelist($response[0]);
		
		$rootFolder = $response->getRootNode();
		
		$decoder = new MegaResponseNodeDecoder($this->_container_key);
		
		$clearFolder = $decoder->decode($rootFolder);
		
		if($this->_node_hierarchy === false)
		{
			$this->_node_hierarchy = new MegaNodeHierarchy($clearFolder);
		}
		else
		{
			$this->_node_hierarchy->add($clearFolder);
		}
		
		foreach($response->getNonrootNodes() as $otherFolder)
		{
			$other_clear = $decoder->decode($otherFolder);
			
			$this->_node_hierarchy->add($other_clear);
		}
		
		return $this->_node_hierarchy->retrieve($node_id);
	}
	
	
	
	public function downloadFile($url, MegaNode $node)
	{
		$fragment = $this->parseMegaUrl($url);
		
		$this->downloadFileFromFragment($fragment, $node);
	}
	
	protected function downloadFileFromFragment(MegaFragment $fragment, MegaNode $node)
	{
		$args = array(
			'a' => 'g',
			'g' => 1,
			'n' => $node->h(),
			'ssl' => 2,
		);
		
		$payload = json_encode(array($args));
		$url = 'https://'.$this->_server.'/cs?id='.($this->_seqno++);
		if(!empty($this->_user_session_id))
			$url .= '&sid='.$this->_user_session_id;
		$url .= '&n='.$fragment->getHandle();
		$url .= '&lang=en&domain=meganz';
		
		$json_response = $this->request($url, $payload);
		
		$response = json_decode($json_response, true);
		if($response === false)
			throw new MegaException('Impossible to decode the json response from the server.', MegaException::EINTERNAL);
		
		if(!isset($response[0]))
			throw new MegaException('Impossible to decode the json data from the server.', MegaException::EINTERNAL);
		
		if(is_int($response[0]))
			throw new MegaException(null, $response[0]);
		
		$encrypted_response = new MegaEncryptedFileLocation($response[0]);
		
		// TODO change to be able to stream data instead
		$encoded_data = $this->request($encrypted_response->g(), null);
		
		var_dump($encoded_data);
		
		$clear_data = $this->decryptAesCtr($encoded_data, $this->a32ToStr($node->getKey()), $this->a32ToStr($node->getInitializationVector()));
		
		return $clear_data;
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
	
	/**
	 * Gets a 32 bits word array from strings. The string is padded at the end
	 * to have a length multiple of 32.
	 *
	 * @param string $string
	 * @param 32 bits words array
	 */
	protected function strToA32($string)
	{
		$lenb = strlen($string);
		$padding = ((($lenb + 3) >> 2) * 4) - $lenb;
		if($padding > 0)
			$string .= str_repeat("\0", $padding);
		return array_values(unpack('N*', $string));
	}
	
	/**
	 * Converts an array of 32 bits words into a string
	 *
	 * @param array $_32b_words_be
	 * @return string the packed string
	 */
	protected function a32ToStr(array $_32b_words_be)
	{
		return call_user_func_array('pack', array_merge(array('N*'), $_32b_words_be));
	}
	
	protected function a32ToBase64(array $_32b_words_be)
	{
		return $this->base64Urlencode($this->a32ToStr($_32b_words_be));
	}
	
	protected function base64ToA32($string)
	{
		return $this->strToA32($this->base64Urldecode($string));
	}
	
	protected function base64Urlencode($string)
	{
		$data = base64_encode($string);
		return str_replace(array('+', '/', '='), array('-', '_', ''), $data);
	}
	
	protected function base64Urldecode($string)
	{
		$string .= substr('==', (2 - strlen($string) * 3) % 4);
		$data = str_replace(array('-', '_', ','), array('+', '/', ''), $string);
		return base64_decode($data);
	}
	
	protected function prepareKey(array $_32b_words_be)
	{
		$total = count($_32b_words_be);
		$pkey = array(0x93C467E3, 0x7DB0C7A4, 0xD1BE3F81, 0x0152CB56);
		
		for($r = 0; $r < 0x10000; $r++)
		{
			for($j = 0; $j < $total; $j += 4)
			{
				$key = array(0, 0, 0, 0);
				
				for ($i = 0; $i < 4; $i++)
				{
					if($i + $j < $total)
					{
						$key[$i] = $_32b_words_be[$i + $j];
					}
				}
				
				$pkey = $this->encryptAesCbcA32($pkey, $key);
			}
		}
		
		return $pkey;
	}
	
	protected function decryptKey(array $_32b_words_be, $aes_key)
	{
		$x = array();
		
		for($i = 0; $i < count($_32b_words_be); $i += 4)
		{
			$x = array_merge($x, $this->decryptAesCbcA32(array_slice($_32b_words_be, $i, 4), $aes_key));
		}
		
		return $x;
	}
	
	protected function stringHash($string, $aes_key)
	{
		$string_a32 = $this->strToA32($string);
		$h32 = array(0, 0, 0, 0);
		
		for($i = 0; $i < count($string_a32); $i++)
		{
			$h32[$i & 3] ^= $string_a32[$i];
		}
		
		$k32 = $this->a32ToStr($h32);
		for($i = 0; $i < 0x4000; $i++)
		{
			$k32 = $this->encryptAesCbc($aes_key, $k32);
		}
		
		$l32 = $this->strToA32($k32);
		return $this->a32ToBase64(array($l32[0], $l32[2]));
	}
	
	/**
	 * Encrypts the given data with the given key and a zero initialization
	 * vector.
	 *
	 * @param string $aes_key
	 * @param string $data
	 * @return string
	 */
	protected function encryptAesCbc($aes_key, $data)
	{
		$keysize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = str_repeat("\0", $keysize);
		return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $aes_key, $data, MCRYPT_MODE_CBC, $iv);
	}
	
	/**
	 * Decrypts the given data with the given key and a zero initialization
	 * vector.
	 *
	 * @param string $aes_key
	 * @param string $data
	 * @return string
	 */
	protected function decryptAesCbc($aes_key, $data)
	{
		$keysize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = str_repeat("\0", $keysize);
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $aes_key, $data, MCRYPT_MODE_CBC, $iv);
	}
	
	/**
	 * Encrypts the given data with the given 32b word array and a zero
	 * initialization vector.
	 *
	 * @param string $aes_key
	 * @param string[] $_32b_words_data
	 * @return string[]
	 */
	protected function encryptAesCbcA32($aes_key, array $_32b_words_data)
	{
		return $this->strToA32($this->encryptAesCbc($aes_key, $this->a32ToStr($_32b_words_data)));
	}
	
	/**
	 * Decrypts the given data with the given 32b word array and a zero
	 * initialization vector.
	 *
	 * @param array $_32b_words_data
	 * @param string[] $aes_key
	 * @return string[]
	 */
	protected function decryptAesCbcA32(array $_32b_words_data, $aes_key)
	{
		return $this->strToA32($this->decryptAesCbc($aes_key, $this->a32ToStr($_32b_words_data)));
	}
	
	/**
	 * Decrypts the given data with the given key and initialization vector.
	 *
	 * @param string $encoded_data
	 * @param string $key
	 * @param string $iv
	 */
	protected function decryptAesCtr($encoded_data, $key, $iv)
	{
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encoded_data, 'ctr', $iv);
	}
	
	/**
	 * Decrypts the attribute values from the encrypted string.
	 *
	 * @param string $encrypted_attr
	 * @param string[] $aes_key
	 * @return MegaAttribute
	 * @throws MegaException
	 */
	protected function decryptAttribute($encrypted_attr, array $aes_key)
	{
		$attr = trim($this->decryptAesCbc($this->a32ToStr($aes_key), $encrypted_attr));
		var_dump($attr);
		if(substr($attr, 0, 6) !== 'MEGA{"')
			throw new MegaException('Impossible to decrypt attribute, do you have the right key ?');
		
		return new MegaAttribute(json_decode(substr($attr, 4), true));
	}
	
}
