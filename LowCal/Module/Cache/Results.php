<?php
declare(strict_types=1);
namespace LowCal\Module\Cache;
use LowCal\Module\Module;

/**
 * Class Results
 * Results from cache
 * @package LowCal\Module\Cache
 */
class Results extends Module
{
	/**
	 * The returned cache value.
	 * @var null|mixed
	 */
	public $value = null;

	/**
	 * Results destructor.
	 */
	function __destruct()
	{
		$this->free();
	}

	/**
	 * Frees memory.
	 */
	public function free(): void
	{
		$this->value = null;
	}
}