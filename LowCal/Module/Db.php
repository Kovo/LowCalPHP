<?php
declare(strict_types=1);

namespace LowCal\Module;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Module\Db\Couchbase;
use LowCal\Module\Db\Mysqli;
use LowCal\Module\Db\Results;
use LowCal\Module\Db\Server;

/**
 * Class Db
 * The main DB module used to connect to and interact with different db providers.
 * @package LowCal\Module
 */
class Db extends Module
{
	/**
	 * Array of registered cache servers.
	 * @var array
	 */
	protected $_servers = array();

	/**
	 * The current active server id the db module will reference.
	 * @var null|string
	 */
	protected $_active_server_id = null;

	/**
	 * Whether query logging should take place.
	 * @var bool
	 */
	protected $_log_queries = false;

	/**
	 * Db constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);

		$this->_log_queries = Config::get('SETTING_DB_LOG_QUERIES');
	}

	/**
	 * Register a server with the db module.
	 * @param string $identifier
	 * @param int $type
	 * @param string $user
	 * @param string $password
	 * @param string $name
	 * @param string $host
	 * @param int $port
	 * @param bool $auto_connect
	 * @param bool $assign_active
	 * @return Db
	 * @throws \Exception
	 */
	public function addServer(string $identifier, int $type, string $user = '', string $password = '', string $name = '', string $host = 'localhost', int $port = 3306, bool $auto_connect = false, bool $assign_active = true): Db
	{
		if(empty($identifier))
		{
			throw new \Exception('Identifier cannot be empty', Codes::DB_IDENTIFIER_MISSING);
		}

		$this->_servers[$identifier] = new Server($this->_Base);

		$this->_servers[$identifier]->setIdentifier($identifier)
			->setType($type)
			->setUser($user)
			->setPassword($password)
			->setName($name)
			->setHost($host)
			->setPort($port)
			->setConnectRetryAttempts(Config::get('SETTING_DB_CONNECT_RETRY_ATTEMPTS'))
			->setConnectRetryDelay(Config::get('SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'))
			->setDeadlockFirstIntervalDelay(Config::get('SETTING_DB_WRITE_RETRY_FIRST_INTERVAL_DELAY_SECONDS'))
			->setDeadlockSecondIntervalDelay(Config::get('SETTING_DB_WRITE_RETRY_SECOND_INTERVAL_DELAY_SECONDS'))
			->setDeadlockFirstIntervalRetries(Config::get('SETTING_DB_WRITE_RETRY_FIRST_INTERVAL_RETRIES'))
			->setDeadlockSecondIntervalRetries(Config::get('SETTING_DB_WRITE_RETRY_SECOND_INTERVAL_RETRIES'));

		$this->_servers[$identifier]->init();

		if($assign_active)
		{
			$this->_active_server_id = $identifier;
		}

		if($auto_connect)
		{
			$this->_servers[$identifier]->connect();
		}

		return $this;
	}

	/**
	 * Unregister a registered server. Will also try to disconnect the target db connection.
	 * @param string $identifier
	 * @return Db
	 * @throws \Exception
	 */
	public function removeServer(string $identifier = ''): Db
	{
		if(empty($identifier) && !empty($this->_servers))
		{
			/**
			 * @var $server Server
			 */
			foreach($this->_servers as $id => $server)
			{
				$server->disconnect();
				$this->_servers[$id] = null;
				unset($this->_servers[$id]);
			}
		}
		elseif(isset($this->_servers[$identifier]))
		{
			$this->_servers[$identifier]->disconnect();
			$this->_servers[$identifier] = null;
			unset($this->_servers[$identifier]);
		}
		else
		{
			throw new \Exception('Identifier not found in server registry.', Codes::DB_IDENTIFIER_MISSING);
		}

		return $this;
	}

	/**
	 * Get the active server ID.
	 * @return null|string
	 */
	public function getActiveServerIdentifier(): ?string
	{
		return $this->_active_server_id;
	}

	/**
	 * Set the active server ID.
	 * @param string $identifier
	 * @return Db
	 * @throws \Exception
	 */
	public function setActiveServerIdentifier(string $identifier): Db
	{
		if(empty($identifier))
		{
			throw new \Exception('Identifier cannot be empty', Codes::DB_IDENTIFIER_MISSING);
		}

		$this->_active_server_id = $identifier;

		return $this;
	}

