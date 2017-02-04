<?php

namespace PhpExtended\Mega;

/**
 * Exception for all interactions with the Mega API.
 *
 * @author Anastaszor
 */
class MegaException extends \Exception
{
	
	const EUNKNOWN = 0;
	const EINTERNAL = -1;
	const EARGS = -2;
	const EAGAIN = -3;
	const ERATELIMIT = -4;
	const EFAILED = -5;
	const ETOOMANY = -6;
	const ERANGE = -7;
	const EEXPIRED = -8;
	const ENOENT = -9;
	const ECIRCULAR = -10;
	const EACCESS = -11;
	const EEXIST = -12;
	const EINCOMPLETE = -13;
	const EKEY = -14;
	const ESID = -15;
	const EBLOCKED = -16;
	const EOVERQUOTA = -17;
	const ETEMPUNAVAIL = -18;
	
	/**
	 * All standard error messages.
	 *
	 * @var string[]
	 */
	private static $_std_err_msg = array(
		self::EUNKNOWN     => 'An unknown error has occured. Please submit a bug report, detailing the exact circumstances in which this error occurred.',
		self::EINTERNAL    => 'An internal error has occurred. Please submit a bug report, detailing the exact circumstances in which this error occurred.',
		self::EARGS        => 'You have passed invalid arguments to this command',
		self::EAGAIN       => 'A temporary congestion or server malfunction prevented your request from being processed. No data was altered. Retry. Retries must be spaced with exponential backoff',
		self::ERATELIMIT   => 'You have exceeded your command weight per time quota. Please wait a few seconds, then try again (this should never happen in sane real-life applications)',
		self::EFAILED      => 'The upload failed. Please restart it from scratch',
		self::ETOOMANY     => 'Too many concurrent IP addresses are accessing this upload target URL',
		self::ERANGE       => 'The upload file packet is out of range or not starting and ending on a chunk boundary',
		self::EEXPIRED     => 'The upload target URL you are trying to access has expired. Please request a fresh one',
		self::ENOENT       => 'Object (typically, node or user) not found',
		self::ECIRCULAR    => 'Circular linkage attempted',
		self::EACCESS      => 'Access violation (e.g., trying to write to a read-only share)',
		self::EEXIST       => 'Trying to create an object that already exists',
		self::EINCOMPLETE  => 'Trying to access an incomplete resource',
		self::EKEY         => 'A decryption operation failed (never returned by the API)',
		self::ESID         => 'Invalid or expired user session, please relogin',
		self::EBLOCKED     => 'User blocked',
		self::EOVERQUOTA   => 'Request over quota',
		self::ETEMPUNAVAIL => 'Resource temporarily not available, please try again later',
	);
	
	/**
	 * Builds a new MegaException. This method will given a standard error message
	 * based on the code that is used.
	 *
	 * @param string $message
	 * @param integer $code
	 * @param \Throwable $previous
	 */
	public function __construct($message = null, $code = null, $previous = null)
	{
		if(!isset(self::$_std_err_msg[$code]))
			$code = 0;
		if($message === null)
			$message = self::$_std_err_msg[$code];
		parent::__construct($message, $code, $previous);
	}
	
}
