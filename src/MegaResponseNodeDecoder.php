<?php

namespace PhpExtended\Mega;

/**
 * MegaResponseNodeDecoder class file.
 *
 * This class is to decrypt the metadata of other nodes from the key of parent
 * node.
 *
 * @author Anastaszor
 */
class MegaResponseNodeDecoder
{
	
	/**
	 * The key to decode the other nodes.
	 *
	 * @var IMegaKeyAes128
	 */
	private $_key = null;
	
	/**
	 * Builds a new MegaResponseNodeDecoder object with the given master key.
	 *
	 * @param IMegaKeyAes128 $decoding_key
	 */
	public function __construct(IMegaKeyAes128 $decoding_key)
	{
		$this->_key = $decoding_key;
	}
	
	/**
	 * Decodes the response node and gives a clear node.
	 *
	 * @param MegaResponseNode $node
	 * @return MegaNode
	 * @throws MegaException
	 */
	public function decode(MegaResponseNode $node)
	{
		switch($node->getNodeType())
		{
			case MegaNode::TYPE_FOLDER:
				return $this->decodeFolder($node);
			case MegaNode::TYPE_FILE:
				return $this->decodeFile($node);
		}
		
		throw new MegaException('Unsupported node type to decode ({t}).',
			array('{t}' => $node->getNodeType()), MegaException::EINTERNAL);
	}
	
	/**
	 * Decodes a folder node.
	 *
	 * @param MegaResponseNode $node
	 * @return MegaNode
	 */
	protected function decodeFolder(MegaResponseNode $node)
	{
		$node_key_str = $node->getNodeKey()->getNodeKey()->toClear();
		$node_key_raw = new MegaKeyAes128String($node_key_str->__toString());
		
		$key = $this->decryptKey($node_key_raw, $this->_key);
		$iv = new MegaKeyAes128Array32(array(0, 0, 0, 0));
		$meta_mac = null;
		/* @var $key IMegaKeyAes128 */
		$attributes = $this->decryptAttributes($node->getNodeAttributes(), $key);
		return new MegaNode(
			$node->getNodeId(),
			$node->getParentNodeId(),
			$node->getOwnerId(),
			$attributes,
			$node->getNodeType(),
			$node->getNodeSize(),
			$node->getLastModifiedDatetime(),
			$key,
			$iv,
			$meta_mac
		);
	}
	
	/**
	 * Decodes a file node.
	 *
	 * @param MegaResponseNode $node
	 */
	protected function decodeFile(MegaResponseNode $node)
	{
		$node_key_str = $node->getNodeKey()->getNodeKey()->toClear();
		$node_key_raw = new MegaKeyAes256String($node_key_str->__toString());
		
		$decrypted_256 = $this->decryptKey256($node_key_raw, $this->_key);
		$key = $decrypted_256->reduceAes128();
		$iv = $decrypted_256->getInitializationVector();
		$meta_mac = $decrypted_256->getMetaMac();
		
		$attributes = $this->decryptAttributes($node->getNodeAttributes(), $key);
		return new MegaNode(
			$node->getNodeId(),
			$node->getParentNodeId(),
			$node->getOwnerId(),
			$attributes,
			$node->getNodeType(),
			$node->getNodeSize(),
			$node->getLastModifiedDatetime(),
			$key,
			$iv,
			$meta_mac
		);
	}
	
	/**
	 * Decrypts a 128 bits AES key encrypted with another 128 bits AES key,
	 * i.e. decrypts the children key for a folder with the parent's key.
	 *
	 * @param IMegaKeyAes128 $encoded_key_to_decode
	 * @param IMegaKeyAes128 $encoding_key
	 * @return IMegaKeyAes128 the encoded_key in decoded form.
	 */
	protected function decryptKey(IMegaKeyAes128 $encoded_key_to_decode, IMegaKeyAes128 $encoding_key)
	{
		$keysize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = str_repeat("\0", $keysize);
		$decoded_key = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,
			$encoding_key->toRawString()->__toString(),
			$encoded_key_to_decode->toRawString()->__toString(),
			MCRYPT_MODE_CBC, $iv
		);
		return new MegaKeyAes128String($decoded_key);
	}
	
	/**
	 * Decrypts a 256 bits AES key encrypted with a 128 bits AES key, i.e.
	 * decrypts the children key for a file with the parent's folder key.
	 *
	 * @param IMegaKeyAes256 $encoded_key_to_decode
	 * @param IMegaKeyAes128 $encoding_key
	 * @return IMegaKeyAes256 the encoded_key in decoded form.
	 */
	protected function decryptKey256(IMegaKeyAes256 $encoded_key_to_decode, IMegaKeyAes128 $encoding_key)
	{
		$keysize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = str_repeat("\0", $keysize);
		
		$rawstring = $encoded_key_to_decode->toRawString()->__toString();
		$decoded_key = '';
		foreach(array(substr($rawstring, 0, 16), substr($rawstring, 16)) as $chunk)
		{
			$decoded_key .= mcrypt_decrypt(MCRYPT_RIJNDAEL_128,
				$encoding_key->toRawString()->__toString(),
				$chunk,	// decode by chunks of 128 bits
				MCRYPT_MODE_CBC, $iv
			);
		}
		return new MegaKeyAes256String($decoded_key);
	}
	
	/**
	 * Decrypts a string with the key, i.e. decrypts the attributes for a node
	 * with it's own key.
	 *
	 * @param IMegaString $string
	 * @param IMegaKeyAes128 $key
	 * @return MegaAttribute
	 * @throws MegaException
	 */
	protected function decryptAttributes(IMegaString $string, IMegaKeyAes128 $key)
	{
		$keysize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = str_repeat("\0", $keysize);
		$decoded_data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,
			$key->toRawString()->__toString(),
			$string->toClear()->__toString(),
			MCRYPT_MODE_CBC, $iv
		);
		if(substr($decoded_data, 0, 6) !== 'MEGA{"')
			throw new MegaException('Impossible to decrypt attribute, do you have the right key ?', MegaException::EKEY);
		
		// sometimes, there is some'\0' at the end of the string
		$fpos = strpos($decoded_data, '{');
		$lpos = strrpos($decoded_data, '}');
		if($fpos === false || $lpos === false)
			throw new MegaException(strtr('Impossible to decode the json attribute data "{val}".',
				array('{val}' => $decoded_data)), MegaException::EINTERNAL);
		
		$substr_data = substr($decoded_data, $fpos, $lpos - $fpos + 1);
		$json_decoded = json_decode($substr_data, true);
		if($json_decoded === false || $json_decoded === null)
			throw new MegaException(strtr('Failed to decode the json attribute data "{val}".',
				array('{val}' => $substr_data)), MegaException::EINTERNAL);
		
		return new MegaAttribute($json_decoded);
	}
	
}
