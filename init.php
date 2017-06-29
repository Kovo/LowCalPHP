<?php
declare(strict_types=1);
/*
 * NOTE: Your application starts here. Everything below this comment block is provided as a guideline.
 * NOTE: Feel free to clear init.php and start from scratch.
 */
use LowCal\Helper\Config;

/*
 * Configuration for routing
 */
$LowCal->routing()->setSiteUrl(Config::get('ROOT_URL'));
$LowCal->routing()->setBaseUri(Config::get('ROOT_URI'));

/*
 * Sample constants for routing patterns commonly used in web apps
 */
define('ROUTING_LANG_REG_EX', '[a-z]{2,3}+');
define('ROUTING_ID_REG_EX', '[0-9]+');
define('ROUTING_NAME_REG_EX', '[a-zA-Z0-9\-]+');

/*
 * Define routing rules here
 */
$LowCal->routing()->add('home', '/', '\LowCal\Controller\Home', 'indexAction');

/*
 * Begin db configs, logs, session, and start listening for routes
 */
try
{
	/*
	* Register logs here
	* You should register logs depending on what databases you will be using,
	* cache servers, etc...
	*/
	$LowCal->log()->registerFile('mysqli', Config::get('LOGS_DIR'))
		->registerFile('memcached', Config::get('LOGS_DIR'));

	/*
	* Set view engine
	*/
	$LowCal->view()->setViewEngineType(Config::get('VIEW_ENGINE_PHP'))
		->setViewEngineObject(new \LowCal\Module\View\PHP($LowCal));

	if(strlen(session_id()) != 64 || session_id() === '')
	{
		session_id(\LowCal\Helper\Strings::createCode(64));
	}

	session_start([
		'read_and_close' => true,
		'cookie_lifetime' => 0,
		'cookie_path' => '/',
		'cookie_domain' => Config::get('COOKIE_URL'),
		'cookie_secure' => false,
		'cookie_httponly' => true,
	]);

	$LowCal->db()->addServer('', Config::get('DATABASE_TYPE_MYSQLI'), Config::get('DB_USER'), Config::get('DB_PASSWORD'), Config::get('DB_NAME'), Config::get('DB_HOST'), Config::get('DB_PORT'));

	echo $LowCal->routing()->listen();
}
catch(Exception $e)
{
	if(in_array($e->getCode(), array(\LowCal\Helper\Codes::VIEW_NOT_FOUND,\LowCal\Helper\Codes::ROUTING_ERROR_NO_ROUTE)))
	{
		$LowCal->response()->setHeader('Status', '404 Not Found');
		$LowCal->response()->setHeader('HTTP/1.0 404 Not Found');
		$LowCal->locale()->addLanguage('en', 'en-us');
		echo $LowCal->view()->render('404', array('exceptionMsg' => $e->getMessage(), 'exceptionCode' => $e->getCode()));
	}
	else
	{
		$LowCal->response()->setHeader('Status', '500 Internal Server Error');
		$LowCal->response()->setHeader('HTTP/1.0 500 Internal Server Error');
		$LowCal->locale()->addLanguage('en', 'en-us');
		echo $LowCal->view()->render('500', array('exceptionMsg' => $e->getMessage(), 'exceptionCode' => $e->getCode()));
	}
}