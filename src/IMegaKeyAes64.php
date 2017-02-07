<?php

namespace PhpExtended\Mega;

/**
 * IMegaKeyAes64 interface file.
 *
 * Generic interface for manipulating 64 bits AES keys.
 *
 * @author Anastaszor
 */
interface IMegaKeyAes64
{
	
	/**
	 * Gets a version of that key encoded in an array of two 32 bits values.
	 *
	 * @return MegaKeyAes64Array32
	 */
	public function toArray32();
	
	/**
	 * Gets a version of that key in pure form.
	 *
	 * @return MegaKeyAes64String
	 */
	public function toRawString();
	
	/**
	 * Gets a string representation of this object in current state.
	 *
	 * @return string
	 */
	public function __toString();
	
}
