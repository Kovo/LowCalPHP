<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Controller;

use LowCal\Base;
use LowCal\Helper\Codes;

/**
 * Class Controller
 * This is the "master" controller that you can choose to extend your application controllers from.
 * @package LowCal\Controller
 */
class Controller
{
	/**
	 * Base LowCal object is stored in the master controller.
	 * @var null|Base
	 */
	protected $_Base = null;

	/**
	 * Controller constructor.
	 * @param Base $Base
	 */
	protected function __construct(Base $Base)
	{
		$this->_Base = $Base;
	}

	/**
	 * A method that is executed (when available) before desired action is executed.
	 * @param string $lang
	 * @param string $action
	 * @param string $controller
	 * @return null|string
	 */
	public function before(string $lang, string $action, string $controller): ?string
	{
		if(!empty($lang))
		{
			if($this->_Base->locale()->languageExists($lang))
			{
				$this->_Base->locale()->setCurrentLocale($lang);
			}
		}

		return null;
	}

	/**
	 * A method that is executed (when available) after the desired action is executed.
	 * @param string $action
	 * @param string $controller
	 */
	public function after(string $action, string $controller): void
	{

	}
}
