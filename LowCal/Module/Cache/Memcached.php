<?php
declare(strict_types=1);
namespace LowCal\Module\Cache;
use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Interfaces\Cache;

/**
 * Class Memcached
 * @package LowCal\Module\Cache
 */
class Memcached extends \LowCal\Module\Cache\Cache implements Cache
{
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
	 * @return Memcached
	 */
	public function enableCompression(): Memcached
	{
		if(is_object($this->_cache_object) && method_exists($this->_cache_object, 'setOption'))
		{
			$this->_cache_object->setOption(\Memcached::OPT_COMPRESSION, true);
		}

		return $this;
	}

	/**
	 * @return Memcached
	 */
	public function disableCompression(): Memcached
	{
		if(is_object($this->_cache_object) && method_exists($this->_cache_object, 'setOption'))
		{
			$this->_cache_object->setOption(\Memcached::OPT_COMPRESSION, false);
		}

		return $this;
	}

	/**
	 * @param string $host
	 * @param int $port
	 * @param string $user
	 * @param string $password
	 * @param string $name
	 * @return bool
	 * @throws \Exception
	 */
	public function connect(string $host, int $port, string $user = '', string $password = '', string $name = ''): bool
	{
		if($this->_is_connected === false)
		{
			$this->_cache_object = new \Memcached();

			if(!$this->_cache_object->addServer($host, $port))
			{
				if(!empty($this->_connect_retry_attempts))
				{
					for($x=0;$x<$this->_connect_retry_attempts;$x++)
					{
						sleep($this->_connect_retry_delay);

						if(!$this->_cache_object->addServer($host, $port))
						{
							$this->_Base->log()->add('memcached', 'Exception during connection attempt to cache server.');
						}
						else
						{
							break;
						}
					}
				}

				if($this->_is_connected === false)
				{
					$error_string = 'Failed to connect to cache server after several attempts.';

					$this->_Base->log()->add('memcached', $error_string);

					throw new \Exception($error_string, Codes::CACHE_CONNECT_ERROR);
				}
			}

			$this->_is_connected = true;

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function disconnect(): bool
	{
		if($this->_is_connected === true && is_object($this->_cache_object) && method_exists($this->_cache_object, 'quit'))
		{
			$this->_cache_object->quit();

			$this->_cache_object= '';

			$this->_is_connected = false;

			return true;
		}

		return false;
	}

	/**
	 * @return \Memcached|\Couchbase\Bucket|Local
	 */
	public function getCacheObject(): \Memcached
	{
		return $this->_cache_object;
	}

	/**
	 * @param string $key
	 * @return Results
	 */
	public function get(string $key): Results;

	/**
	 * @param string $key
	 * @return Results
	 */
	public function set(string $key): Results;

	/**
	 * @param string $key
	 * @return Results
	 */
	public function add(string $key): Results;

	/**
	 * @param string $key
	 * @return Results
	 */
	public function update(string $key): Results;

	/**
	 * @param string $key
	 * @return Results
	 */
	public function delete(string $key): Results;
}