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

###BEGIN YOUR APP###
include Config::get('BASE_DIR').'init.php';
