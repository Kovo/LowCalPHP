<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use Couchbase\N1qlQuery;
use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Helper\Strings;
use LowCal\Interfaces\Module\Db;

/**
 * Class Couchbase
 * This couchbase class implements rdbm-centric functionality to mimic what Mysql does.
 * @package LowCal\Module\Db
 */
class Couchbase extends \LowCal\Module\Db\Db implements Db
{
	/**
	 * The couchbase cluster object is stored here.
	 * @var null|\Couchbase\Cluster
	 */
	protected $_cluster_object = null;

	/**
	 * N1QL query consistency flag.
	 * @var int
	 */
	protected $_n1ql_query_consistency = N1qlQuery::NOT_BOUNDED;

	/**
	 * Whether N1QL queries should prepared or not at the SDK level.
	 * @var bool
	 */
	protected $_n1ql_query_adhoc = true;

	/**
	 * Timeout for locks on key/value lookups.
	 * @var int
	 */
	protected $_lock_timeout_seconds = 0;

	/**
	 * The couchbase bucket object is stored here.
	 * @var null|\Couchbase\Bucket
	 */
	protected $_db_object = null;

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

	/**
	 * Couchbase destructor.
	 */
	function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Set N1QL consistency to NOT BOUND (will not look for freshest data).
	 * @return Couchbase
	 */
	public function setQueryConsistencyNotBound(): Couchbase
	{
		$this->_n1ql_query_consistency = N1qlQuery::NOT_BOUNDED;

		return $this;
	}

	/**
	 * Set N1QL consistency to REQUEST PLUS (will return fresh data).
	 * @return Couchbase
	 */
	public function setQueryConsistencyRequestPlus(): Couchbase
	{
		$this->_n1ql_query_consistency = N1qlQuery::REQUEST_PLUS;

		return $this;
	}

	/**
	 * Set N1QL consistency to STATEMENT PLUS (will return freshest data).
	 * @return Couchbase
	 */
	public function setQueryConsistencyStatementPlus(): Couchbase
	{
		$this->_n1ql_query_consistency = N1qlQuery::STATEMENT_PLUS;

		return $this;
	}

	/**
	 * Get current query consistency.
	 * @return int
	 */
	public function getQueryConsistency(): int
	{
		return $this->_n1ql_query_consistency;
	}

	/**
	 * If set to true, queries will be prepared at the SDK level to increase execution speed for subsequent calls to the same query.
	 * @param bool $adhoc
	 * @return Couchbase
	 */
	public function setQueryAdhoc(bool $adhoc): Couchbase
	{
		$this->_n1ql_query_adhoc = $adhoc;

		return $this;
	}

	/**
	 * Get current adhoc setting.
	 * @return bool
	 */
	public function getQueryAdhoc(): bool
	{
		return $this->_n1ql_query_adhoc;
	}

	/**
	 * Set the lock timeout/expiry for key/value lookups.
	 * @param int $seconds
	 * @return Couchbase
	 */
	public function setLocktimeout(int $seconds): Couchbase
	{
		$this->_lock_timeout_seconds = $seconds;

		return $this;
	}

	/**
	 * Connects to the couchbase cluster, and then opens the desired bucket.
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
			if(!empty($password))
			{
				$Authenticator = new \Couchbase\ClassicAuthenticator();
				$Authenticator->bucket($name, $password);
			}

			try
			{
				$this->_cluster_object = new \Couchbase\Cluster($host.($port!==0?':'.$port:'').Config::get('SETTING_DB_COUCHBASE_CONNECTION_CONFIGURATION_STRING'));
				$this->_is_connected = true;
			}
			catch(\Exception $e)
			{
				$this->_Base->log()->add('couchbase_db', 'Exception during connection attempt: '.$e->getMessage().' | '.$e->getCode());

				if(!empty($this->_connect_retry_attempts))
				{
					for($x=0;$x<$this->_connect_retry_attempts;$x++)
					{
						sleep($this->_connect_retry_delay);

						try
						{
							$this->_cluster_object = new \Couchbase\Cluster($host.($port!==0?':'.$port:'').Config::get('SETTING_DB_COUCHBASE_CONNECTION_CONFIGURATION_STRING'));
							$this->_is_connected = true;

							break;
						}
						catch(\Exception $e)
						{
							$this->_Base->log()->add('couchbase_db', 'Exception during connection attempt: '.$e->getMessage().' | '.$e->getCode());
						}
					}
				}
			}

			if($this->_is_connected === false)
			{
				$error_string = 'Failed to connect to database after several attempts.';

				$this->_Base->log()->add('couchbase_db', $error_string);

				throw new \Exception($error_string, Codes::DB_CONNECT_ERROR);
			}

			try
			{
				$this->_db_object = $this->_cluster_object->openBucket($name);
			}
			catch(\Exception $e)
			{
				$error_string = 'Failed to open to bucket.';

				$this->_Base->log()->add('couchbase_db', $error_string);

				throw new \Exception($error_string, Codes::DB_CANNOT_OPEN_DATABASE);
			}
		}

		return true;
	}

	/**
	 * Closes the current open bucket, and disconnects from the cluster (couchbase SDK dependant).
	 * @return bool
	 */
	public function disconnect(): bool
	{
		$this->_cluster_object = null;
		$this->_db_object = null;

		$this->_is_connected = false;

		return true;
	}

