<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Module\Module;

/**
 * Class Server
 * @package LowCal\Module\Db
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
	 * @var null|Mysqli|Couchbase
	 */
	protected $_interaction_object = null;

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
			case Config::get('DATABASE_MYSQLI'):
				$this->_interaction_object = new Mysqli($this->_Base, $this->_identifier, $this->_connect_retry_attempts, $this->_connect_retry_delay);
				break;
			case Config::get('DATABASE_COUCHBASE'):
				$this->_interaction_object = new Couchbase($this->_Base);
				break;
			default:
				throw new \Exception('Invalid DB type provided.', Codes::DB_BAD_TYPE);
		}
	}

	/**
	 * @return Couchbase|Mysqli|null
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
		return $this->_interaction_object->connect($this->_user, $this->_password, $this->_name, $this->_host, $this->_port);
	}

	/**
	 * @return bool
	 */
	public function disconnect(): bool
	{
		return $this->_interaction_object->disconnect();
	}

	/**
	 * @param string $db_name
	 * @return bool
	 */
	public function changeDatabase(string $db_name): bool
	{
		return $this->_interaction_object->changeDatabase($db_name);
	}

	/**
	 * @param string $user_name
	 * @param string $password
	 * @param string|null $db_name
	 * @return bool
	 */
	public function changeUser(string $user_name, string $password, string $db_name = null): bool
	{
		return $this->_interaction_object->changeUser($user_name, $password, $db_name);
	}
}