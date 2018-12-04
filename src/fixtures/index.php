<?php

	if (PHP_VERSION_ID < 70100) {
        die('This software requires PHP 7.1.0 or newer, but you have an older version. Please upgrade');
    }

	// Set standard constants needed elsewhere
	defined('DS') || define('DS', DIRECTORY_SEPARATOR);
    define('CASPAR_BASE_PATH', realpath(getcwd() . DS . '..' . DS) . DS);
    define('CASPAR_PATH', CASPAR_BASE_PATH . 'vendor' . DS . 'thebuggenie' . DS . 'caspar' . DS . 'src' . DS);
    define('CASPAR_APPLICATION_PATH', CASPAR_BASE_PATH . 'application' . DS);
	define('CASPAR_CACHE_PATH', CASPAR_APPLICATION_PATH . 'cache' . DS);
	define('CASPAR_SESSION_NAME', 'CASPAR');

    require CASPAR_BASE_PATH . 'vendor' . DS . 'autoload.php';

	\caspar\core\Caspar::registerErrorHandlers();

	// Set runtime environment
	\caspar\core\Caspar::setEnvironment('dev');

	// Uncomment one of the following lines to enable file or memory-based cache
	\caspar\core\Caspar::setCacheStrategy(new \caspar\core\Cache(\caspar\core\Cache::TYPE_FILE, ['path' => CASPAR_CACHE_PATH]));
	// \caspar\core\Caspar::setCacheStrategy(new \caspar\core\Cache(\caspar\core\Cache::TYPE_APC));

	// Initialize framework
	caspar\core\Caspar::initialize();
