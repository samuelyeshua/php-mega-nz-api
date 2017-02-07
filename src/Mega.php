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
		
		return $this->getFileInfoWithChildren($node_id);
	}
	
	protected function getFileInfoWithChildren(MegaNodeId $node_id)
	{
		
		// not found in cache : get the folders data
		
		$args = array(
			'a' => 'f',	// folder?
			'c' => 1,	// ???
			'r' => 1,	// recursive
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
		
		var_dump($url, $payload);
		$json_response = $this->request($url, $payload);
		var_dump($json_response);
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
	
	/**
	 * Gets the children of given node.
	 *
	 * @param MegaNode $node
	 * @return MegaNode[]
	 */
	public function getChildren(MegaNode $node)
	{
		$children = $this->_node_hierarchy->getChildren($node->getNodeId());
		if(count($children) === 0)
		{
			$info = $this->getFileInfoWithChildren($node->getNodeId());
			var_dump($info);die();
			$children = $this->_node_hierarchy->getChildren($node->getNodeId());
		}
		return $children;
	}
	
	/**
	 * Gets the raw data for the file represented by given node.
	 *
	 * @param MegaNode $node
	 * @return string binary raw data for the decoded file
	 * @throws MegaException
	 */
	public function downloadFile(MegaNode $node)
	{
		$args = array(
			'a' => 'g',
			'g' => 1,
			'n' => $node->getNodeId()->getValue(),
			'ssl' => 2,
		);
		
		$payload = json_encode(array($args));
		$url = 'https://'.$this->_server.'/cs?id='.($this->_seqno++);
		if(!empty($this->_user_session_id))
			$url .= '&sid='.$this->_user_session_id;
		$url .= '&n='.$this->_container_id->__toString();
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
		
		$file_decoder = new MegaResponseFileDecoder($node);
		
		$clear_data = $file_decoder->decode($encoded_data);
		
		return $clear_data;
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
