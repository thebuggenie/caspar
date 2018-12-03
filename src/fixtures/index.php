<?php

	if (PHP_VERSION_ID < 70100) {
        die('This software requires PHP 7.1.0 or newer, but you have an older version. Please upgrade');
    }

	// Set standard constants needed elsewhere
	defined('DS') || define('DS', DIRECTORY_SEPARATOR);
    define('CASPAR_BASE_PATH', realpath(getcwd() . DS . '..' . DS) . DS);
    define('CASPAR_PATH', CASPAR_BASE_PATH . 'vendor' . DS . 'thebuggenie' . DS . 'caspar' . DS . 'src' . DS);
    define('CASPAR_APPLICATION_PATH', CASPAR_BASE_PATH . 'application' . DS);
    define('CASPAR_SESSION_NAME', 'CASPAR');

    require CASPAR_BASE_PATH . 'vendor' . DS . 'autoload.php';

	// Set runtime environment
	\caspar\core\Caspar::setEnvironment('dev');
	\caspar\core\Caspar::setCacheStrategy(
	    ['enabled' => false, 'type' => \caspar\core\Cache::TYPE_APC],
        ['enabled' => false, 'path' => CASPAR_CACHE_PATH]
    );

	// Initialize framework
	caspar\core\Caspar::initialize();
