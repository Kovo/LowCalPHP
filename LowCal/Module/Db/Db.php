<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use LowCal\Helper\Codes;
use LowCal\Module\Module;
use LowCal\Module\Security;

/**
 * Class Db
 * Base DB class that db type classes extend from. Offers some basic methods and properties.
 * @package LowCal\Module\Db
 */
class Db extends Module
{
	/**
	 * Flag indicating connection status.
	 * @var bool
	 */
	protected $_is_connected = false;

	/**
	 * Server ID.
	 * @var string
	 */
	protected $_server_identifier = '';

	/**
	 * Value for connection retry attempts.
	 * @var int
	 */
	protected $_connect_retry_attempts = 0;

	/**
	 * Value for delay between connection retry attempts.
	 * @var int
	 */
	protected $_connect_retry_delay = 0;

	/**
	 * Holds the last error message returned by the database.
	 * @var string
	 */
	protected $_last_error_message = '';

	/**
	 * Holds the last error code returned by the database.
	 * @var int
	 */
	protected $_last_error_number = 0;

	/**
	 * Holds the DB object (mysqli, etc...)
	 * @var null|\mysqli|\Couchbase\Bucket
	 */
	protected $_db_object = null;

	/**
	 * Sanitizes user input to prevent injection.
	 * @param $value
	 * @param bool $must_be_numeric
	 * @param int $decimal_places
	 * @param int $clean_flag
	 * @return array|string
	 */
	public function sanitize($value, bool $must_be_numeric = true, int $decimal_places = 2, int $clean_flag = Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
	{
		$this->_Base->db()->server($this->_server_identifier)->connect();

		return $this->_cleanQuery($value, $must_be_numeric, $decimal_places, $clean_flag);
	}

	/**
	 * Rules to clean html to different degrees.
	 * @param $value
	 * @param int $clean_all
	 * @return array|string
	 * @throws \Exception
	 */
	protected function _cleanHTML($value, int $clean_all = Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
	{
		if($clean_all !== false)
		{
			//empty blacklist array
			$html_remove = array();

			switch($clean_all)
			{
				case Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES:
				case Security::CLEAN_HTML_JS_STYLE_COMMENTS:
					if($clean_all === Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
					{
						foreach(array('<:&lt;', '>:&gt;') as $v)
						{
							$ex = explode(':', $v); //explode array item in question
							$value = str_replace($ex[0], $ex[1], $value); //do the replacements
						}
					}

					$html_remove[0] = '@<script[^>]*?>.*?</script>@si'; //bye bye javascript
					$html_remove[2] = '@<style[^>]*?>.*?</style>@siU'; //bye bye styling
					$html_remove[3] = '@<![\s\S]*?--[ \t\n\r]*>@'; //goodbye comments

					//now apply blacklist
					$value = preg_replace($html_remove, '', $value);
					$value = strip_tags($value);
					break;
				case Security::CLEAN_JS_STYLE_COMMENTS:
					$html_remove[0] = '@<script[^>]*?>.*?</script>@si'; //bye bye javascript
					$html_remove[1] = '@<style[^>]*?>.*?</style>@siU'; //bye bye styling
					$html_remove[2] = '@<![\s\S]*?--[ \t\n\r]*>@'; //goodbye comments

					//now apply blacklist
					$value = preg_replace($html_remove, '', $value);
					break;
				case Security::CLEAN_STYLE_COMMENTS:
					$html_remove[0] = '@<style[^>]*?>.*?</style>@siU'; //bye bye styling
					$html_remove[1] = '@<![\s\S]*?--[ \t\n\r]*>@'; //goodbye comments

					//now apply blacklist
					$value = preg_replace($html_remove, '', $value);
					break;
				default:
					throw new \Exception('', Codes::DB_INCORRECT_SANITIZE_DIRECTIVE);
			}
		}

		return $value;
	}

	/**
	 * Rules for basic type checking, and basic sanitization of user input.
	 * @param $value
	 * @param bool $must_be_numeric
	 * @param int $decimal_places
	 * @param int $clean_all
	 * @return array|string
	 */
	protected function _cleanQuery($value, bool $must_be_numeric = true, int $decimal_places = 2, int $clean_all = Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES)
	{
		if(is_array($value) === false)
		{
			if($value === null)
			{
				$value = "NULL";
			}
			else
			{
				if($must_be_numeric === true)
				{
					if((string)(float)$value == $value)
					{
						//32bit safe method to get floating point numbers or numbers beyond 32bit limit
						return bcmul($value, 1, $decimal_places);
					}
					else
					{
						//32bit safe method to get floating point numbers or numbers beyond 32bit limit
						return bcmul($value, 1, 0);
					}
				}

				$value = $this->_cleanHTML($value, $clean_all);

				if(method_exists($this->_db_object, 'real_escape_string'))
				{
					$value = $this->_db_object->real_escape_string($value);
				}
			}

			//we are done!
			return $value;
		}
		else
		{
			$sanitized_array = array();

			if(!empty($value))
			{
				foreach($value as $key => $val)
				{
					$sanitized_array[$key] = $this->_cleanQuery($val, $must_be_numeric, $decimal_places, $clean_all);
				}
			}

			return $sanitized_array;
		}
	}

	/**
	 * Get last error message returned by db.
	 * @return string
	 */
	public function getLastErrorMessage(): string
	{
		return $this->_last_error_message;
	}

	/**
	 * Get last error code returned by db.
	 * @return int
	 */
	public function getLastErrorNumber(): int
	{
		return $this->_last_error_number;
	}

	/**
	 * Return the current connection state of the db server.
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return $this->_is_connected;
	}
}