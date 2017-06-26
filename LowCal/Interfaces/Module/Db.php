<?php
declare(strict_types=1);
namespace LowCal\Interfaces;
use LowCal\Module\Db\Couchbase;
use LowCal\Module\Db\Mysqli;
use LowCal\Module\Db\Results;
use LowCal\Module\Security;

/**
 * Interfaces Db
 * @package LowCal\Interfaces
 */
interface Db
{
	/**
	 * @param string $user
	 * @param string $password
	 * @param string $name
	 * @param string $host
	 * @param int $port
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
	 * @return \mysqli|\Couchbase\Bucket
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

	/**
	 * @param $value
	 * @param bool $must_be_numeric
	 * @param int $decimal_places
	 * @param int $clean_flag
	 * @return mixed
	 */
	public function sanitize($value, bool $must_be_numeric = true, int $decimal_places = 2, int $clean_flag = Security::CLEAN_HTML_JS_STYLE_COMMENTS_HTMLENTITIES);
}