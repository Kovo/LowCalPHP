<?php
declare(strict_types=1);

namespace LowCal\Interfaces\Module;

use LowCal\Module\Cache\Local;
use LowCal\Module\Cache\Results;

/**
 * Interfaces Cache
 * Interface for all cache types used in LowCal's module architecture.
 * @package LowCal\Interfaces\Module
 */
interface Cache
{
	/**
	 * @param string $host
	 * @param int $port
	 * @param string $user
	 * @param string $password
	 * @param string $name
	 * @return bool
	 */
	public function connect(string $host, int $port, string $user, string $password, string $name): bool;

	/**
	 * @return bool
	 */
	public function disconnect(): bool;

	/**
	 * @return bool
	 */
	public function isConnected(): bool;

	/**
	 * @return \Memcached|\Couchbase\Bucket|Local
	 */
	public function getCacheObject();

	/**
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $set_lock
	 * @return Results
	 */
	public function get(string $key, bool $check_lock = false, bool $set_lock = false): Results;

	/**
	 * @param string $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $delete_lock
	 * @return bool
	 */
	public function set(string $key, $value, int $timeout = 0, bool $delete_lock = false): bool;

	/**
	 * @param string $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $delete_lock
	 * @return bool
	 */
	public function add(string $key, $value, int $timeout = 0, bool $delete_lock = false): bool;

	/**
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $delete_lock
	 * @return bool
	 */
	public function delete(string $key, bool $check_lock = false, bool $delete_lock = false): bool;

	/**
	 * @param int $atomic_id_classifier
	 * @param int|null $atomic_id_secondary_classifier
	 * @param int $initial
	 * @param int $expiry
	 * @return int
	 */
	public function getNextId(int $atomic_id_classifier, ?int $atomic_id_secondary_classifier = null, int $initial = 100000, int $expiry = 0): int;
}