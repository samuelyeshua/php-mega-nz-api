<?php

namespace PhpExtended\Mega;

/**
 * MegaResponseNode class file.
 *
 * This class represents a node, as given by the API.
 *
 * @author Anastaszor
 */
class MegaResponseNode
{
	
	/**
	 * The id of the node.
	 *
	 * @var MegaNodeId
	 */
	private $_h = null;
	
	/**
	 * The id of parent node.
	 *
	 * @var MegaNodeId
	 */
	private $_p = null;
	
	/**
	 * The id of the owner user.
	 *
	 * @var MegaUserId
	 */
	private $_u = null;
	
	/**
	 * The type of the node
	 *
	 * @var string
	 */
	private $_t = null;
	
	/**
	 *The attributes of the node, encrypted, base64.
	 *
	 * @var MegaBase64String
	 */
	private $_a = null;
	
	/**
	 * The key of the node, encrypted, base64.
	 *
	 * @var MegaBase64String
	 */
	private $_k = null;
	
	/**
	 * The size of the node, in octets.
	 *
	 * @var integer
	 */
	private $_s = null;
	
	/**
	 * The timestamp of the last modification of the node.
	 *
	 * @var \DateTime
	 */
	private $_ts = null;
	
	/**
	 * unknown data
	 *
	 * @var unknown
	 */
	private $_fa = null;
	
	/**
	 * Build a new MegaResponseNode with the given json data.
	 *
	 * @param array $json_data
	 */
	public function __construct(array $json_data)
	{
		foreach($json_data as $key => $value)
		{
			switch($key)
			{
				case 'h':
					$this->_h = new MegaNodeId($value);
					break;
				case 'p':
					$this->_p = new MegaNodeId($value);
					break;
				case 'u':
					$this->_u = new MegaUserId($value);
					break;
				case 't';
					switch($value)
					{
						case MegaNode::TYPE_FILE:
						case MegaNode::TYPE_FOLDER:
							$this->_t = (int) $value;
							break;
						default:
							throw new MegaException(strtr('Unsupported node type value "{val}".',
								array('{val}' => $value)));
					}
					break;
				case 'a':
					$this->_a = new MegaBase64String($value);
					break;
				case 'k':
					$this->_k = new MegaResponseKey($value);
					break;
				case 's':
					$this->_s = $value;
					break;
				case 'ts':
					$this->_ts = new \DateTime('@'.$value);
					break;
				case 'fa':
					$this->_fa = $value;
					break;
				default:
					throw new MegaException(strtr('Unknown attribute property "{key}" with value "{val}".',
						array('{key}' => $key, '{val}' => $value)), MegaException::EINTERNAL);
			}
		}
	}
	
	/**
	 * Gets this node id.
	 *
	 * @return MegaNodeId
	 */
	public function getNodeId()
	{
		return $this->_h;
	}
	
	/**
	 * Gets the parent node id.
	 *
	 * @return MegaNodeId
	 */
	public function getParentNodeId()
	{
		return $this->_p;
	}
	
	/**
	 * Gets the owner user id.
	 *
	 * @return MegaUserId
	 */
	public function getOwnerId()
	{
		return $this->_u;
	}
	
	/**
	 * Gets this node type.
	 *
	 * @return integer
	 */
	public function getNodeType()
	{
		return $this->_t;
	}
	
	/**
	 * Gets the attributes, as encrypted string.
	 *
	 * @return MegaBase64String
	 */
	public function getNodeAttributes()
	{
		return $this->_a;
	}
	
	/**
	 * Gets the key, as encrypted string.
	 *
	 * @return MegaResponseKey
	 */
	public function getNodeKey()
	{
		return $this->_k;
	}
	
	/**
	 * Gets the node size, in octets.
	 *
	 * @return integer
	 */
	public function getNodeSize()
	{
		return $this->_s;
	}
	
	/**
	 * Gets the last modified date and time.
	 *
	 * @return \DateTime
	 */
	public function getLastModifiedDatetime()
	{
		return $this->_ts;
	}
	
}
