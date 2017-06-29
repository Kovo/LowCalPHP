<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Module\Cache\Couchbase;
use LowCal\Module\Cache\Local;
use LowCal\Module\Cache\Memcached;
use LowCal\Module\Cache\Results;
use LowCal\Module\Cache\Server;

/** 
 * Class Cache
 * The main cache module used for connecting to and interacting with cache providers.
 * @package LowCal\Module
 */
class Cache extends Module
{
	/**
	 * Array of registered cache servers.
	 * @var array
	 */
	protected $_servers = array();

	/**
	 * The current active server id the cache module will reference.
	 * @var null|string
	 */
	protected $_active_server_id = null;

	/**
	 * Register a server with the cache module.
	 * @param string $identifier
	 * @param int $type
	 * @param string $host
	 * @param int $port
	 * @param string $user
	 * @param string $password
	 * @param string $name
	 * @param bool $auto_connect
	 * @param bool $assign_active
	 * @return Cache
	 * @throws \Exception
	 */
	public function addServer(string $identifier, int $type, string $host = 'localhost', int $port = 3306, string $user = '', string $password = '', string $name = '', bool $auto_connect = false, bool $assign_active = true): Cache
	{
		if(empty($identifier))
		{
			throw new \Exception('Identifier cannot be empty', Codes::CACHE_IDENTIFIER_MISSING);
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
			->setConnectRetryDelay(Config::get('SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'));

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
	 * Unregister a registered server. Will also try to disconnect the target cache connection.
	 * @param string $identifier
	 * @return Cache
	 * @throws \Exception
	 */
	public function removeServer(string $identifier = ''): Cache
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
	 * @return Cache
	 * @throws \Exception
	 */
	public function setActiveServerIdentifier(string $identifier): Cache
	{
		if(empty($identifier))
		{
			throw new \Exception('Identifier cannot be empty', Codes::CACHE_IDENTIFIER_MISSING);
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
			throw new \Exception('Server with provided Identifier not found.', Codes::CACHE_IDENTIFIER_MISSING);
		}
	}

	/**
	 * Returns the interaction object from the server object for further interaction.
	 * @param string $identifier
	 * @return Memcached|Couchbase|null|Local
	 */
	public function interact(string $identifier = '')
	{
		return $this->server($identifier)->getInteractionObject();
	}

	/**
	 * Abstracted get method.
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $set_lock
	 * @param null $cas_token
	 * @param string $server_identifier
	 * @return Results
	 */
	public function get(string $key, bool $check_lock = false, bool $set_lock = false, &$cas_token = null, string $server_identifier = ''): Results
	{
		return $this->interact($server_identifier)->get($key, $check_lock, $set_lock, $cas_token);
	}

	/**
	 * Abstracted set method.
	 * @param string $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $delete_lock
	 * @param string $server_identifier
	 * @return bool
	 */
	public function set(string $key, $value, int $timeout = 0, bool $delete_lock = false, string $server_identifier = ''): bool
	{
		return $this->interact($server_identifier)->set($key, $value, $timeout, $delete_lock);
	}

	/**
	 * Abstracted add method.
	 * @param string $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $delete_lock
	 * @param string $server_identifier
	 * @return bool
	 */
	public function add(string $key, $value, int $timeout = 0, bool $delete_lock = false, string $server_identifier = ''): bool
	{
		return $this->interact($server_identifier)->add($key, $value, $timeout, $delete_lock);
	}

	/**
	 * Abstracted delete method.
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $delete_lock
	 * @param string $server_identifier
	 * @return bool
	 */
	public function delete(string $key, bool $check_lock = false, bool $delete_lock = false, string $server_identifier = ''): bool
	{
		return $this->interact($server_identifier)->delete($key, $check_lock, $delete_lock);
	}
}