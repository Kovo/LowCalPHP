<?php
declare(strict_types=1);

namespace LowCal\Model;

use LowCal\Base;

/**
 * Class Model
 * @package LowCal\Model
 */
class Model
{
	/**
	 * @var null|Base
	 */
	protected $_LowCal = null;

	/**
	 * Model constructor.
	 * @param Base $LowCal
	 */
	protected function __construct(Base $LowCal)
	{
		$this->_LowCal = $LowCal;
	}
}