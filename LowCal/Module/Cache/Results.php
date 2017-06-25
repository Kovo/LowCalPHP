<?php
declare(strict_types=1);
namespace LowCal\Module\Cache;
use LowCal\Helper\Codes;
use LowCal\Module\Module;

/**
 * Class Results
 * @package LowCal\Module\Cache
 */
class Results extends Module
{
	/**
	 * @var null|mixed
	 */
	public $value = null;

	function __destruct()
	{
		$this->free();
	}

	public function free(): void
	{
		$this->value = null;
	}
}