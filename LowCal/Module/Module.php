<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Base;

/**
 * Class Module
 * @package LowCal\Module
 */
class Module
{
	/**
	 * @var null|Base
	 */
	protected $_Base = null;

	/**
	 * Module constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		$this->_Base = $Base;
	}
}