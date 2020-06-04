<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Module\Cache;

use LowCal\Base;
use LowCal\Module\Module;

/**
 * Class Cache
 * Base Cache class that cache type classes extend from. Offers some basic methods and properties.
 * @package LowCal\Module\Cache
 */
class Cache extends Module
{
	/**
	 * Flag to see if cache server is connected or not.
	 * @var bool
	 */
	protected $_is_connected = false;

	/**
	 * Server id.
	 * @var string
	 */
	protected $_server_identifier = '';

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
	 * Last error message returned by the cache server.
	 * @var string
	 */
	protected $_last_error_message = '';

	/**
	 * Last error code returned by the cache server.
	 * @var int
	 */
	protected $_last_error_number = 0;

	/**
	 * How long (in seconds) will key locks last.
	 * @var int
	 */
	protected $_lock_timeout_seconds = 0;

	/**
	 * Generic cache object is stored here.
	 * @var null|\Couchbase\Bucket|\Memcached|Local
	 */
	protected $_cache_object = null;

	/**
	 * Cache constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);
	}

	/**
	 * Method returns last error message from the cache server.
	 * @return string
	 */
	public function getLastErrorMessage(): string
	{
		return $this->_last_error_message;
	}

	/**
	 * Method returns last error code from the cache server.
	 * @return int
	 */
	public function getLastErrorNumber(): int
	{
		return $this->_last_error_number;
	}

	/**
	 * Change the lock timeout.
	 * @param int $timeout_seconds
	 * @return Cache
	 */
	public function setLockTimeout(int $timeout_seconds): Cache
	{
		$this->_lock_timeout_seconds = $timeout_seconds;

		return $this;
	}

	/**
	 * Get the current lock timeout.
	 * @return int
	 */
	public function getLockTimeout(): int
	{
		return $this->_lock_timeout_seconds;
	}

	/**
	 * Return if the cache server is connected or not.
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return $this->_is_connected;
	}
}