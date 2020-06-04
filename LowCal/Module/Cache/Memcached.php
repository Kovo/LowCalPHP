<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Module\Cache;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Interfaces\Module\Cache;

/**
 * Class Memcached
 * This memcached class implements cache-centric functionality.
 * @package LowCal\Module\Cache
 */
class Memcached extends \LowCal\Module\Cache\Cache implements Cache
{
	/**
	 * \Memcached object instance is stored here.
	 * @var null|\Memcached
	 */
	protected $_cache_object = null;

	/**
	 * Memcached constructor.
	 * @param Base $Base
	 * @param string $server_identifier
	 * @param int $connect_retry_attempts
	 * @param int $connect_retry_delay
	 * @param int $lock_timeout_seconds
	 */
	function __construct(Base $Base, string $server_identifier, int $connect_retry_attempts, int $connect_retry_delay, int $lock_timeout_seconds)
	{
		parent::__construct($Base);

		$this->_server_identifier = $server_identifier;
		$this->_connect_retry_attempts = $connect_retry_attempts;
		$this->_connect_retry_delay = $connect_retry_delay;
		$this->_lock_timeout_seconds = $lock_timeout_seconds;
	}

	/**
	 * Memcached destructor.
	 */
	function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Enable value compression.
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
	 * Disable value compression.
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
	 * Connects to the Memcached server.
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
							$this->_is_connected = true;

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
			else
			{
				$this->_is_connected = true;
			}
		}

		return true;
	}

	/**
	 * Disconnects from the Memcached server.
	 * @return bool
	 */
	public function disconnect(): bool
	{
		if($this->_is_connected === true)
		{
			if(is_object($this->_cache_object) && method_exists($this->_cache_object, 'quit'))
			{
				$this->_cache_object->quit();
			}

			$this->_cache_object = null;

			$this->_is_connected = false;

			return true;
		}

		return false;
	}

	/**
	 * Returns \Memcached object.
	 * @return \Memcached
	 */
	public function getCacheObject(): \Memcached
	{
		$this->_Base->cache()->server($this->_server_identifier)->connect();

		return $this->_cache_object;
	}

	/**
	 * Gets the requested key, allowing you to check for active locks, and setting them as well.
	 * If an active lock is detected, the method will wait until the lock expires, and then returns the value (if it exists).
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $set_lock
	 * @param null $cas_token
	 * @return Results
	 * @throws \Exception
	 */
	public function get(string $key, bool $check_lock = false, bool $set_lock = false, &$cas_token = null): Results
	{
		$Results = new Results($this->_Base);

		try
		{
			$this->_Base->cache()->server($this->_server_identifier)->connect();

			if($check_lock)
			{
				while($this->_cache_object->get($key.'_LOCK'))
				{
					usleep(random_int(1000,500000));
				}
			}

			while($set_lock && !$this->_cache_object->add($key.'_LOCK', true, $this->_lock_timeout_seconds))
			{
				usleep(random_int(1000,500000));
			}

			$this->_last_error_message = '';
			$this->_last_error_number = '';

			if(defined('\Memcached::GET_EXTENDED'))
			{
				//your IDE might flag the next line as an error. It is not.
				$returned_array = $this->_cache_object->get($key, null, \Memcached::GET_EXTENDED);
				$Results->value = $returned_array['value'];
				$cas_token = $returned_array['cas'];
			}
			else
			{
				$Results->value = $this->_cache_object->get($key, null, $cas_token);
			}
		}
		catch(\Exception $e)
		{
			if($e->getCode() === Codes::CACHE_CANNOT_SET_LOCK)
			{
				throw new \Exception($e->getMessage(), $e->getCode());
			}

			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('memcached', 'Exception during get of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');
		}

		return $Results;
	}

	/**
	 * Sets a new value, or updates and existing one.
	 * You need to delete your lock during this step if you set one during your get.
	 * @param string $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $delete_lock
	 * @return bool
	 */
	public function set(string $key, $value, int $timeout = 0, bool $delete_lock = false): bool
	{
		try
		{
			$this->_Base->cache()->server($this->_server_identifier)->connect();

			if($this->_cache_object->set($key, $value, $timeout))
			{
				if($delete_lock)
				{
					$this->_cache_object->delete($key.'_LOCK');
				}

				$this->_last_error_message = '';
				$this->_last_error_number = '';

				return true;
			}
			else
			{
				$this->_last_error_message = $this->_cache_object->getResultMessage();
				$this->_last_error_number = $this->_cache_object->getResultCode();

				return false;
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('memcached', 'Exception during set of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}

	/**
	 * Adds a new value, or fails if its key already exists.
	 * You need to delete your lock during this step if you set one during your get.
	 * @param string $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $delete_lock
	 * @return bool
	 */
	public function add(string $key, $value, int $timeout = 0, bool $delete_lock = false): bool
	{
		try
		{
			$this->_Base->cache()->server($this->_server_identifier)->connect();

			if($this->_cache_object->add($key, $value, $timeout))
			{
				if($delete_lock)
				{
					$this->_cache_object->delete($key.'_LOCK');
				}

				$this->_last_error_message = '';
				$this->_last_error_number = '';

				return true;
			}
			else
			{
				$this->_last_error_message = $this->_cache_object->getResultMessage();
				$this->_last_error_number = $this->_cache_object->getResultCode();

				return false;
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('memcached', 'Exception during add of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}

	/**
	 * Deletes provided key, and can also check for existing locks, and delete them as well.
	 * If an active lock is detected, the method will wait until the lock expires, and then deletes the key (if it exists).
	 * You need to delete your lock during this step if you set one during your get.
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $delete_lock
	 * @return bool
	 */
	public function delete(string $key, bool $check_lock = false, bool $delete_lock = false): bool
	{
		try
		{
			$this->_Base->cache()->server($this->_server_identifier)->connect();

			if($check_lock)
			{
				while($this->_cache_object->get($key.'_LOCK'))
				{
					usleep(random_int(1000,500000));
				}
			}

			if($this->_cache_object->delete($key))
			{
				if($delete_lock)
				{
					$this->_cache_object->delete($key.'_LOCK');
				}

				$this->_last_error_message = '';
				$this->_last_error_number = '';

				return true;
			}
			else
			{
				$this->_last_error_message = $this->_cache_object->getResultMessage();
				$this->_last_error_number = $this->_cache_object->getResultCode();

				return false;
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('memcached', 'Exception during delete of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}
}