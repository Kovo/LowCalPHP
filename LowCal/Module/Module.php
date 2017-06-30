<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Base;

/**
 * Class Module
 * The core class used by many modules and submodules.
 * @package LowCal\Module
 */
class Module
{
	/**
	 * LowCal Base object is stored here.
	 * @var null|Base
	 */
	protected $_Base = null;

	/**
	 * Module constructor.
	 * @param Base $Base
	 */
	protected function __construct(Base $Base)
	{
		$this->_Base = $Base;
	}
}