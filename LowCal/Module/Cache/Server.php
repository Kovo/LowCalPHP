<?php
declare(strict_types=1);
namespace LowCal\Module\Cache;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Module\Module;

/**
 * Class Server
 * @package LowCal\Module\Cache
 */
class Server extends Module
{
	/**
	 * @var null|string
	 */
	protected $_identifier = null;

	/**
	 * @var null|int
	 */
	protected $_type = null;

	/**
	 * @var string
	 */
	protected $_user = '';

	/**
	 * @var string
	 */
	protected $_password = '';

	/**
	 * @var string
	 */
	protected $_name = '';

	/**
	 * @var string
	 */
	protected $_host = '';

	/**
	 * @var null|int
	 */
	protected $_port = null;

	/**
	 * @var int
	 */
	protected $_connect_retry_attempts = 0;

	/**
	 * @var int
	 */
	protected $_connect_retry_delay = 0;

	/**
	 * @var null|Memcached|Couchbase|Local
	 */
	protected $_interaction_object = null;

	/**
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return $this->_interaction_object->isConnected();
	}

	/**
	 * @return null|string
	 */
	public function getIdentifier(): ?string
	{
		return $this->_identifier;
	}

	/**
	 * @return int|null
	 */
	public function getType(): ?int
	{
		return $this->_type;
	}

	/**
	 * @return string
	 */
	public function getUser(): string
	{
		return $this->_user;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->_password;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->_name;
	}

	/**
	 * @return string
	 */
	public function getHost(): string
	{
		return $this->_host;
	}

	/**
	 * @return int|null
	 */
	public function getPort(): ?int
	{
		return $this->_port;
	}

	/**
	 * @return int
	 */
	public function getConnectRetryAttempts(): int
	{
		return $this->_connect_retry_attempts;
	}

	/**
	 * @return int
	 */
	public function getConnectRetryDelay(): int
	{
		return $this->_connect_retry_delay;
	}

	/**
	 * @param string $identifier
	 * @return Server
	 */
	public function setIdentifier(string $identifier): Server
	{
		$this->_identifier = $identifier;

		return $this;
	}

	/**
	 * @param int $type
	 * @return Server
	 */
	public function setType(int $type): Server
	{
		$this->_type = $type;

		return $this;
	}

	/**
	 * @param string $user
	 * @return Server
	 */
	public function setUser(string $user): Server
	{
		$this->_user = $user;

		return $this;
	}

	/**
	 * @param string $password
	 * @return Server
	 */
	public function setPassword(string $password): Server
	{
		$this->_password = $password;

		return $this;
	}

	/**
	 * @param string $name
	 * @return Server
	 */
	public function setName(string $name): Server
	{
		$this->_name = $name;

		return $this;
	}

	/**
	 * @param string $host
	 * @return Server
	 */
	public function setHost(string $host): Server
	{
		$this->_host = $host;

		return $this;
	}

	/**
	 * @param int $port
	 * @return Server
	 */
	public function setPort(int $port): Server
	{
		$this->_port = $port;

		return $this;
	}

	/**
	 * @param int $connect_retry_attempts
	 * @return Server
	 */
	public function setConnectRetryAttempts(int $connect_retry_attempts): Server
	{
		$this->_connect_retry_attempts = $connect_retry_attempts;

		return $this;
	}

	/**
	 * @param int $connect_retry_delay
	 * @return Server
	 */
	public function setConnectRetryDelay(int $connect_retry_delay): Server
	{
		$this->_connect_retry_delay = $connect_retry_delay;

		return $this;
	}

	/**
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
	 * @return Couchbase|Memcached|Local|null
	 */
	public function getInteractionObject()
	{
		return $this->_interaction_object;
	}

	/**
	 * @return bool
	 */
	public function connect(): bool
	{
		return $this->_interaction_object->connect($this->_host, $this->_port, $this->_user, $this->_password, $this->_name);
	}

	/**
	 * @return bool
	 */
	public function disconnect(): bool
	{
		return $this->_interaction_object->disconnect();
	}
}