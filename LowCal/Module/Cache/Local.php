<?php
declare(strict_types=1);
namespace LowCal\Module\Cache;
use LowCal\Base;
use LowCal\Interfaces\Cache;

/**
 * Class Local
 * @package LowCal\Module\Cache
 */
class Local extends \LowCal\Module\Cache\Cache implements Cache
{
	/**
	 * @var array
	 */
	protected $_cache_bucket = array();

	/**
	 * @var array
	 */
	protected $_cache_locks = array();

	/**
	 * Local constructor.
	 * @param Base $Base
	 * @param string $server_identifier
	 */
	function __construct(Base $Base, string $server_identifier)
	{
		parent::__construct($Base);

		$this->_server_identifier = $server_identifier;
	}

	function __destruct()
	{
		$this->disconnect();
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
	public function connect(string $host = '', int $port = 0, string $user = '', string $password = '', string $name = ''): bool
	{
		if($this->_is_connected === false)
		{
			$this->_is_connected = true;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function disconnect(): bool
	{
		$this->_cache_bucket = null;
		$this->_cache_locks = null;

		$this->_cache_bucket = array();
		$this->_cache_locks = array();

		$this->_is_connected = false;

		return true;
	}

	/**
	 * @return Local
	 */
	public function getCacheObject(): Local
	{
		return $this->_cache_object;
	}

	/**
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $set_lock
	 * @param null $cas_token
	 * @return Results
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
					usleep(mt_rand(1000,500000));
				}
			}

			if($set_lock && !$this->_cache_object->add($key.'_LOCK', true, $this->_lock_timeout_seconds))
			{
				throw new \Exception('Cannot set lock key for '.$key.'.', Codes::CACHE_CANNOT_SET_LOCK);
			}

			$this->_last_error_message = '';
			$this->_last_error_number = '';

			$Results->value = $this->_cache_object->get($key, null, $cas_token);
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('memcached', 'Exception during get of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');
		}

		return $Results;
	}

	/**
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
					usleep(mt_rand(1000,500000));
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