<?php
declare(strict_types=1);
namespace LowCal\Interfaces;
use LowCal\Module\Db\Results;

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

	/**
	 * @param string $query
	 * @return Results
	 */
	public function query(string $query): Results;

	/**
	 * @param string $query
	 * @return Results
	 */
	public function select(string $query): Results;

	/**
	 * @param string $query
	 * @return Results
	 */
	public function update(string $query): Results;

	/**
	 * @param string $query
	 * @return Results
	 */
	public function delete(string $query): Results;

	/**
	 * @param string $query
	 * @return Results
	 */
	public function insert(string $query): Results;

	/**
	 * @return string
	 */
	public function getLastErrorMessage(): string;

	/**
	 * @return int
	 */
	public function getLastErrorNumber(): int;
}