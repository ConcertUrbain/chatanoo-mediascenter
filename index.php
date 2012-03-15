<?php

	// Define path to application directory
	defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/Application'));

	// Define application environment
	defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');

	// Typically, you will also want to add your library/ directory
	// to the include_path, particularly if it contains your ZF install
	set_include_path(implode(PATH_SEPARATOR, array(
	    APPLICATION_PATH . '/../Library',
	    get_include_path(),
	)));

	/** Zend_Application */
	require_once 'Zend/Application.php';

	// Create application, bootstrap, and run
	$application = new Zend_Application(
	    APPLICATION_ENV,
	    APPLICATION_PATH . '/etc/init.xml'
	);
	$application->bootstrap()
				->run();