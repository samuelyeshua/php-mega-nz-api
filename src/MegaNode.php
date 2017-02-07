<?php

namespace PhpExtended\Mega;

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
	 *
	 * @var MegaNodeId
	 */
	private $_node_id = null;
	
	/**
	 *
	 * @var MegaNodeId
	 */
	private $_parent_node_id = null;
	
	/**
	 *
	 * @var MegaUserId
	 */
	private $_owner_id = null;
	
	/**
	 *
	 * @var MegaAttribute
	 */
	private $_attributes = null;
	
	/**
	 *
	 * @var integer
	 */
	private $_node_type = null;
	
	/**
	 *
	 * @var integer
	 */
	private $_node_size = null;
	
	/**
	 *
	 * @var \DateTime
	 */
	private $_last_modified_date = null;
	
	/**
	 *
	 * @var IMegaKeyAes128
	 */
	private $_node_key = null;
	
	/**
	 *
	 * @var IMegaKeyAes128
	 */
	private $_init_vec = null;
	
	/**
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
	 *
	 * @return MegaNodeId
	 */
	public function getNodeId()
	{
		return $this->_node_id;
	}
	
	/**
	 *
	 * @return MegaNodeId
	 */
	public function getParentId()
	{
		return $this->_parent_node_id;
	}
	
}
