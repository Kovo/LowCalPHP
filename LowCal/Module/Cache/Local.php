<?php
declare(strict_types=1);
namespace LowCal\Module\Cache;
use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Interfaces\Cache;

/**
 * Class Local
 * @package LowCal\Module\Cache
 */
class Local extends \LowCal\Module\Cache\Cache implements Cache
{
	/**
	 * @var null|Local
	 */
	protected $_cache_object = null;

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
	 * @return Results
	 */
	public function get(string $key, bool $check_lock = false, bool $set_lock = false): Results
	{
		$Results = new Results($this->_Base);

		try
		{
			if($check_lock)
			{
				while($this->_checkLock($key.'_LOCK'))
				{
					sleep(1);
				}
			}

			if($set_lock && !$this->add($key.'_LOCK', true, $this->_lock_timeout_seconds))
			{
				throw new \Exception('Cannot set lock key for '.$key.'.', Codes::CACHE_CANNOT_SET_LOCK);
			}

			$this->_last_error_message = '';
			$this->_last_error_number = '';

			if(array_key_exists($key, $this->_cache_bucket))
			{
				if($this->_cache_bucket[$key]['timeout'] === 0 || time() < $this->_cache_bucket[$key]['timeout'])
				{
					$Results->value = $this->_cache_bucket[$key]['value'];
				}
				else
				{
					$this->_cache_bucket[$key]['value'] = null;
					$this->_cache_bucket[$key] = null;
					unset($this->_cache_bucket[$key]);
				}
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('local', 'Exception during get of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');
		}

		return $Results;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	protected function _checkLock(string $key): bool
	{
		if(isset($this->_cache_locks[$key]))
		{
			if(time() < $this->_cache_locks[$key])
			{
				return true;
			}

			unset($this->_cache_locks[$key]);
		}

		return false;
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

			$this->_cache_bucket[$key] = array(
				'timeout' => ($timeout==0?0:time()+$timeout),
				'value' => $value
			);

			if($delete_lock)
			{
				$this->delete($key.'_LOCK');
			}

			return true;
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('local', 'Exception during set of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

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

			if(!array_key_exists($key, $this->_cache_bucket))
			{
				$this->_cache_bucket[$key] = array(
					'timeout' => ($timeout==0?0:time()+$timeout),
					'value' => $value
				);

				if($delete_lock)
				{
					$this->delete($key.'_LOCK');
				}

				return true;
			}
			else
			{
				$this->_last_error_message = 'Cannot add key/value pair.';
				$this->_last_error_number = Codes::CACHE_CANNOT_SET_KEYVALUE;

				return false;
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('local', 'Exception during add of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

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
				while($this->_checkLock($key.'_LOCK'))
				{
					sleep(1);
				}
			}

			if(array_key_exists($key, $this->_cache_bucket))
			{
				$this->_cache_bucket[$key]['value'] = null;
				$this->_cache_bucket[$key] = null;
				unset($this->_cache_bucket[$key]);
			}

			if($delete_lock)
			{
				$this->delete($key.'_LOCK');
			}

			return true;
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('local', 'Exception during delete of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			return false;
		}
	}
}