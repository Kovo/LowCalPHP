<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Interfaces\Model\NoSQL;

use LowCal\Module\Db\Results;

/**
 * Interface Data
 * @package LowCal\Interfaces\Model
 */
interface Data
{
	/**
	 * @return string
	 */
	public function getFinalArrayString(): string;

	/**
	 * @param bool $for_insert
	 * @return array
	 */
	public function getFinalArray(bool $for_insert = false): array;

	/**
	 * @return string
	 */
	public function getPrefixedId(): string;

	/**
	 * @param array $full_json
	 */
	public function ingestJsonFromDatabase(array $full_json): void;

	/**
	 * @param array $full_json
	 * @param bool $for_delete
	 */
	public function ingestJsonFromRequest(array $full_json, bool $for_delete = false): void;

	/**
	 * @return Results
	 */
	public function insert(): Results;

	/**
	 * @return Results
	 */
	public function update(): Results;

	/**
	 * @return Results
	 */
	public function delete(): Results;

	/**
	 * @param int $id
	 * @return mixed
	 */
	public function setId(int $id);

	/**
	 * @return int
	 */
	public function getId(): ?int;
}