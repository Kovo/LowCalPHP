<?php
declare(strict_types=1);
namespace LowCal\Controller;

/**
 * Class Home
 * A sample controller.
 * @package LowCal\Controller
 */
class Home extends Controller
{
	/**
	 * Sample index action.
	 * @return null|String
	 */
	public function indexAction(): ?String
	{
		return $this->_Base->view()->render('index');
	}
}
