<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use LowCal\Base;
use LowCal\Helper\Codes;
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
	 * @var null|int
	 */
	protected $_connect_retry_attempts = null;

	/**
	 * @var null|int
	 */
	protected $_connect_retry_delay = null;

	/**
	 * Mysqli constructor.
	 * @param Base $Base
	 * @param int $connect_retry_attempts
	 * @param int $connect_retry_delay
	 */
	function __construct(Base $Base, int $connect_retry_attempts, int $connect_retry_delay)
	{
		parent::__construct($Base);

		$this->_connect_retry_attempts = $connect_retry_attempts;
		$this->_connect_retry_delay = $connect_retry_delay;
	}

	function __destruct()
	{
		$this->disconnect();
	}

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

				if(!empty($this->_connect_retry_attempts))
				{
					for($x=0;$x<$this->_connect_retry_attempts;$x++)
					{
						sleep($this->_connect_retry_delay);

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
				}

				if($this->_is_connected === false)
				{
					$error_string = 'Failed to connect to database after several attempts.';

					$this->_Base->log()->add('mysqli', $error_string);

					throw new \Exception($error_string, Codes::DB_CONNECT_ERROR);
				}
			}

			$this->_is_connected = true;

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function disconnect(): bool
	{
		if($this->_is_connected === true && is_object($this->_db_object) && method_exists($this->_db_object, 'close'))
		{
			$this->_db_object->close();

			$this->_db_object = null;

			$this->_is_connected = false;

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return $this->_is_connected;
	}

	/**
	 * @return \mysqli|null
	 */
	public function getDbObject(): ?\mysqli
	{
		return $this->_db_object;
	}

	/**
	 * @param string $query
	 * @return Results
	 */
	public function query(string $query): Results
	{
		$Results = new Results($this->_Base);

		$Results->setResultsO($this->_db_object->query($query));

		return $Results;
	}

	/**
	 * @param string $query
	 * @return Results
	 */
	public function select(string $query): Results
	{
		$Results = new Results($this->_Base);

		return $Results;
	}

	/**
	 * @param string $query
	 * @return Results
	 */
	public function update(string $query): Results
	{
		$Results = new Results($this->_Base);

		return $Results;
	}

	/**
	 * @param string $query
	 * @return Results
	 */
	public function delete(string $query): Results
	{
		$Results = new Results($this->_Base);

		return $Results;
	}

	/**
	 * @param string $query
	 * @return Results
	 */
	public function insert(string $query): Results
	{
		$Results = new Results($this->_Base);

		return $Results;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function changeDatabase(string $name): bool
	{
		return $this->_db_object->select_db($name);
	}

	/**
	 * @param string $user_name
	 * @param string $password
	 * @param string|null $db_name
	 * @return bool
	 */
	public function changeUser(string $user_name, string $password, string $db_name = null): bool
	{
		return $this->_db_object->change_user($user_name, $password, $db_name);
	}
}