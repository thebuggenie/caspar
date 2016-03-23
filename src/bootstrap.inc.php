<?php

	if (!defined('CASPAR_PATH')) {
		throw new RuntimeException('You must define the CASPAR_PATH constant so we can find the files we need');
	}

	if (!defined('CASPAR_APPLICATION_PATH')) {
		throw new RuntimeException('You must define the CASPAR_APPLICATION_PATH constant so we can find the application files');
	}

	date_default_timezone_set('UTC');
	mb_internal_encoding("UTF-8");
	mb_language('uni');
	mb_http_output("UTF-8");

	defined('CASPAR_CORE_PATH') || define('CASPAR_CORE_PATH', CASPAR_PATH . 'core' . DS);
	defined('CASPAR_LIB_PATH') || define('CASPAR_LIB_PATH', CASPAR_PATH . 'libs' . DS);
	defined('CASPAR_MODULES_PATH') || define('CASPAR_MODULES_PATH', CASPAR_APPLICATION_PATH . 'modules' . DS);
	defined('CASPAR_ENTITIES_PATH') || define('CASPAR_ENTITIES_PATH', CASPAR_APPLICATION_PATH . 'entities' . DS);
	defined('CASPAR_CACHE_PATH') || define('CASPAR_CACHE_PATH', CASPAR_APPLICATION_PATH . 'cache' . DS);
	defined('CASPAR_SESSION_NAME') || defined('CASPAR_SESSION_NAME') || define('CASPAR_SESSION_NAME', 'CASPAR_SESSION');

	// Set up error and exception handling
	set_exception_handler(array('\\caspar\\core\\Caspar', 'exceptionHandler'));
	set_error_handler(array('\\caspar\\core\\Caspar', 'errorHandler'));
	error_reporting(E_ALL | E_NOTICE | E_STRICT);

	if (!isset($argc) && !ini_get('session.auto_start')) {
		session_name(CASPAR_SESSION_NAME);
		session_start();
	}
