<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use LowCal\Base;
use LowCal\Interfaces\Db;

/**
 * Class Couchbase
 * @package LowCal\Module\Db
 */
class Couchbase extends \LowCal\Module\Db\Db implements Db
{
	/**
	 * @var null|\CouchbaseCluster
	 */
	protected $_cluster_object = null;

	/**
	 * Couchbase constructor.
	 * @param Base $Base
	 * @param string $server_identifier
	 * @param int $connect_retry_attempts
	 * @param int $connect_retry_delay
	 */
	function __construct(Base $Base, string $server_identifier, int $connect_retry_attempts, int $connect_retry_delay)
	{
		parent::__construct($Base);

		$this->_server_identifier = $server_identifier;
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
			$this->_cluster_object = new \CouchbaseCluster($host.':'.$port);

			if($this->_cluster_object->connect_error)
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
		return true;
	}

	/**
	 * @return \CouchbaseBucket|null
	 */
	public function getDbObject(): ?\CouchbaseBucket
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