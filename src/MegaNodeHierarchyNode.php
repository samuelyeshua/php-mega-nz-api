<?php

namespace PhpExtended\Mega;

/**
 * MegaNodeHierarchyNode class file.
 *
 * This class represents a node in the linked list hierarchy, which encapsulates
 * MegaNode objects.
 *
 * Those objects are double linked lists (tree in fact, as the children part
 * is multiple and not 1-1).
 *
 * @author Anastaszor
 */
class MegaNodeHierarchyNode
{
	
	/**
	 * The actual node object.
	 *
	 * @var MegaNode
	 */
	private $_inner_node = null;
	
	/**
	 * The parent node object.
	 *
	 * @var MegaNodeHierarchyNode
	 */
	private $_parent = null;
	
	/**
	 * The children node objects.
	 *
	 * @var MegaNodeHierarchyNode[]
	 */
	private $_children = array();
	
	/**
	 * Builds a new MegaNodeHierarchyNode with the given node.
	 *
	 * @param MegaNode $node
	 */
	public function __construct(MegaNode $node)
	{
		$this->_inner_node = $node;
	}
	
	/**
	 * Sets the parent of this node. Once the parent is set, it cannot move.
	 *
	 * @param MegaNodeHierarchyNode $parent
	 * @throws MegaException
	 */
	public function setParent(MegaNodeHierarchyNode $parent)
	{
		if($this->_parent !== null)
		{
			if($this->_parent->getNode()->getNodeId()->equals($parent->getNode()->getNodeId()))
				return;
			
			throw new MegaException('Impossible to set another parent for this node.');
		}
		
		$this->_parent = $parent;
		$parent->addChild($this);
	}
	
	/**
	 * Adds a child node to the collection of this one.
	 *
	 * @param MegaNodeHierarchyNode $child
	 * @throws MegaException
	 */
	public function addChild(MegaNodeHierarchyNode $child)
	{
		// checks if already in, if it is, do not add it once more
		// check using isset in O(1)
		if(isset($this->_children[$child->getNode()->getNodeId()->__toString()]))
			return;
		
		$this->_children[$child->getNode()->getNodeId()->__toString()] = $child;
		$child->setParent($this);
	}
	
	/**
	 * Gets the real node.
	 *
	 * @return MegaNode
	 */
	public function getNode()
	{
		return $this->_inner_node;
	}
	
	/**
	 * Gets the parent node.
	 *
	 * @return MegaNodeHierarchyNode
	 */
	public function getParent()
	{
		return $this->_parent;
	}
	
	/**
	 * Gets the children nodes.
	 *
	 * @return MegaNodeHierarchyNode[]
	 */
	public function getChildren()
	{
		return $this->_children;
	}
	
}
