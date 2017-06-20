<?php
declare(strict_types=1);
/*
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
 * @author Kevork Aghazarian
 * @website http://www.lowcalphp.com
 */
use LowCal\Helper\Config;
use LowCal\Base;

try
{
	###AUTOLOAD###
	spl_autoload_register(function($className){
		$className = str_replace ('\\', '/', $className);

		$file = stream_resolve_include_path(__DIR__.DIRECTORY_SEPARATOR.$className.'.php');

		if($file === false)
		{
			$file = stream_resolve_include_path(__DIR__.DIRECTORY_SEPARATOR.strtolower($className.'.php'));
		}

		if($file !== false)
		{
			require_once $file;

			return true;
		}

		return false;
	});

	###BASE CONFIG###
	$LOWCAL_CONFIG_ARRAY['BASE_DIR'] = __DIR__.DIRECTORY_SEPARATOR;
	$LOWCAL_CONFIG_ARRAY['LOWCAL_ENV'] = getenv('LOWCAL_ENV');

	###INIT CONFIG###
	Config::loadArray($LOWCAL_CONFIG_ARRAY);
	Config::loadFile('config.php');
	Config::loadConfig('config');

	###INIT LOWCAL###
	$_LOWCAL = new Base();

	###BEGIN YOUR APP###
	include \LowCal\Helper\Config::get('BASE_DIR').'init.php';
}
catch(\Throwable $t)
{
	error_log('LowCal start-up error. Msg: '.$t->getMessage().' / Code: '.$t->getCode());
	exit();
}
