<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Module\Cache;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Interfaces\Module\Cache;

/**
 * Class Couchbase
 * This couchbase class implements cache-centric functionality to mimic what Memcached does.
 * @package LowCal\Module\Cache
 */
class Couchbase extends \LowCal\Module\Cache\Cache implements Cache
{
	/**
	 * Couchbase bucket object is stored here.
	 * @var null|\Couchbase\Bucket
	 */
	protected $_cache_object = null;

	/**
	 * Couchbase cluster object is stored here.
	 * @var null|\Couchbase\Cluster
	 */
	protected $_cluster_object = null;

	/**
	 * Timeout retry counter.
	 * @var int
	 */
	protected $_timeout_retry_count = 0;

	/**
	 * Couchbase constructor.
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
	 * Couchbase destructor.
	 */
	function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Connects to the couchbase cluster and then opens the desired bucket.
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
			try
			{
				$Authenticator = new \Couchbase\PasswordAuthenticator();
				$Authenticator->username($name)->password($password);

				$this->_cluster_object = new \Couchbase\Cluster($host.($port!==0?':'.$port:'').Config::get('SETTING_DB_COUCHBASE_CONNECTION_CONFIGURATION_STRING'));
				$this->_cluster_object->authenticate($Authenticator);

				$this->_is_connected = true;
			}
			catch(\Exception $e)
			{
				$this->_Base->log()->add('couchbase_cache', 'Exception during connection attempt: '.$e->getMessage().' | '.$e->getCode());

				if(!empty($this->_connect_retry_attempts))
				{
					for($x=0;$x<$this->_connect_retry_attempts;$x++)
					{
						sleep($this->_connect_retry_delay);

						try
						{
							$this->_cluster_object = new \Couchbase\Cluster($host.($port!==0?':'.$port:'').Config::get('SETTING_CACHE_COUCHBASE_CONNECTION_CONFIGURATION_STRING'));
							$this->_cluster_object->authenticate($Authenticator);

							$this->_is_connected = true;

							break;
						}
						catch(\Exception $e)
						{
							$this->_Base->log()->add('couchbase_cache', 'Exception during connection attempt: '.$e->getMessage().' | '.$e->getCode());
						}
					}
				}
			}

			if($this->_is_connected === false)
			{
				$error_string = 'Failed to connect to cache server after several attempts.';

				$this->_Base->log()->add('couchbase_cache', $error_string);

				throw new \Exception($error_string, Codes::CACHE_CONNECT_ERROR);
			}

			try
			{
				$this->_cache_object = $this->_cluster_object->openBucket($name);
			}
			catch(\Exception $e)
			{
				$error_string = 'Failed to open to bucket. '.$e->getMessage().'/'.$e->getCode();

				$this->_Base->log()->add('couchbase_cache', $error_string);

				throw new \Exception($error_string, Codes::CACHE_CANNOT_OPEN_DATABASE);
			}

