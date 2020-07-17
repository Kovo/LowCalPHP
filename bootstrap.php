<?php
declare(strict_types=1);
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
/*
 * NOTE: Try not to modify bootstrap.php to make your application compatible with future updates.
 */

use LowCal\Helper\Config;
use LowCal\Base;

try
{
	require_once 'Psr4Autoloader.php';

	$loader = new Psr4Autoloader();
	$loader->register();
	$loader->addNamespace('LowCal\\', 'LowCal/');
	$loader->addNamespace('PHPMailer\\', 'LowCal/PHPMailer/');

	###BASE CONFIG###
	$LOWCAL_CONFIG_ARRAY['BASE_DIR'] = __DIR__.DIRECTORY_SEPARATOR;
	$LOWCAL_CONFIG_ARRAY['LOWCAL_ENV'] = getenv('LOWCAL_ENV');

	###INIT CONFIG###
	Config::loadArray($LOWCAL_CONFIG_ARRAY);
	Config::loadFile('config.php');
	Config::loadConfigForEnv('config.php');

	###INIT LOWCAL###
	$LowCal = new Base($loader);
}
catch(\Throwable $t)
{
	error_log('LowCal start-up error. Msg: '.$t->getMessage().' / Code: '.$t->getCode());

	exit();
}

try
{
	/*
	 * Configuration for routing
	 */
	$LowCal->routing()->setSiteUrl(Config::get('APP_ROOT_URL'));
	$LowCal->routing()->setBaseUri(Config::get('APP_ROOT_URI'));

	/*
	 * Constants for routing patterns commonly used in web apps
	 */
	define('ROUTING_LANG_REG_EX', '[a-z]{2,3}+');
	define('ROUTING_ID_REG_EX', '[0-9]+');
	define('ROUTING_NAME_REG_EX', '[a-zA-Z0-9\-]+');

	/*
	* Set view engine
	*/
	$LowCal->view()->setViewEngineType(Config::get('VIEW_ENGINE_PHP'))
		->setViewEngineObject(new \LowCal\Module\View\PHP($LowCal))
		->getViewEngineObject()
		->setViewDir(Config::get('VIEWS_DIR'));

	###BEGIN YOUR APP###
	require_once Config::get('BASE_DIR').'init.php';

	#Module initiation
	$LowCal->loadAdditionalModules();

	echo $LowCal->routing()->listen();
}
catch(Exception $e)
{
	if(
		in_array(
			$e->getCode(),
			Config::get('APP_404_ERROR_CODES')
		)
	)
	{
		$LowCal->response()->setHeader('Status', '404 Not Found');
		$LowCal->response()->setHeader('HTTP/1.0 404 Not Found');

		echo $LowCal->view()->render(
			Config::get('APP_404_VIEW'),
			array(
				'exception_msg' => $e->getMessage(),
				'exception_code' => $e->getCode()
			)
		);
	}
	else
	{
		$LowCal->response()->setHeader('Status', '500 Internal Server Error');
		$LowCal->response()->setHeader('HTTP/1.0 500 Internal Server Error');

		echo $LowCal->view()->render(
			Config::get('APP_500_VIEW'),
			array(
				'exception_msg' => $e->getMessage(),
				'exception_code' => $e->getCode()
			)
		);
	}
}