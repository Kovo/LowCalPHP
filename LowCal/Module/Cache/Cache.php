<?php
declare(strict_types=1);
namespace LowCal\Module\Cache;
use LowCal\Module\Module;

/**
 * Class Cache
 * @package LowCal\Module\Cache
 */
class Cache extends Module
{
	/**
	 * @var bool
	 */
	protected $_is_connected = false;

	/**
	 * @var null|\Memcached|\Couchbase\Bucket|Local
	 */
	protected $_cache_object = null;

	/**
	 * @var string
	 */
	protected $_server_identifier = '';

	/**
	 * @var int
	 */
	protected $_connect_retry_attempts = 0;

	/**
	 * @var int
	 */
	protected $_connect_retry_delay = 0;

	/**
	 * @var string
	 */
	protected $_last_error_message = '';

	/**
	 * @var int
	 */
	protected $_last_error_number = 0;

	/**
	 * @return string
	 */
	public function getLastErrorMessage(): string
	{
		return $this->_last_error_message;
	}

	/**
	 * @return int
	 */
	public function getLastErrorNumber(): int
	{
		return $this->_last_error_number;
	}

	/**
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return $this->_is_connected;
	}
}