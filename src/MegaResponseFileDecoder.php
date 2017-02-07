<?php

namespace PhpExtended\Mega;

/**
 * MegaResponseFileDecoder class file.
 *
 * This class decodes raw file contents using the metadata in the file node.
 *
 * @author Anastaszor
 */
class MegaResponseFileDecoder
{
	
	/**
	 * The node which holds the key to decode the data.
	 *
	 * @var MegaNode
	 */
	private $_node = null;
	
	/**
	 * Builds a new MegaResponseFileDecoder object with the given node.
	 *
	 * @param MegaNode $node
	 */
	public function __construct(MegaNode $node)
	{
		return $this->_node = $node;
	}
	
	/**
	 * Decodes the given data according to the keys in the node.
	 *
	 * @param string $string
	 * @return string
	 */
	public function decode($string)
	{
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_128,
			$this->_node->getNodeKey()->toRawString()->__toString(),
			$string, 'ctr',
			$this->_node->getInitializationVector()->toRawString()->__toString()
		);
	}
	
}
