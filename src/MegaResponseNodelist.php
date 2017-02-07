<?php

namespace PhpExtended\Mega;

/**
 * MegaResponseNodeList class file.
 *
 * This class represents a node list, as given by the API.
 *
 * @author Anastaszor
 */
class MegaResponseNodelist
{
	
	/**
	 * The nodes inside the response.
	 *
	 * @var MegaResponseNode[]
	 */
	private $_f = array();
	
	/**
	 * unknown data
	 *
	 * @var unknown
	 */
	private $_sn = null;
	
	/**
	 * unknown data
	 *
	 * @var unknown
	 */
	private $_noc = null;
	
	/**
	 * Builds a new MegaNodeResponse with the given json data.
	 *
	 * @param array $json_data
	 * @throws MegaException
	 */
	public function __construct(array $json_data)
	{
		foreach($json_data as $key => $value)
		{
			switch($key)
			{
				case 'f':
					if(!is_array($value))
						throw new MegaException(strtr('The folder value is supposed to be an array, {type} given.',
							array('{type}' => gettype($value))), MegaException::EINTERNAL);
					
					foreach($value as $metanode)
					{
						$this->_f[] = new MegaResponseNode($metanode);
					}
					break;
				case 'sn':
					$this->_sn = $value;
					break;
				case 'noc':
					$this->_noc = $value;
					break;
				default:
					throw new MegaException(strtr('Unknown attribute property "{key}" with value "{val}".',
						array('{key}' => $key, '{val}' => $value)), MegaException::EINTERNAL);
			}
		}
	}
	
	/**
	 * Gets the node list.
	 *
	 * @return MegaEncryptedNode[]
	 */
	public function getNodes()
	{
		return $this->_f;
	}
	
	/**
	 * Gets the node which is the root amongst the nodes given in the list.
	 *
	 * @return MegaResponseNode
	 */
	public function getRootNode()
	{
		// normally, the root folder is the first of the list.
		// double check in case all the other folders are not rooted to this one
		$supposed_root = $this->_f[0];
		
		// TODO disabled as we use the recursive mode in Mega class, and all
		// nodes in the hierarchy are given flat, we need more complex checks
// 		foreach($this->_f as $node)
// 		{
// 			if($node->getNodeId()->equals($supposed_root->getNodeId()))
// 				continue;
// 			if($node->getParentNodeId()->equals($supposed_root->getNodeId()))
// 				continue;
			
// 			throw new MegaException(strtr('The supposed root element "{pkey}" is not the parent of node "{ckey}".',
// 				array('{pkey}' => $supposed_root->getNodeId(), '{ckey}' => $node->getNodeId())));
// 		}
		
		return $supposed_root;
	}
	
	/**
	 * Gets all the other non-root nodes.
	 *
	 * @return MegaResponseNode[]
	 */
	public function getNonrootNodes()
	{
		$root = $this->getRootNode();
		
		$nodes = array();
		foreach($this->_f as $node)
		{
			if($node->getNodeId()->equals($root->getNodeId()))
				continue;
			$nodes[] = $node;
		}
		return $nodes;
	}
	
}
