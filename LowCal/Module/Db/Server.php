<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Module\Db;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Module\Module;

/**
 * Class Server
 * This server class maintains server specific information for the chosen db system.
 * It abstracts from specific db classes to allow easy switching between different types of db systems.
 * @package LowCal\Module\Db
 */
class Server extends Module
{
	/**
	 * This server object's id.
	 * @var null|string
	 */
	protected $_identifier = null;

	/**
	 * The type of db to be used.
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
	 * Db Name/bucket to login with (if necessary).
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
	 * The amount of times LowCal will try to connect to the db server.
	 * @var int
	 */
	protected $_connect_retry_attempts = 0;

	/**
	 * Delay in seconds between retry attempts.
	 * @var int
	 */
	protected $_connect_retry_delay = 0;

	/**
	 * Delay in seconds between retrying cud operations in a deadlock state (level 1).
	 * @var float
	 */
	protected $_deadlock_first_interval_delay = 0.0;

	/**
	 * Delay in seconds between retrying cud operations in a deadlock state (level 2).
	 * @var float
	 */
	protected $_deadlock_second_interval_delay = 0.0;

	/**
	 * Amount of times cud operations in deadlock state should be retried before reducing delay.
	 * @var int
	 */
	protected $_deadlock_first_interval_retries = 0;

	/**
	 * Amount of times cud operations in deadlock state should be retried before giving up.
	 * @var int
	 */
	protected $_deadlock_second_interval_retries = 0;

	/**
	 * The interaction object is the current active database object (mysqli, etc...).
	 * @var null|Mysqli|Couchbase
	 */
	protected $_interaction_object = null;

	/**
	 * Server constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);
	}

	/**
	 * Returns the state of the current database connection.
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return $this->_interaction_object->isConnected();
	}

	/**
	 * Get this server object's ID.
	 * @return null|string
	 */
	public function getIdentifier(): ?string
	{
		return $this->_identifier;
	}

	/**
	 * Get the db type.
	 * @return int|null
	 */
	public function getType(): ?int
	{
		return $this->_type;
	}

	/**
	 * Get the db user.
	 * @return string
	 */
	public function getUser(): string
	{
		return $this->_user;
	}

	/**
	 * Get the db password.
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->_password;
	}

	/**
	 * Get the db bucket/name.
	 * @return string
	 */
	public function getName(): string
	{
		return $this->_name;
	}

	/**
	 * Get the db host.
	 * @return string
	 */
	public function getHost(): string
	{
		return $this->_host;
	}

	/**
	 * Get the db port.
	 * @return int|null
	 */
	public function getPort(): ?int
	{
		return $this->_port;
	}

	/**
	 * Get the current connection retry attempts setting.
	 * @return int
	 */
	public function getConnectRetryAttempts(): int
	{
		return $this->_connect_retry_attempts;
	}

	/**
	 * Get the current connection retry attempts delay setting.
	 * @return int
	 */
	public function getConnectRetryDelay(): int
	{
		return $this->_connect_retry_delay;
	}

	/**
	 * Get the current deadlock delay (level 1) setting.
	 * @return float
	 */
	public function getDeadlockFirstIntervalDelay(): float
	{
		return $this->_deadlock_first_interval_delay;
	}

	/**
	 * Get the current deadlock delay (level 2) setting.
	 * @return float
	 */
	public function getDeadlockSecondIntervalDelay(): float
	{
		return $this->_deadlock_second_interval_delay;
	}

	/**
	 * Get the current deadlock retry (level 1) setting.
	 * @return int
	 */
	public function getDeadlockFirstIntervalRetries(): int
	{
		return $this->_deadlock_first_interval_retries;
	}

