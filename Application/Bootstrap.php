<?php

	class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
	{
		protected function _initAutoload()
		{
			$autoloader = Zend_Loader_Autoloader::getInstance();
			$autoloader->setFallbackAutoloader(true);
		}

		protected function _initConfig()
		{
			$config = new Zend_Config_Xml(APPLICATION_PATH.'/etc/config.xml', APPLICATION_ENV);
			Zend_Registry::set('config', $config);
		}

		protected function _initDb()
		{
			$dbAdapter = Zend_Db::factory(Zend_Registry::get('config')->database);
			Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
			Zend_Registry::set('db', $dbAdapter);
		}
		
		protected function _initRouter()
		{
			$router = Zend_Controller_Front::getInstance()->getRouter();
			
			// Default upload route
			$route = new Zend_Controller_Router_Route_Regex(
			    'upload',
			    array(
			        'controller' => 'index',
			        'action' => 'upload'
			    )
			);
			$router->addRoute('upload', $route);
			
			$route0 = new Zend_Controller_Router_Route_Regex(
			    'curl_upload',
			    array(
			        'controller' => 'index',
			        'action' => 'curlupload'
			    )
			);
			$router->addRoute('curl_upload', $route0);
			
			// Default video route
			$route1 = new Zend_Controller_Router_Route_Regex(
			    'm/(?:(?:([0-9]+)x([0-9]+)/)|(?:([0-9]+):([0-9]+)/)|(?:([0-9]+)Hz/)|(?:([0-9]+)bits-s/)|(?:(disable_audio)/)|(?:([0-9]+)k/))*(MC-[a-zA-Z]+-V)\.(avi|mov|flv|mp4|wmv|ogv|webm)',
			    array(
			        'controller' => 'Media',
			        'action' => 'video'
			    ),
			    array(
			    	1 => 'width',
			    	2 => 'height',
			    	3 => 'ratio_width',
			    	4 => 'ratio_height',
			    	5 => 'rate',
			    	6 => 'bits',
			    	7 => 'disable_audio',
			    	8 => 'limit_size',
			    	9 => 'media_uiid',
			    	10 => 'format'
			    )
			);
			$router->addRoute('getVideo', $route1);
			
			// Default video preview route
			$route2 = new Zend_Controller_Router_Route_Regex(
			    'm/preview/(?:(?:([0-9]+)x([0-9]+)/)|(?:([0-9]+):([0-9]+):([0-9]+)/))*(MC-[a-zA-Z]+-V)\.(png|jpg|jpeg|PNG|JPG|JPEG)',
			    array(
			        'controller' => 'Media',
			        'action' => 'preview'
			    ),
			    array(
			    	1 => 'width',
			    	2 => 'height',
			    	3 => 'time_hour',
			    	4 => 'time_minute',
			    	5 => 'time_seconde',
			    	6 => 'media_uiid',
			    	7 => 'format'
			    )
			);
			$router->addRoute('getPreview', $route2);
			
			// Default picture route
			$route3 = new Zend_Controller_Router_Route_Regex(
			    'm/(?:(?:([0-9]+)x([0-9]+)/))*(MC-[a-zA-Z]+-P)\.(png|jpg|gif)',
			    array(
			        'controller' => 'Media',
			        'action' => 'picture'
			    ),
			    array(
			    	1 => 'width',
			    	2 => 'height',
			    	3 => 'media_uiid',
			    	4 => 'format'
			    )
			);
			$router->addRoute('getPicture', $route3);
			
			// Default audio route
			$route4 = new Zend_Controller_Router_Route_Regex(
			    'm/(?:(?:(?:([0-9]+)bits-s)/)|(?:([0-9]+)Hz/)|(?:([0-9]+)k/))*(MC-[a-zA-Z]+-A)\.(mp3|wav|aac|m4a)',
			    array(
			        'controller' => 'Media',
			        'action' => 'audio'
			    ),
			    array(
			    	1 => 'bit_rate',
			    	2 => 'sampling_rate',
			    	3 => 'limit_size',
			    	4 => 'media_uiid',
			    	5 => 'format'
			    )
			);
			$router->addRoute('getAudio', $route4);
		}
		
		protected function _initLog()
		{
			$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../Logs/log.txt');
		
			$logger = new Zend_Log($writer);
			Zend_Registry::set('logger', $logger);
		}
	}
