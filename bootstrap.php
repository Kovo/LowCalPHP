<?php
declare(strict_types=1);
/**
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice, contribtuions, and original author information.
 * @author Kevork Aghazarian
 * @website http://www.lowcalphp.com
 */
namespace LowCal;
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
			include $file;

			return true;
		}

		return false;
	});

	###BASE CONFIG###
	$LOWCAL_CONFIG_ARRAY['BASE_DIR'] = __DIR__.DIRECTORY_SEPARATOR;
	$LOWCAL_CONFIG_ARRAY['LOWCAL_ENV'] = getenv('LOWCAL_ENV');

	###INIT CONFIG###
	\LowCal\Helper\Config::loadArray($LOWCAL_CONFIG_ARRAY);
	\LowCal\Helper\Config::loadFile($LOWCAL_CONFIG_ARRAY['BASE_DIR'].'config.php');
	\LowCal\Helper\Config::loadConfig('config');

	###INIT LOWCAL###
	$_LOWCAL = new \LowCal\Base();
}
catch(\Exception $e)
{
	error_log('LowCal start-up error. Msg: '.$e->getMessage().' / Code: '.$e->getCode());
	exit();
}

###CUSTOM BOOTSTRAP###
include \LowCal\Helper\Config::get('BASE_DIR').'init.php';
