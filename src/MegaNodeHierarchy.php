<?php

namespace PhpExtended\Mega;

/**
 * MegaNodeHierarchy class file.
 *
 * This class represents the hierarchy of nodes that are inside target folder.
 *
 * @author Anastaszor
 */
class MegaNodeHierarchy
{
	
	/**
	 * The linked-tree-node for the root node of the hierarchy.
	 *
	 * @var MegaNodeHierarchyNode
	 */
	private $_root = null;
	
	/**
	 * A hashmap of all known nodes to have insert and searching in O(1)
	 *
	 * @var [string => MegaNodeHierarchyNode]
	 */
	private $_known_nodes = array();
	
	/**
	 * Builds a new MegaNodeHierarchy with the given root node.
	 *
	 * @param MegaNode $root_node
	 */
	public function __construct(MegaNode $root_node)
	{
		$this->_root = new MegaNodeHierarchyNode($root_node);
	}
	
	/**
	 * Gets the root node of the folder hierarchy.
	 *
	 * @return MegaNode
	 */
	public function getRoot()
	{
		return $this->_root->getNode();
	}
	
	/**
	 * Inserts a new node in the hierarchy.
	 *
	 * @param MegaNode $node
	 * @throws MegaException if the parent node cannot be found.
	 */
	public function add(MegaNode $node)
	{
		$alreadyin = $this->searchNode($node->getNodeId());
		if($alreadyin !== null)
			throw new MegaException(strtr('Impossible to add node with id "{id}", already inside the hierarchy.',
				array('{id}' => $node->getNodeId())));
		
		$pnode = $this->searchNode($node->getParentId());
		if($pnode === null)
			throw new MegaException(strtr('Impossible to find node with given id "{id}".',
				array('{id}' => $node->getParentId())));
		
		$hnode = new MegaNodeHierarchyNode($node);
		$hnode->setParent($pnode);
		$this->_known_nodes[$node->getNodeId()->__toString()] = $hnode;
	}
	
	/**
	 * Gets the node metadata for the node defined by given node id.
	 *
	 * @param MegaNodeId $node_id
	 * @return MegaNode or null if the node cannot be found
	 */
	public function retrieve(MegaNodeId $node_id)
	{
		$hnode = $this->searchNode($node_id);
		if($hnode === null)
			return null;
		return $hnode->getNode();
	}
	
	/**
	 * Gets the parent node of the node defined by given node id.
	 *
	 * @param MegaNodeId $node_id
	 * @return MegaNode or null if this node is the root
	 * @throws MegaException if the child node cannot be found in the hierarchy
	 */
	public function getParent(MegaNodeId $node_id)
	{
		$hnode = $this->searchNode($node_id);
		if($hnode === null)
			throw new MegaException(strtr('Impossible to find node with given id "{id}".',
				array('{id}' => $node_id)));
		if($hnode === $this->_root)
			return null;
		return $hnode->getParent()->getNode();
	}
	
	/**
	 * Gets the children of the node defined by given node id.
	 *
	 * @param MegaNodeId $node_id
	 * @return MegaNode[] or empty array if none is found
	 */
	public function getChildren(MegaNodeId $node_id)
	{
		$hnode = $this->searchNode($node_id);
		if($hnode === null)
			throw new MegaException(strtr('Impossible to find node with given id "{id}".',
				array('{id}' => $node_id)));
		$children = array();
		foreach($hnode->getChildren() as $hchild)
			$children[] = $hchild->getNode();
		return $children;
	}
	
	/**
	 * Gets the node hierarchy node which contains the node with the given id.
	 *
	 * @param MegaNodeId $node_id
	 * @return MegaNodeHierarchyNode, null if not found
	 */
	protected function searchNode(MegaNodeId $node_id)
	{
		// search the cache of known nodes first
		if(isset($this->_known_nodes[$node_id->getValue()]))
			return $this->_known_nodes[$node_id->getValue()];
		return null;
	}
	
}
