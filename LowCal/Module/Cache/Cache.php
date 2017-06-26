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
	 * @return bool
	 */
	public function isConnected(): bool
	{
		return $this->_is_connected;
	}
}