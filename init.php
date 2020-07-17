<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);
/*
 * NOTE: Your application starts here. Everything below this comment block is provided as a guideline.
 * NOTE: Feel free to clear init.php and start from scratch.
 */
use LowCal\Helper\Config;

global $LowCal;

/*
 * Define routing rules here
 */
$LowCal->routing()->add('home', '/<lang>/', '\LowCal\Controller\Home', 'indexAction');

/*
 * Begin db configs, logs, session, and start listening for routes
 */
$LowCal->locale()->addLanguage('en', 'en-us');
$LowCal->locale()->setCurrentLocale('en');

/*
* Register logs here
* You should register logs depending on what databases you will be using,
* cache servers, etc...
*/
$LowCal->log()->registerFile('mysqli', Config::get('LOGS_DIR'))
	->registerFile('memcached', Config::get('LOGS_DIR'));


if((isset($_COOKIE['PHPSESSID']) && strlen($_COOKIE['PHPSESSID']) != 64) || !isset($_COOKIE['PHPSESSID']))
{
	session_id(\LowCal\Helper\Strings::createCode(64));
}

session_start([
	'cookie_lifetime' => 0,
	'cookie_path' => '/',
	'cookie_domain' => Config::get('APP_COOKIE_URL'),
	'cookie_secure' => false,
	'cookie_httponly' => true,
]);

$LowCal->db()->addServer('firstmysqliserver', Config::get('DATABASE_SELECTED_TYPE'), Config::get('APP_DB_USER'), Config::get('APP_DB_PASSWORD'), Config::get('APP_DB_NAME'), Config::get('APP_DB_HOST'), Config::get('APP_DB_PORT'));