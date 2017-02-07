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
		return $this->searchNodeRecursive($node_id, $this->_root);
	}
	
	/**
	 * Gets the node hierarchy node which contains the node with the given id,
	 * if not found, search the given node hierarchy children.
	 *
	 * @param MegaNodeId $node_id
	 * @param MegaNodeHierarchyNode $hierarchy
	 * @return MegaNodeHierarchyNode, null if not found
	 */
	protected function searchNodeRecursive(MegaNodeId $node_id, MegaNodeHierarchyNode $hierarchy)
	{
		if($hierarchy->getNode()->getNodeId()->equals($node_id))
			return $hierarchy;
		foreach($hierarchy->getChildren() as $child)
		{
			$rchild = $this->searchNodeRecursive($node_id, $child);
			if($rchild !== null)
				return $rchild;
		}
		return null;
	}
	
}
