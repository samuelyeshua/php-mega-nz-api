<?php

namespace PhpExtended\Mega;

/**
 * IMegaKeyAes128 interface file.
 *
 * Generic interface for manipulating 128 bits AES keys.
 *
 * @author Anastaszor
 */
interface IMegaKeyAes128
{
	
	/**
	 * Gets a version of that key encoded in an array of four 32 bits values.
	 *
	 * @return MegaKeyAes128Array32
	 */
	public function toArray32();
	
	/**
	 * Gets a version of that key in pure form.
	 *
	 * @return MegaKeyAes128String
	 */
	public function toRawString();
	
	/**
	 * Gets a string representation of this object in current state.
	 *
	 * @return string
	 */
	public function __toString();
	
}
