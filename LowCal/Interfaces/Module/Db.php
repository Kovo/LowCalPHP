<?php
declare(strict_types=1);
namespace LowCal\Interfaces;

/**
 * Interfaces Db
 * @package LowCal\Interfaces
 */
interface Db
{
	/**
	 * @return bool
	 */
	public function connect(string $user, string $password, string $name, string $host, int $port): bool;

	/**
	 * @return bool
	 */
	public function disconnect(): bool;

	/**
	 * @return bool
	 */
	public function isConnected(): bool;

	/**
	 * @return \mysqli|\CouchbaseBucket
	 */
	public function getDbObject();
}