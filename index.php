<?php

	// Define path to application directory
	defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/Application'));

	// Define application environment
	defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'preproduction');
	require 'vendor/autoload.php';

	/** Zend_Application */
	require_once 'Zend/Application.php';

	// Create application, bootstrap, and run
	$application = new Zend_Application(
	    APPLICATION_ENV,
	    APPLICATION_PATH . '/etc/init.xml'
	);
	$application->bootstrap()
				->run();
