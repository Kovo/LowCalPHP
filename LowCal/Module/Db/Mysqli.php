<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Strings;
use LowCal\Interfaces\Db;

/**
 * Class Mysqli
 * @package LowCal\Module\Db
 */
class Mysqli extends \LowCal\Module\Db\Db implements Db
{
	/**
	 * @var float
	 */
	protected $_deadlock_first_interval_delay = 0.0;

	/**
	 * @var float
	 */
	protected $_deadlock_second_interval_delay = 0.0;

	/**
	 * @var int
	 */
	protected $_deadlock_first_interval_retries = 0;

	/**
	 * @var int
	 */
	protected $_deadlock_second_interval_retries = 0;

	/**
	 * Mysqli constructor.
	 * @param Base $Base
	 * @param string $server_identifier
	 * @param int $connect_retry_attempts
	 * @param int $connect_retry_delay
	 * @param float $deadlock_first_interval_delay
	 * @param float $deadlock_second_interval_delay
	 * @param int $deadlock_first_interval_retries
	 * @param int $deadlock_second_interval_retries
	 */
	function __construct(Base $Base, string $server_identifier, int $connect_retry_attempts, int $connect_retry_delay, float $deadlock_first_interval_delay, float $deadlock_second_interval_delay, int $deadlock_first_interval_retries, int $deadlock_second_interval_retries)
	{
		parent::__construct($Base);

		$this->_server_identifier = $server_identifier;
		$this->_connect_retry_attempts = $connect_retry_attempts;
		$this->_connect_retry_delay = $connect_retry_delay;
		$this->_deadlock_first_interval_delay = $deadlock_first_interval_delay;
		$this->_deadlock_second_interval_delay = $deadlock_second_interval_delay;
		$this->_deadlock_first_interval_retries = $deadlock_first_interval_retries;
		$this->_deadlock_second_interval_retries = $deadlock_second_interval_retries;
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

			if(!empty($this->_db_object->connect_error))
			{
				if(strpos($this->_db_object->connect_error, 'access denied') !== false)
				{
					$error_string = 'Exception during connection attempt: '.$this->_db_object->connect_error.' | '.$this->_db_object->connect_errno;

					$this->_Base->log()->add('mysqli', $error_string);

					throw new \Exception($error_string, Codes::DB_AUTH_CONNECT_ERROR);
				}

				if(!empty($this->_connect_retry_attempts))
				{
					for($x=0;$x<$this->_connect_retry_attempts;$x++)
					{
						sleep($this->_connect_retry_delay);

						$this->_db_object = new \mysqli($host, $user, $password, $name, $port);

						if(!empty($this->_db_object->connect_error))
						{
							$error_string = 'Exception during connection attempt: '.$this->_db_object->connect_error.' | '.$this->_db_object->connect_errno;

							$this->_Base->log()->add('mysqli', $error_string);

							if(strpos($this->_db_object->connect_error, 'access denied') !== false)
							{
								throw new \Exception($error_string, Codes::DB_AUTH_CONNECT_ERROR);
							}
						}
						else
						{
							$this->_is_connected = true;

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
			else
			{
				$this->_is_connected = true;
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function disconnect(): bool
	{
		if($this->_is_connected === true && is_object($this->_db_object) && method_exists($this->_db_object, 'close'))
		{
			$this->_db_object->close();
		}

		$this->_db_object = null;

		$this->_is_connected = false;

		return true;
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

		try
		{
			$this->_Base->db()->server($this->_server_identifier)->connect();

			$query = Strings::trim($query);
			$result = $this->_db_object->query($query);

			if(empty($result))
			{
				$this->_last_error_message = $this->_db_object->error;
				$this->_last_error_number = $this->_db_object->errno;

				$this->_Base->log()->add('mysqli', 'Query failed: "'.$query.' | Error: "#'.$this->_last_error_message.' / '.$this->_last_error_number.'"');
			}
			else
			{
				$this->_last_error_message = '';
				$this->_last_error_number = '';

				$Results->setAffectedRows($this->_db_object->affected_rows)
						->setInsertId($this->_db_object->insert_id)
						->setReturnedRows($result->num_rows)
						->setResults($result);

				$result->free();
				$result = null;
				unset($result);
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('mysqli', 'Excpetion during query: "'.$query.' | Exception: "#'.$this->_last_error_message.' / '.$this->_last_error_number.'"');
		}

		return $Results;
	}

	/**
	 * @param string $query
	 * @return Results
	 */
	public function select(string $query): Results
	{
		return $this->query($query);
	}

	/**
	 * @param string $query
	 * @return Results
	 */
	public function insert(string $query): Results
	{
		$Results = new Results($this->_Base);

		try
		{
			$this->_Base->db()->server($this->_server_identifier)->connect();

			$query = Strings::trim($query);

			$retry_codes = array(
				1213, //Deadlock found when trying to get lock
				1205 //Lock wait timeout exceeded
			);

			//Initialize
			$retry_count = 0;

			//Main loop
			do
			{
				//Initialize 'retry_flag' indicating whether or not we need to retry this transaction
				$retry_flag = 0;

				$result = $this->_db_object->query($query);

				// If failed,
				if(empty($result))
				{
					$this->_last_error_message = $this->_db_object->error;
					$this->_last_error_number = $this->_db_object->errno;

					// Determine if we need to retry this transaction -
					// If duplicate PRIMARY key error,
					// or one of the errors in 'retry_codes'
					// then we need to retry
					if($this->_last_error_number == 1062 && strpos($this->_last_error_message,"for key 'PRIMARY'") !== false)
					{
						$this->_Base->log()->add('mysqli', 'Query failed: Duplicate Primary Key error for query: "'.$query.'". | Error: "#'.$this->_last_error_number.' / '.$this->_last_error_message.'"');
					}

					$retry_flag = (in_array($this->_last_error_number, $retry_codes));

					if(!empty($retry_flag))
					{
						$this->_Base->log()->add('mysqli', 'Query failed: Deadlock detected for query: "'.$query.'" | Error: "#'.$this->_last_error_number.' / '.$this->_last_error_message.'"');
					}
				}

				// If successful or failed but no need to retry
				if(!empty($result) || empty($retry_flag))
				{
					break;
				}

				$retry_count++;

				if($retry_count <= $this->_deadlock_first_interval_retries)
				{
					if($retry_count === $this->_deadlock_first_interval_retries)
					{
						$this->_last_error_message = $this->_db_object->error;
						$this->_last_error_number = $this->_db_object->errno;

						$this->_Base->log()->add('mysqli', 'Reducing retry interval for deadlock detection on query: "'.$query.'". | Error: "#'.$this->_last_error_number.' / '.$this->_last_error_message.'"');
					}

					usleep($this->_deadlock_first_interval_delay*1000000);
				}
				elseif($retry_count > $this->_deadlock_first_interval_retries && $retry_count <= $this->_deadlock_second_interval_retries)
				{
					usleep($this->_deadlock_second_interval_delay*1000000);
				}
				else
				{
					$result = false;

					$this->_last_error_message = $this->_db_object->error;
					$this->_last_error_number = $this->_db_object->errno;

					$this->_Base->log()->add('mysqli', 'Finally gave up on query: "'.$query.'". | Error: "#'.$this->_last_error_number.' / '.$this->_last_error_message.'"');

					break;
				}
			}
			while(true);

			if(empty($result))
			{
				$this->_last_error_message = $this->_db_object->error;
				$this->_last_error_number = $this->_db_object->errno;

				$this->_Base->log()->add('mysqli', 'Query failed: "'.$query.'". | Error: "#'.$this->_last_error_number.' / '.$this->_last_error_message.'"');
			}
			elseif($retry_count > 0 && $retry_count < $this->_deadlock_second_interval_retries)
			{
				$this->_last_error_message = $this->_db_object->error;
				$this->_last_error_number = $this->_db_object->errno;

				$this->_Base->log()->add('mysqli', 'Query finally succeeded: "'.$query.'". | Error: "#'.$this->_last_error_number.' / '.$this->_last_error_message.'"');
			}
			else
			{
				$this->_last_error_message = '';
				$this->_last_error_number = '';

				$Results->setAffectedRows($this->_db_object->affected_rows)
						->setInsertId($this->_db_object->insert_id)
						->setReturnedRows($result->num_rows)
						->setResults($result);

				$result->free();
				$result = null;
				unset($result);
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('mysqli', 'Exception during query: "'.$query.' | Exception: "#'.$this->_last_error_number.' / '.$this->_last_error_message.'"');
		}

		return $Results;
	}

	/**
	 * @param string $query
	 * @return Results
	 */
	public function update(string $query): Results
	{
		return $this->insert($query);
	}

	/**
	 * @param string $query
	 * @return Results
	 */
	public function delete(string $query): Results
	{
		return $this->insert($query);
	}
}