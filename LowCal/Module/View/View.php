<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Module\View;

use LowCal\Base;
use LowCal\Module\Module;

/**
 * Class View
 * This the main view class that view engines using the LowCal module architecture should use.
 * @package LowCal\Module\View
 */
class View extends Module
{
	/**
	 * The base view directory path.
	 * @var string
	 */
	protected $_view_dir = '';

	/**
	 * View constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);
	}
}