	/**
	 * Returns the active server object for further interaction.
	 * @param string $identifier
	 * @return Server
	 * @throws \Exception
	 */
	public function server(string $identifier = ''): Server
	{
		if(empty($identifier))
		{
			return $this->_servers[$this->_active_server_id];
		}
		elseif(isset($this->_servers[$identifier]))
		{
			return $this->_servers[$identifier];
		}
		else
		{
			throw new \Exception('Server with provided Identifier not found.', Codes::DB_IDENTIFIER_MISSING);
		}
	}

	/**
	 * Returns the interaction object from the server object for further interaction.
	 * @param string $identifier
	 * @return Couchbase|Mysqli|null
	 */
	public function interact(string $identifier = '')
	{
		return $this->server($identifier)->getInteractionObject();
	}

	/**
	 * Abstracted query method.
	 * @param string $query
	 * @param string $server_identifier
	 * @return Results
	 */
	public function query(string $query, string $server_identifier = ''): Results
	{
		if($this->_log_queries)
		{
			$this->_Base->log()->add('db_queries', $query);
		}

		return $this->interact($server_identifier)->query($query);
	}

	/**
	 * Abstracted select method.
	 * @param string $query
	 * @param string $server_identifier
	 * @return Results
	 */
	public function select(string $query, string $server_identifier = ''): Results
	{
		if($this->_log_queries)
		{
			$this->_Base->log()->add('db_queries', $query);
		}

		return $this->interact($server_identifier)->select($query);
	}

	/**
	 * Abstracted insert method.
	 * @param string $query
	 * @param string $server_identifier
	 * @return Results
	 */
	public function insert(string $query, string $server_identifier = ''): Results
	{
		if($this->_log_queries)
		{
			$this->_Base->log()->add('db_queries', $query);
		}

		return $this->interact($server_identifier)->insert($query);
	}

	/**
	 * Abstracted update method.
	 * @param string $query
	 * @param string $server_identifier
	 * @return Results
	 */
	public function update(string $query, string $server_identifier = ''): Results
	{
		if($this->_log_queries)
		{
			$this->_Base->log()->add('db_queries', $query);
		}

		return $this->interact($server_identifier)->update($query);
	}

	/**
	 * Abstracted delete method.
	 * @param string $query
	 * @param string $server_identifier
	 * @return Results
	 */
	public function delete(string $query, string $server_identifier = ''): Results
	{
		if($this->_log_queries)
		{
			$this->_Base->log()->add('db_queries', $query);
		}

		return $this->interact($server_identifier)->delete($query);
	}

	/**
	 * Abstracted sanitation method targeted for numeric values.
	 * @param $value
	 * @param string $server_identifier
	 * @return array|mixed|string
	 */
	public function sanitizeQueryValueNumeric($value, string $server_identifier = '')
	{
		return $this->interact($server_identifier)->sanitize($value, true);
	}

	/**
	 * Abstracted sanitation method targeted for non-numeric values.
	 * @param $value
	 * @param string $server_identifier
	 * @return array|mixed|string
	 */
	public function sanitizeQueryValueNonNumeric($value, string $server_identifier = '')
	{
		return $this->interact($server_identifier)->sanitize($value, false);
	}

	/**
	 * Abstracted sanitation method targeted for non-numeric values containing HTML.
	 * @param $value
	 * @param string $server_identifier
	 * @return array|mixed|string
	 */
	public function sanitizeQueryValueHTML($value, string $server_identifier = '')
	{
		return $this->interact($server_identifier)->sanitize($value, false, Security::CLEAN_JS_STYLE_COMMENTS);
	}

	/**
	 * Abstracted sanitation method targeted for all value types and will respect the input type.
	 * @param $value
	 * @param string $server_identifier
	 * @return array|mixed|string
	 */
	public function sanitizeQueryValueTypeSafe($value, string $server_identifier = '')
	{
		if(!is_array($value))
		{
			if(is_string($value))
			{
				return $this->sanitizeQueryValueNonNumeric($value, $server_identifier);
			}
			else
			{
				return $this->sanitizeQueryValueNumeric($value, $server_identifier);
			}
		}
		else
		{
			$sanitized_array = array();

			if(!empty($value))
			{
				foreach($value  as $key => $val)
				{
					$sanitized_array[$key] = $this->sanitizeQueryValueTypeSafe($val, $server_identifier);
				}
			}

			return $sanitized_array;
		}
	}
}