	/**
	 * Get the current deadlock retry (level 2) setting.
	 * @return int
	 */
	public function getDeadlockSecondIntervalRetries(): int
	{
		return $this->_deadlock_second_interval_retries;
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
	 * Set db type.
	 * @param int $type
	 * @return Server
	 */
	public function setType(int $type): Server
	{
		$this->_type = $type;

		return $this;
	}

	/**
	 * Set db user.
	 * @param string $user
	 * @return Server
	 */
	public function setUser(string $user): Server
	{
		$this->_user = $user;

		return $this;
	}

	/**
	 * Set db password.
	 * @param string $password
	 * @return Server
	 */
	public function setPassword(string $password): Server
	{
		$this->_password = $password;

		return $this;
	}

	/**
	 * Set db name/bucket.
	 * @param string $name
	 * @return Server
	 */
	public function setName(string $name): Server
	{
		$this->_name = $name;

		return $this;
	}

	/**
	 * Set db host.
	 * @param string $host
	 * @return Server
	 */
	public function setHost(string $host): Server
	{
		$this->_host = $host;

		return $this;
	}

	/**
	 * Set db port.
	 * @param int $port
	 * @return Server
	 */
	public function setPort(int $port): Server
	{
		$this->_port = $port;

		return $this;
	}

	/**
	 * Set db connect retry attempts.
	 * @param int $connect_retry_attempts
	 * @return Server
	 */
	public function setConnectRetryAttempts(int $connect_retry_attempts): Server
	{
		$this->_connect_retry_attempts = $connect_retry_attempts;

		return $this;
	}

	/**
	 * Set db connect retry delay.
	 * @param int $connect_retry_delay
	 * @return Server
	 */
	public function setConnectRetryDelay(int $connect_retry_delay): Server
	{
		$this->_connect_retry_delay = $connect_retry_delay;

		return $this;
	}

	/**
	 * Set db deadlock interval delay (level 1).
	 * @param float $deadlock_first_interval_delay
	 * @return Server
	 */
	public function setDeadlockFirstIntervalDelay(float $deadlock_first_interval_delay): Server
	{
		$this->_deadlock_first_interval_delay = $deadlock_first_interval_delay;

		return $this;
	}

	/**
	 * Set db deadlock interval delay (level 2).
	 * @param float $deadlock_second_interval_delay
	 * @return Server
	 */
	public function setDeadlockSecondIntervalDelay(float $deadlock_second_interval_delay): Server
	{
		$this->_deadlock_second_interval_delay = $deadlock_second_interval_delay;

		return $this;
	}

	/**
	 * Set db deadlock interval retries (level 1).
	 * @param int $deadlock_first_interval_retries
	 * @return Server
	 */
	public function setDeadlockFirstIntervalRetries(int $deadlock_first_interval_retries): Server
	{
		$this->_deadlock_first_interval_retries = $deadlock_first_interval_retries;

		return $this;
	}

	/**
	 * Set db deadlock interval retries (level 2).
	 * @param int $deadlock_second_interval_retries
	 * @return Server
	 */
	public function setDeadlockSecondIntervalRetries(int $deadlock_second_interval_retries): Server
	{
		$this->_deadlock_second_interval_retries = $deadlock_second_interval_retries;

		return $this;
	}

	/**
	 * Initialize db system.
	 * This is a required step before using your chosen db provider.
	 * @throws \Exception
	 */
	public function init(): void
	{
		switch($this->_type)
		{
			case Config::get('DATABASE_TYPE_MYSQLI'):
				$this->_interaction_object = new Mysqli($this->_Base, $this->_identifier, $this->_connect_retry_attempts, $this->_connect_retry_delay, $this->_deadlock_first_interval_delay, $this->_deadlock_second_interval_delay, $this->_deadlock_first_interval_retries, $this->_deadlock_second_interval_retries);
				break;
			case Config::get('DATABASE_TYPE_COUCHBASE'):
				$this->_interaction_object = new Couchbase($this->_Base, $this->_identifier, $this->_connect_retry_attempts, $this->_connect_retry_delay);
				break;
			default:
				throw new \Exception('Invalid DB type provided.', Codes::DB_BAD_TYPE);
		}
	}

	/**
	 * Return the interaction object.
	 * @return Couchbase|Mysqli|null
	 */
	public function getInteractionObject()
	{
		return $this->_interaction_object;
	}

	/**
	 * Connect to the db server.
	 * @return bool
	 */
	public function connect(): bool
	{
		return $this->_interaction_object->connect($this->_user, $this->_password, $this->_name, $this->_host, $this->_port);
	}

	/**
	 * Disconnect from the db server.
	 * @return bool
	 */
	public function disconnect(): bool
	{
		return $this->_interaction_object->disconnect();
	}

	/**
	 * Change the current database/bucket.
	 * @param string $db_name
	 * @return bool
	 * @throws \Exception
	 */
	public function changeDatabase(string $db_name): bool
	{
		switch($this->_type)
		{
			case Config::get('DATABASE_TYPE_MYSQLI'):
				$this->connect();

				if($this->_interaction_object->getDbObject()->select_db($db_name))
				{
					return true;
				}
				else
				{
					$this->_Base->log()->add('mysqli', 'Failed to change database to '.$db_name.'.');

					return false;
				}
			case Config::get('DATABASE_TYPE_COUCHBASE'):
				$this->connect();

				try
				{
					$this->_interaction_object->setDBObject(
						$this->_interaction_object->getClusterObject()->openBucket($db_name)
					);

					return true;
				}
				catch(\Exception $e)
				{
					$this->_Base->log()->add('couchbase_db', 'Failed to open the '.$db_name.' bucket.');

					return false;
				}
			default:
				throw new \Exception('Invalid DB type provided.', Codes::DB_BAD_TYPE);
		}
	}

	/**
	 * Change the current user interacting with the database.
	 * @param string $user_name
	 * @param string $password
	 * @param string|null $db_name
	 * @return bool
	 * @throws \Exception
	 */
	public function changeUser(string $user_name, string $password, string $db_name = null): bool
	{
		switch($this->_type)
		{
			case Config::get('DATABASE_TYPE_MYSQLI'):
				$this->connect();
				return $this->_interaction_object->getDbObject()->change_user($user_name, $password, $db_name);
			case Config::get('DATABASE_TYPE_COUCHBASE'):
				return false;
			default:
				throw new \Exception('Invalid DB type provided.', Codes::DB_BAD_TYPE);
		}
	}
}