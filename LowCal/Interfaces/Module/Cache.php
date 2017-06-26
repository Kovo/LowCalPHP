<?php
declare(strict_types=1);
namespace LowCal\Interfaces;
use LowCal\Module\Cache\Local;
use LowCal\Module\Cache\Results;

/**
 * Interfaces Cache
 * @package LowCal\Interfaces
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