<?php

namespace PhpExtended\Mega;

/**
 * MegaNode class file.
 *
 * This class represents a node in mega's file and folders hierarchy. A node
 * represents metadata for a folder, a file, or other specific elements.
 *
 * @author Anastaszor
 */
class MegaNode
{
	
	const TYPE_FILE   = 0;
	const TYPE_FOLDER = 1;
	const TYPE_ROOT   = 2;
	const TYPE_INBOX  = 3;
	const TYPE_TRASH  = 4;
	const TYPE_CONTACT = 8;
	const TYPE_NETWORK = 9;
	
	/**
	 * The id of this node.
	 *
	 * @var MegaNodeId
	 */
	private $_node_id = null;
	
	/**
	 * The id of parent node.
	 *
	 * @var MegaNodeId
	 */
	private $_parent_node_id = null;
	
	/**
	 * The id of owner user.
	 *
	 * @var MegaUserId
	 */
	private $_owner_id = null;
	
	/**
	 * The attributes of this node.
	 *
	 * @var MegaAttribute
	 */
	private $_attributes = null;
	
	/**
	 * The type of this node. One of the MegaNode::TYPE_* constants.
	 *
	 * @var integer
	 */
	private $_node_type = null;
	
	/**
	 * The size of this node, in octets.
	 *
	 * @var integer
	 */
	private $_node_size = null;
	
	/**
	 * The date when this node was last modified.
	 *
	 * @var \DateTime
	 */
	private $_last_modified_date = null;
	
	/**
	 * The private key to decrypt this node's content.
	 *
	 * @var IMegaKeyAes128
	 */
	private $_node_key = null;
	
	/**
	 * The initialization vector, to decrypt this node's content.
	 *
	 * @var IMegaKeyAes128
	 */
	private $_init_vec = null;
	
	/**
	 * The meta mac, to check the integrity of this node's content.
	 *
	 * @var IMegaKeyAes64
	 */
	private $_meta_mac = null;
	
	/**
	 * Builds a new MegaNode with the right elements
	 *
	 * @param MegaNodeId $node_id
	 * @param MegaNodeId $parent_node_id
	 * @param MegaUserId $owner_id
	 * @param MegaAttribute $attributes
	 * @param integer $node_type
	 * @param integer $node_size
	 * @param \DateTime $last_modified_date
	 * @param IMegaKeyAes128 $node_key
	 * @param IMegaKeyAes128 $init_vec
	 * @param IMegaKeyAes64 $meta_mac
	 */
	public function __construct(
		MegaNodeId $node_id,
		MegaNodeId $parent_node_id,
		MegaUserId $owner_id,
		MegaAttribute $attributes,
		$node_type,
		$node_size,
		\DateTime $last_modified_date,
		IMegaKeyAes128 $node_key,
		IMegaKeyAes128 $init_vec,
		IMegaKeyAes64 $meta_mac = null
	) {
		$this->_node_id = $node_id;
		$this->_parent_node_id = $parent_node_id;
		$this->_owner_id = $owner_id;
		$this->_attributes = $attributes;
		$this->_node_type = $node_type;
		$this->_node_size = $node_size;
		$this->_last_modified_date = $last_modified_date;
		$this->_node_key = $node_key;
		$this->_init_vec = $init_vec;
		$this->_meta_mac = $meta_mac;
	}
	
	/**
	 * Gets this node's id.
	 *
	 * @return MegaNodeId
	 */
	public function getNodeId()
	{
		return $this->_node_id;
	}
	
	/**
	 * Gets this node parent's id.
	 *
	 * @return MegaNodeId
	 */
	public function getParentId()
	{
		return $this->_parent_node_id;
	}
	
	/**
	 * Gets this node owner's user id.
	 *
	 * @return MegaUserId
	 */
	public function getOwnerId()
	{
		return $this->_owner_id;
	}
	
	/**
	 * Gets the attributes for this node.
	 *
	 * @return MegaAttribute
	 */
	public function getAttributes()
	{
		return $this->_attributes;
	}
	
	/**
	 * Gets this node's type. Only one of MegaNode::TYPE_* constants.
	 *
	 * @return integer
	 */
	public function getNodeType()
	{
		return $this->_node_type;
	}
	
	/**
	 * Gets the node size. Only set when nodes represents files.
	 *
	 * @return integer
	 */
	public function getNodeSize()
	{
		return $this->_node_size;
	}
	
	/**
	 * Gets the last modified date of this node.
	 *
	 * @return \DateTime
	 */
	public function getLastModifiedDate()
	{
		return $this->_last_modified_date;
	}
	
	/**
	 * Gets the encryption key for this node.
	 *
	 * @return IMegaKeyAes128
	 */
	public function getNodeKey()
	{
		return $this->_node_key;
	}
	
	/**
	 * Gets the initialization vector for this node.
	 *
	 * @return IMegaKeyAes128
	 */
	public function getInitializationVector()
	{
		return $this->_init_vec;
	}
	
	/**
	 * Gets the meta mac for this node.
	 *
	 * @return IMegaKeyAes64
	 */
	public function getMetaMac()
	{
		return $this->_meta_mac;
	}
	
}
