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
	 */
	public function addChild(MegaNodeHierarchyNode $child)
	{
		$already_in = false;
		foreach($this->_children as $test)
		{
			if($test->getNode()->getNodeId()->equals($child->getNode()->getNodeId()))
			{
				$already_in = true;
				break;
			}
		}
		if(!$already_in)
		{
			$this->_children[] = $child;
			$child->setParent($this);
		}
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
