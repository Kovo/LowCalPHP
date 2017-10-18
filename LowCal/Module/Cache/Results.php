<?php
declare(strict_types=1);

namespace LowCal\Module\Cache;

use LowCal\Base;
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
	 * The returned cas value.
	 * @var null|string
	 */
	public $cas = null;

	/**
	 * Results constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);
	}

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