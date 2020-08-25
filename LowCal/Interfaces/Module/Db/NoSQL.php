<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Interfaces\Module\Db;

use LowCal\Module\Db\Results;

/**
 * Interface NoSQL
 * Interface for NoSQL specific DBs.
 * @package LowCal\Interfaces\Module\Db
 */
interface NoSQL
{
	/**
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $set_lock
	 * @return Results
	 */
	public function getKV(string $key, bool $check_lock = false, bool $set_lock = false): Results;

	/**
	 * @param string $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $delete_lock
	 * @param string|null $cas
	 * @return bool
	 */
	public function setKV(string $key, $value, int $timeout = 0, bool $delete_lock = false, string $cas = null): bool;

	/**
	 * @param string $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $delete_lock
	 * @param string|null $cas
	 * @return bool
	 */
	public function addKV(string $key, $value, int $timeout = 0, bool $delete_lock = false, string $cas = null): bool;

	/**
	 * @param string $key
	 * @param bool $check_lock
	 * @param bool $delete_lock
	 * @param string|null $cas
	 * @return bool
	 */
	public function deleteKV(string $key, bool $check_lock = false, bool $delete_lock = false, string $cas = ''): bool;

	/**
	 * @param int $atomic_id_classifier
	 * @param int|null $atomic_id_secondary_classifier
	 * @param int $initial
	 * @param int $expiry
	 * @return int
	 */
	public function getNextId(int $atomic_id_classifier, ?int $atomic_id_secondary_classifier = null, int $initial = 100000, int $expiry = 0): int;
}