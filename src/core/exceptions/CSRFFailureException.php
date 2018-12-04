<?php

	namespace caspar\core\exceptions;

	/**
	 * This exception is thrown whenever the request cannot be validated
	 * against the csrf_token stored in the session
	 *
	 * @package caspar
	 * @subpackage core
	 */
	class CSRFFailureException extends \Exception
	{
		
	}