	/**
	 * Returns the current couchbase bucket object.
	 * @return \Couchbase\Bucket|null
	 */
	public function getDbObject(): ?\Couchbase\Bucket
	{
		$this->_Base->db()->server($this->_server_identifier)->connect();

		return $this->_db_object;
	}

	/**
	 * Set the current couchbase bucket object.
	 * @param \Couchbase\Bucket $Bucket
	 * @return Couchbase
	 */
	public function setDBObject(\Couchbase\Bucket $Bucket): Couchbase
	{
		$this->_db_object = null; //hopefully couchbase sdk closes the connection
		$this->_db_object = $Bucket;

		return $this;
	}

	/**
	 * Get current couchbase cluster object.
	 * @return \Couchbase\Cluster
	 */
	public function getClusterObject(): \Couchbase\Cluster
	{
		return $this->_cluster_object;
	}

	/**
	 * Send a generic N1QL query.
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

			$QueryObject = N1qlQuery::fromString($query);
			$QueryObject->consistency($this->_n1ql_query_consistency);
			$QueryObject->adhoc($this->_n1ql_query_adhoc);

			$result = $this->_db_object->query($QueryObject, true);

			if(!is_array($result) && is_object($result))
			{
				$result = json_decode(json_encode($result), true);
			}

			$this->_last_error_message = '';
			$this->_last_error_number = '';

			if(isset($result['metrics']['errorCount']) && $result['metrics']['errorCount'] > 0)
			{
				$Results->setErrorDetected();
			}

			$Results->setAffectedRows($result['metrics']['mutationCount'] ?? 0)
				->setReturnedRows($result['metrics']['resultCount'])
				->setResults($result['rows']);

			$QueryObject = null;
			$result = null;
			unset($result,$QueryObject);
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$Results->setErrorDetected();

			$this->_Base->log()->add('couchbase_db', 'Exception during query: "'.$query.' | Exception: "#'.$this->_last_error_message.' / '.$this->_last_error_number.'"');
		}

		return $Results;
	}

	/**
	 * Stub for emulation of other DB systems (can also be changed for specific logic for select N1QL queries).
	 * @param string $query
	 * @return Results
	 */
	public function select(string $query): Results
	{
		return $this->query($query);
	}

	/**
	 * Stub for emulation of other DB systems (can also be changed for specific logic for insert N1QL queries).
	 * @param string $query
	 * @return Results
	 */
	public function insert(string $query): Results
	{
		return $this->query($query);
	}

	/**
	 * Stub for emulation of other DB systems (can also be changed for specific logic for update N1QL queries).
	 * @param string $query
	 * @return Results
	 */
	public function update(string $query): Results
	{
		return $this->insert($query);
	}

	/**
	 * Stub for emulation of other DB systems (can also be changed for specific logic for delete N1QL queries).
	 * @param string $query
	 * @return Results
	 */
	public function delete(string $query): Results
	{
		return $this->insert($query);
	}

