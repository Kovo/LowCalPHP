<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use LowCal\Interfaces\Db;
use LowCal\Module\Module;

/**
 * Class Couchbase
 * @package LowCal\Module\Db
 */
class Couchbase extends Module implements Db
{
	public function connect(string $user, string $password, string $name, string $host, int $port): bool
	{
		return true;
	}

	public function disconnect(): bool
	{
		return true;
	}
}