<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use Couchbase\N1qlQuery;
use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Strings;
use LowCal\Interfaces\Db;

/**
 * Class Couchbase
 * @package LowCal\Module\Db
 */
class Couchbase extends \LowCal\Module\Db\Db implements Db
{
	/**
	 * @var null|\Couchbase\Cluster
	 */
	protected $_cluster_object = null;

	/**
	 * @var int
	 */
	protected $_n1ql_query_consistency = N1qlQuery::NOT_BOUNDED;

	/**
	 * @var bool
	 */
	protected $_n1ql_query_adhoc = true;

	/**
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

	function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * @return Couchbase
	 */
	public function setQueryConsistencyNotBound(): Couchbase
	{
		$this->_n1ql_query_consistency = N1qlQuery::NOT_BOUNDED;

		return $this;
	}

	/**
	 * @return Couchbase
	 */
	public function setQueryConsistencyRequestPlus(): Couchbase
	{
		$this->_n1ql_query_consistency = N1qlQuery::REQUEST_PLUS;

		return $this;
	}

	/**
	 * @return Couchbase
	 */
	public function setQueryConsistencyStatementPlus(): Couchbase
	{
		$this->_n1ql_query_consistency = N1qlQuery::STATEMENT_PLUS;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getQueryConsistency(): int
	{
		return $this->_n1ql_query_consistency;
	}

	/**
	 * @param bool $adhoc
	 * @return Couchbase
	 */
	public function setQueryAdhoc(bool $adhoc): Couchbase
	{
		$this->_n1ql_query_adhoc = $adhoc;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getQueryAdhoc(): bool
	{
		return $this->_n1ql_query_adhoc;
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
			if(!empty($password))
			{
				$Authenticator = new \Couchbase\ClassicAuthenticator();
				$Authenticator->bucket($name, $password);
			}

			try
			{
				$this->_cluster_object = new \Couchbase\Cluster($host.':'.$port);
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
							$this->_cluster_object = new \Couchbase\Cluster($host.':'.$port);
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
	 * @return \Couchbase\Bucket|null
	 */
	public function getDbObject(): ?\Couchbase\Bucket
	{
		return $this->_db_object;
	}

	/**
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
	 * @return \Couchbase\Cluster
	 */
	public function getClusterObject(): \Couchbase\Cluster
	{
		return $this->_cluster_object;
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

			$QueryObject = N1qlQuery::fromString($query);
			$QueryObject->consistency($this->_n1ql_query_consistency);
			$QueryObject->adhoc($this->_n1ql_query_adhoc);

			$result = $this->_db_object->query($QueryObject, true);

			$this->_last_error_message = '';
			$this->_last_error_number = '';

			$Results->setAffectedRows($result['metrics']['resultCount'])
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

			$this->_Base->log()->add('couchbase_db', 'Excpetion during query: "'.$query.' | Exception: "#'.$this->_last_error_message.' / '.$this->_last_error_number.'"');
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
		return $this->query($query);
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