	/**
	 * Gets the requested key, allowing you to check for active locks, and setting them as well.
	 * If an active lock is detected, the method will wait until the lock expires, and then returns the value (if it exists).
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $set_lock
	 * @return Results
	 * @throws \Exception
	 */
	public function getKV(string $key, bool $check_lock = false, bool $set_lock = false): Results
	{
		$Results = new Results($this->_Base);

		try
		{
			$this->_Base->db()->server($this->_server_identifier)->connect();

			if($check_lock)
			{
				while($this->_db_object->get($key.'_LOCK')->value)
				{
					usleep(random_int(1000,500000));
				}
			}

			try
			{
				while($set_lock && !$this->_db_object->insert($key.'_LOCK', true, array('expiry'=>$this->_lock_timeout_seconds)))
				{
					throw new \Exception('Cannot set lock key for '.$key.'.', Codes::DB_CANNOT_SET_LOCK);
				}
			}
			catch(\Exception $e)
			{
				throw new \Exception($e->getMessage(), $e->getCode());
			}

			$this->_last_error_message = '';
			$this->_last_error_number = '';

			$result = $this->_db_object->get($key);

			if(empty($result->error))
			{
				$Results->setResults(!is_array($result->value)?array((array)$result->value):$result->value);
				$Results->setReturnedRows((
				is_array($result)?count($result):1
				));
			}
			else
			{
				if(!empty($result->error))
				{
					throw new \Exception($result->error->getMessage(), $result->error->getCode());
				}
				else
				{
					throw new \Exception('Error occurred when fetching K/V, but no exception was provided by SDK.', Codes::DB_SDK_UNKNOWN);
				}
			}
		}
		catch(\Exception $e)
		{
			if($e->getCode() === Codes::DB_CANNOT_SET_LOCK)
			{
				throw new \Exception($e->getMessage(), $e->getCode());
			}
			elseif($e->getCode() !== 13/*LCB_KEY_ENOENT*/)
			{
				$this->_last_error_message = $e->getMessage();
				$this->_last_error_number = $e->getCode();

				$Results->setErrorDetected();

				$this->_Base->log()->add('couchbase_db', 'Exception during get of: "'.$key.'" | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');
			}
		}

		return $Results;
	}

	/**
	 * Sets a new value, or updates and existing one.
	 * You need to delete your lock during this step if you set one during your get.
	 * @param string $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $delete_lock
	 * @param string|null $cas
	 * @return bool
	 */
	public function setKV(string $key, $value, int $timeout = 0, bool $delete_lock = false, string $cas = null): bool
	{
		try
		{
			$this->_Base->db()->server($this->_server_identifier)->connect();

			try
			{
				$this->_db_object->upsert($key, $value, array('expiry'=>$timeout, 'cas' => $cas));

				if($delete_lock)
				{
					$this->_db_object->remove($key.'_LOCK');
				}

				$this->_last_error_message = '';
				$this->_last_error_number = '';

				return true;
			}
			catch(\Exception $e)
			{
				$this->_last_error_message = $e->getMessage();
				$this->_last_error_number = $e->getCode();

				return false;
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('couchbase_db', 'Exception during set of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}

	/**
	 * Adds a new value, or fails if its key already exists.
	 * You need to delete your lock during this step if you set one during your get.
	 * @param string $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $delete_lock
	 * @param string|null $cas
	 * @return bool
	 */
	public function addKV(string $key, $value, int $timeout = 0, bool $delete_lock = false, string $cas = null): bool
	{
		try
		{
			$this->_Base->db()->server($this->_server_identifier)->connect();

			try
			{
				$this->_db_object->insert($key, $value, array('expiry'=>$timeout, 'cas' => $cas));

				if($delete_lock)
				{
					$this->_db_object->remove($key.'_LOCK');
				}

				$this->_last_error_message = '';
				$this->_last_error_number = '';

				return true;
			}
			catch(\Exception $e)
			{
				$this->_last_error_message = $e->getMessage();
				$this->_last_error_number = $e->getCode();

				return false;
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('couchbase_db', 'Exception during add of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}

	/**
	 * Deletes provided key, and can also check for existing locks, and delete them as well.
	 * If an active lock is detected, the method will wait until the lock expires, and then deletes the key (if it exists).
	 * You need to delete your lock during this step if you set one during your get.
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $delete_lock
	 * @param string|null $cas
	 * @return bool
	 */
	public function deleteKV(string $key, bool $check_lock = false, bool $delete_lock = false, string $cas = ''): bool
	{
		try
		{
			$this->_Base->db()->server($this->_server_identifier)->connect();

			try
			{
				if($check_lock)
				{
					while($this->_db_object->get($key.'_LOCK')->value)
					{
						usleep(random_int(1000,500000));
					}
				}

				$this->_db_object->remove($key, array('cas' => $cas));

				if($delete_lock)
				{
					$this->_db_object->remove($key.'_LOCK');
				}

				$this->_last_error_message = '';
				$this->_last_error_number = '';

				return true;
			}
			catch(\Exception $e)
			{
				$this->_last_error_message = $e->getMessage();
				$this->_last_error_number = $e->getCode();

				return false;
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('couchbase_db', 'Exception during delete of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}
}