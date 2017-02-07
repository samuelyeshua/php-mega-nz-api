<?php

namespace PhpExtended\Mega;

/**
 * IMegaKeyAes256 interface file.
 *
 * Generic interface for manipulating 256 bits AES keys.
 *
 * @author Anastaszor
 */
interface IMegaKeyAes256
{
	
	/**
	 * Gets a version of that key encoded in an array of eight 32 bits values.
	 *
	 * @return MegaKeyAes256Array32
	 */
	public function toArray32();
	
	/**
	 * Gets a version of that key in pure form.
	 *
	 * @return MegaKeyAes256String
	 */
	public function toRawString();
	
	/**
	 * Gets the 128 bits key which is hidden in the 256 bits.
	 *
	 * @return IMegaKeyAes128
	 */
	public function reduceAes128();
	
	/**
	 * Gets the initialization vector hidden in the 256 bits.
	 *
	 * @return IMegaKeyAes128
	 */
	public function getInitializationVector();
	
	/**
	 * Gets the meta mac data hidden in the 256 bits.
	 *
	 * @return IMegaKeyAes64
	 */
	public function getMetaMac();
	
	/**
	 * Gets a string representation of this object in current state.
	 *
	 * @return string
	 */
	public function __toString();
	
}
