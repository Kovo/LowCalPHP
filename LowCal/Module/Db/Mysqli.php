<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Interfaces\Db;
use LowCal\Module\Module;

/**
 * Class Mysqli
 * @package LowCal\Module\Db
 */
class Mysqli extends Module implements Db
{
	/**
	 * @var bool
	 */
	protected $_is_connected = false;

	/**
	 * @var null|\mysqli
	 */
	protected $_db_object = null;

	/**
	 * @param string $user
	 * @param string $password
	 * @param string $name
	 * @param string $host
	 * @param int $port
	 * @return bool
	 * @throws \Exception
	 */
	public function connect(string $user, string $password, string $name, string $host, int $port): bool
	{
		if($this->_is_connected === false)
		{
			$this->_db_object = new \mysqli($host, $user, $password, $name, $port);

			if($this->_db_object->connect_error)
			{
				if(strpos($this->_db_object->connect_error, 'access denied') !== false)
				{
					$error_string = 'Excpetion during connection attempt: '.$this->_db_object->connect_error.' | '.$this->_db_object->connect_errno;

					$this->_Base->log()->add('mysqli', $error_string);

					$this->_is_connected = false;

					throw new \Exception($error_string, Codes::DB_CONNECT_ERROR);
				}

				for($x=0;$x<$this->_connectRetryAttempts;$x++)
				{
					sleep($this->_connectRetryDelay);

					$this->_db_object = new \mysqli($host, $user, $password, $name, $port);

					if($this->_db_object->connect_error)
					{
						$error_string = 'Excpetion during connection attempt: '.$this->_db_object->connect_error.' | '.$this->_db_object->connect_errno;

						$this->_Base->log()->add('mysqli', $error_string);

						if(strpos($this->_db_object->connect_error, 'access denied') !== false)
						{
							$this->_is_connected = false;

							throw new \Exception($error_string, Codes::DB_CONNECT_ERROR);
						}
					}
					else
					{
						break;
					}
				}

				if($this->_is_connected === false)
				{
					$error_string = 'Failed to connect to database after several attempts.';

					$this->_Base->log()->add('mysqli', $error_string);

					throw new \Exception($error_string, Codes::DB_CONNECT_ERROR);
				}
			}
		}

		$this->_is_connected = true;

		return true;
	}

	public function disconnect(): bool
	{
		return true;
	}
}