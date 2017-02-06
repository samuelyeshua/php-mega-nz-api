<?php

namespace PhpExtended\Mega;

/**
 * IMegaString interface file.
 *
 * Generic interfaces to parse strings given by Mega API.
 *
 * @author Anastaszor
 */
interface IMegaString
{
	
	/**
	 * Gets a version of that string encoded base64.
	 *
	 * @return MegaBase64String
	 */
	public function toBase64();
	
	/**
	 * Gets a version of that string in pure form.
	 *
	 * @return MegaClearString
	 */
	public function toClear();
	
	/**
	 * Gets a string representation of this object in current state.
	 *
	 * @return string
	 */
	public function __toString();
	
}
