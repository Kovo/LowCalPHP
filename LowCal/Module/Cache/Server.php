<?php
declare(strict_types=1);
namespace LowCal\Module\Cache;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Module\Module;

/**
 * Class Server
 * This server class maintains server specific information for the chosen caching system.
 * It abstracts from specific caching classes to allow easy switching between different types of caching systems.
 * @package LowCal\Module\Cache
 */
class Server extends Module
{
	/**
	 * Id for this server instance.
	 * @var null|string
	 */
	protected $_identifier = null;

	/**
	 * Type of server (Memcached, etc...)
	 * @var null|int
	 */
	protected $_type = null;

	/**
	 * User to login with (if necessary).
	 * @var string
	 */
	protected $_user = '';

	/**
	 * Password to login with (if necessary).
	 * @var string
	 */
	protected $_password = '';

	/**
	 * Bucket/DB name to use (if necessary).
	 * @var string
	 */
	protected $_name = '';

	/**
	 * Host to login with (if necessary).
	 * @var string
	 */
	protected $_host = '';

	/**
	 * Port to login with (if necessary).
	 * @var null|int
	 */
	protected $_port = null;

	/**
	 * How many times will LowCal try to connect to a cache server.
	 * @var int
	 */
	protected $_connect_retry_attempts = 0;

	/**
	 * Delay in seconds between connection retries.
	 * @var int
	 */
	protected $_connect_retry_delay = 0;

	/**
	 * Interaction objects are simply the chosen cache type's object.
	 * So for Memcached, this will return \Memcached, etc...
	 * @var null|Memcached|Couchbase|Local
	 */
	protected $_interaction_object = null;

	/**
	 * Returns the state of the connection for the cache server.
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return $this->_interaction_object->isConnected();
	}

	/**
	 * Get server's ID.
	 * @return null|string
	 */
	public function getIdentifier(): ?string
	{
		return $this->_identifier;
	}

	/**
	 * Get server's type.
	 * @return int|null
	 */
	public function getType(): ?int
	{
		return $this->_type;
	}

	/**
	 * Get server's user.
	 * @return string
	 */
	public function getUser(): string
	{
		return $this->_user;
	}

	/**
	 * Get server's password.
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->_password;
	}

	/**
	 * Get server's db/bucket name.
	 * @return string
	 */
	public function getName(): string
	{
		return $this->_name;
	}

	/**
	 * Get server's host.
	 * @return string
	 */
	public function getHost(): string
	{
		return $this->_host;
	}

	/**
	 * Get server's port.
	 * @return int|null
	 */
	public function getPort(): ?int
	{
		return $this->_port;
	}

	/**
	 * Return the setting for connection retry attempts.
	 * @return int
	 */
	public function getConnectRetryAttempts(): int
	{
		return $this->_connect_retry_attempts;
	}

	/**
	 * Return the setting for connection retry delay.
	 * @return int
	 */
	public function getConnectRetryDelay(): int
	{
		return $this->_connect_retry_delay;
	}

	/**
	 * Set this server's ID.
	 * @param string $identifier
	 * @return Server
	 */
	public function setIdentifier(string $identifier): Server
	{
		$this->_identifier = $identifier;

		return $this;
	}

	/**
	 * Set this server's type.
	 * @param int $type
	 * @return Server
	 */
	public function setType(int $type): Server
	{
		$this->_type = $type;

		return $this;
	}

	/**
	 * Set this server's user.
	 * @param string $user
	 * @return Server
	 */
	public function setUser(string $user): Server
	{
		$this->_user = $user;

		return $this;
	}

	/**
	 * Set this server's password.
	 * @param string $password
	 * @return Server
	 */
	public function setPassword(string $password): Server
	{
		$this->_password = $password;

		return $this;
	}

	/**
	 * Set this server's db/bucket name.
	 * @param string $name
	 * @return Server
	 */
	public function setName(string $name): Server
	{
		$this->_name = $name;

		return $this;
	}

	/**
	 * Set this server's host.
	 * @param string $host
	 * @return Server
	 */
	public function setHost(string $host): Server
	{
		$this->_host = $host;

		return $this;
	}

	/**
	 * Set this server's port.
	 * @param int $port
	 * @return Server
	 */
	public function setPort(int $port): Server
	{
		$this->_port = $port;

		return $this;
	}

	/**
	 * Set this server's connection retry attempts.
	 * @param int $connect_retry_attempts
	 * @return Server
	 */
	public function setConnectRetryAttempts(int $connect_retry_attempts): Server
	{
		$this->_connect_retry_attempts = $connect_retry_attempts;

		return $this;
	}

	/**
	 * Set this server's connection retry attempt delay.
	 * @param int $connect_retry_delay
	 * @return Server
	 */
	public function setConnectRetryDelay(int $connect_retry_delay): Server
	{
		$this->_connect_retry_delay = $connect_retry_delay;

		return $this;
	}

	/**
	 * Initialize cache system.
	 * This is a required step before using your chosen cache provider.
	 * @throws \Exception
	 */
	public function init(): void
	{
		switch($this->_type)
		{
			case Config::get('CACHE_TYPE_LOCAL'):
				$this->_interaction_object = new Local($this->_Base, $this->_identifier);
				break;
			case Config::get('CACHE_TYPE_MEMCACHED'):
				$this->_interaction_object = new Memcached($this->_Base, $this->_identifier, $this->_connect_retry_attempts, $this->_connect_retry_delay, Config::get('SETTING_CACHE_LOCK_EXPIRE_TIME_SECONDS'));
				break;
			case Config::get('CACHE_TYPE_COUCHBASE'):
				$this->_interaction_object = new Couchbase($this->_Base, $this->_identifier, $this->_connect_retry_attempts, $this->_connect_retry_delay, Config::get('SETTING_CACHE_LOCK_EXPIRE_TIME_SECONDS'));
				break;
			default:
				throw new \Exception('Invalid Cache type provided.', Codes::CACHE_BAD_TYPE);
		}
	}

	/**
	 * Return the interaction object.
	 * @return Couchbase|Memcached|Local|null
	 */
	public function getInteractionObject()
	{
		return $this->_interaction_object;
	}

	/**
	 * Connect to the cache server.
	 * @return bool
	 */
	public function connect(): bool
	{
		return $this->_interaction_object->connect($this->_host, $this->_port, $this->_user, $this->_password, $this->_name);
	}

	/**
	 * Disconnect from the cache server.
	 * @return bool
	 */
	public function disconnect(): bool
	{
		return $this->_interaction_object->disconnect();
	}
}