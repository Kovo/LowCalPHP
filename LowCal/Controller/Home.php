<?php
declare(strict_types=1);

namespace LowCal\Controller;

use LowCal\Base;

/**
 * Class Home
 * A sample controller.
 * @package LowCal\Controller
 */
class Home extends Controller
{
	/**
	 * Home constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);
	}

	/**
	 * Sample index action.
	 * @return null|String
	 */
	public function indexAction(): ?String
	{
		return $this->_Base->view()->render('index');
	}
}
