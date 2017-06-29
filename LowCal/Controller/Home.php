<?php
declare(strict_types=1);
namespace LowCal\Controller;

/**
 * Class Home
 * @package LowCal\Controller
 */
class Home extends Controller
{
	/**
	 * @return null|String
	 */
	public function indexAction(): ?String
	{
		return $this->_Base->view()->render('index');
	}
}