			if(empty($this->_cache_object))
			{
				$error_string = 'Issue opening bucket!';

				$this->_Base->log()->add('couchbase_cache', $error_string);

				throw new \Exception($error_string, Codes::DB_CANNOT_OPEN_DATABASE);
			}
		}

		return true;
	}

	/**
	 * Closes the current open bucket, and disconnects from the cluster (couchbase SDK dependant).
	 * @return bool
	 */
	public function disconnect(): bool
	{
		$this->_cluster_object = null;
		$this->_cache_object = null;

		$this->_is_connected = false;

		return true;
	}

	/**
	 * Returns the current couchbase bucket object.
	 * @return \Couchbase\Bucket
	 */
	public function getCacheObject(): \Couchbase\Bucket
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
	 * @return Results
	 * @throws \Exception
	 */
	public function get(string $key, bool $check_lock = false, bool $set_lock = false): Results
	{
		$Results = new Results($this->_Base);

		try
		{
			$this->_Base->cache()->server($this->_server_identifier)->connect();

			if($check_lock)
			{
				while($this->_cache_object->get($key.'_LOCK')->value)
				{
					usleep(random_int(1000,500000));
				}
			}

			try
			{
				while($set_lock && !$this->_cache_object->insert($key.'_LOCK', true, array('expiry'=>$this->_lock_timeout_seconds)))
				{
					usleep(random_int(1000,500000));
				}
			}
			catch(\Exception $e)
			{
				throw new \Exception($e->getMessage(), $e->getCode());
			}

			$this->_last_error_message = '';
			$this->_last_error_number = '';

			$result = $this->_cache_object->get($key);

			if(empty($result->error))
			{
				$Results->value = $result->value;
				$Results->cas = $result->cas;

				$this->_timeout_retry_count = 0;
			}
			else
			{
				throw new \Exception($result->error->getMessage(), $result->error->getCode());
			}
		}
		catch(\Exception $e)
		{
			if($e->getCode() === Codes::CACHE_CANNOT_SET_LOCK)
			{
				throw new \Exception($e->getMessage(), $e->getCode());
			}
			elseif($e->getCode() === 23/*LCB_ETIMEDOUT*/)
			{
				$this->_last_error_message = $e->getMessage();
				$this->_last_error_number = $e->getCode();

				if($this->_timeout_retry_count < Config::get('SETTING_DB_TIMEOUT_RETRIES'))
				{
					$this->_timeout_retry_count++;

					$this->_Base->log()->add('couchbase_cache', 'Exception during get of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'". Retrying...');

					sleep($this->_timeout_retry_count);

					return $this->get($key, $check_lock, $set_lock);
				}
				else
				{
					$this->_Base->log()->add('couchbase_cache', 'Exception during get of: "'.$key.'" | Exception: "#'.$e->getCode().' / '.$e->getMessage().'".');

					$this->_timeout_retry_count = 0;
				}
			}
			elseif($e->getCode() !== 13/*LCB_KEY_ENOENT*/)
			{
				$this->_last_error_message = $e->getMessage();
				$this->_last_error_number = $e->getCode();

				$this->_Base->log()->add('couchbase_cache', 'Exception during get of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

				$this->_timeout_retry_count = 0;
			}
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
	 * @param string|null $cas
	 * @return bool
	 */
	public function set(string $key, $value, int $timeout = 0, bool $delete_lock = false, string $cas = null): bool
	{
		try
		{
			$this->_Base->cache()->server($this->_server_identifier)->connect();

			try
			{
				$this->_cache_object->upsert($key, $value, array('expiry'=>$timeout, 'cas' => $cas));

				if($delete_lock)
				{
					$this->_cache_object->remove($key.'_LOCK');
				}

				$this->_last_error_message = '';
				$this->_last_error_number = '';

				$this->_timeout_retry_count = 0;

				return true;
			}
			catch(\Exception $e)
			{
				if($e->getCode() === 23/*LCB_ETIMEDOUT*/)
				{
					$this->_last_error_message = $e->getMessage();
					$this->_last_error_number = $e->getCode();

					if($this->_timeout_retry_count < Config::get('SETTING_DB_TIMEOUT_RETRIES'))
					{
						$this->_timeout_retry_count++;

						$this->_Base->log()->add('couchbase_cache', 'Exception during set of: "'.$key.'" | Exception: "#'.$e->getCode().' / '.$e->getMessage().'". Retrying...');

						sleep($this->_timeout_retry_count);

						return $this->set($key, $value, $timeout, $delete_lock, $cas);
					}
					else
					{
						$this->_last_error_message = $e->getMessage();
						$this->_last_error_number = $e->getCode();

						$this->_timeout_retry_count = 0;

						return false;
					}
				}
				else
				{
					$this->_last_error_message = $e->getMessage();
					$this->_last_error_number = $e->getCode();

					$this->_timeout_retry_count = 0;

					return false;
				}
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('couchbase_cache', 'Exception during set of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			$this->_timeout_retry_count = 0;

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
	 * @param string|null $cas
	 * @return bool
	 */
	public function add(string $key, $value, int $timeout = 0, bool $delete_lock = false, string $cas = null): bool
	{
		try
		{
			$this->_Base->cache()->server($this->_server_identifier)->connect();

			try
			{
				$this->_cache_object->insert($key, $value, array('expiry'=>$timeout, 'cas' => $cas));

				if($delete_lock)
				{
					$this->_cache_object->remove($key.'_LOCK');
				}

				$this->_last_error_message = '';
				$this->_last_error_number = '';

				$this->_timeout_retry_count = 0;

				return true;
			}
			catch(\Exception $e)
			{
				if($e->getCode() === 23/*LCB_ETIMEDOUT*/)
				{
					$this->_last_error_message = $e->getMessage();
					$this->_last_error_number = $e->getCode();

					if($this->_timeout_retry_count < Config::get('SETTING_DB_TIMEOUT_RETRIES'))
					{
						$this->_timeout_retry_count++;

						$this->_Base->log()->add('couchbase_cache', 'Exception during add of: "'.$key.'" | Exception: "#'.$e->getCode().' / '.$e->getMessage().'". Retrying...');

						sleep($this->_timeout_retry_count);

						return $this->add($key, $value, $timeout, $delete_lock, $cas);
					}
					else
					{
						$this->_last_error_message = $e->getMessage();
						$this->_last_error_number = $e->getCode();

						$this->_timeout_retry_count = 0;

						return false;
					}
				}
				else
				{
					$this->_last_error_message = $e->getMessage();
					$this->_last_error_number = $e->getCode();

					$this->_timeout_retry_count = 0;

					return false;
				}
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('couchbase_cache', 'Exception during add of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			$this->_timeout_retry_count = 0;

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
	 * @param string|null $cas
	 * @return bool
	 */
	public function delete(string $key, bool $check_lock = false, bool $delete_lock = false, string $cas = null): bool
	{
		try
		{
			$this->_Base->cache()->server($this->_server_identifier)->connect();

			try
			{
				if($check_lock)
				{
					while($this->_cache_object->get($key.'_LOCK')->value)
					{
						usleep(random_int(1000,500000));
					}
				}

				$this->_cache_object->remove($key, array('cas' => $cas));

				if($delete_lock)
				{
					$this->_cache_object->remove($key.'_LOCK');
				}

				$this->_last_error_message = '';
				$this->_last_error_number = '';

				$this->_timeout_retry_count = 0;

				return true;
			}
			catch(\Exception $e)
			{
				if($e->getCode() === 23/*LCB_ETIMEDOUT*/)
				{
					$this->_last_error_message = $e->getMessage();
					$this->_last_error_number = $e->getCode();

					if($this->_timeout_retry_count < Config::get('SETTING_DB_TIMEOUT_RETRIES'))
					{
						$this->_timeout_retry_count++;

						$this->_Base->log()->add('couchbase_cache', 'Exception during delete of: "'.$key.'" | Exception: "#'.$e->getCode().' / '.$e->getMessage().'". Retrying...');

						sleep($this->_timeout_retry_count);

						return $this->delete($key, $check_lock, $delete_lock, $cas);
					}
					else
					{
						$this->_last_error_message = $e->getMessage();
						$this->_last_error_number = $e->getCode();

						$this->_timeout_retry_count = 0;

						return false;
					}
				}
				else
				{
					$this->_last_error_message = $e->getMessage();
					$this->_last_error_number = $e->getCode();

					$this->_timeout_retry_count = 0;

					return false;
				}
			}
		}
		catch(\Exception $e)
		{
			$this->_last_error_message = $e->getMessage();
			$this->_last_error_number = $e->getCode();

			$this->_Base->log()->add('couchbase_cache', 'Exception during delete of: "'.$key.' | Exception: "#'.$e->getCode().' / '.$e->getMessage().'"');

			$this->_timeout_retry_count = 0;

			return false;
		}
	}

	/**
	 * This method returns a unique ID, atomically.
	 * @param int $atomic_id_classifier
	 * @param int|null $atomic_id_secondary_classifier
	 * @param int $initial
	 * @param int $expiry
	 * @return int
	 * @throws \Exception
	 */
	public function getNextId(int $atomic_id_classifier, ?int $atomic_id_secondary_classifier = null, int $initial = 100000, int $expiry = 0): int
	{
		try
		{
			$new_id = $this->_db_object->counter(
				'atomic_counter_incremental:'.$atomic_id_classifier.(!empty($atomic_id_secondary_classifier)?':'.$atomic_id_secondary_classifier:''),
				1,
				array(
					'initial'=> $initial,
					'expiry' => $expiry
				)
			)->value;
		}
		catch(\Exception $e)
		{
			if($e->getCode() === 23/*LCB_ETIMEDOUT*/)
			{
				if($this->_timeout_retry_count < Config::get('SETTING_DB_TIMEOUT_RETRIES'))
				{
					$this->_timeout_retry_count++;

					$this->_Base->log()->add('couchbase_db', 'Exception during new atomic id get of: "'.$atomic_id_classifier.'"/"'.$atomic_id_secondary_classifier.'" | Exception: "#'.$e->getCode().' / '.$e->getMessage().'". Retrying...');

					sleep($this->_timeout_retry_count);

					$new_id = $this->getNextId($atomic_id_classifier, $atomic_id_secondary_classifier, $initial, $expiry);
				}
				else
				{
					$this->_timeout_retry_count = 0;
				}
			}
			else
			{
				$this->_timeout_retry_count = 0;
			}
		}

		if(isset($new_id) && is_numeric($new_id))
		{
			$this->_timeout_retry_count = 0;

			return $new_id;
		}
		else
		{
			throw new \Exception('Unable to get new incremental atomic id for "'.$atomic_id_classifier.'"/"'.$atomic_id_secondary_classifier.'".', Codes::DB_FAILED_TO_GET);
		}
	}
}