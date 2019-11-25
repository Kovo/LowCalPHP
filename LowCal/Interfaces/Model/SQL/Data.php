<?php
declare(strict_types=1);

namespace LowCal\Interfaces\Model\SQL;

use LowCal\Module\Db\Results;

/**
 * Interface Data
 * @package LowCal\Interfaces\Model
 */
interface Data
{
	/**
	 * @param array $full_row
	 */
	public function ingestRowFromDatabase(array $full_row): void;

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