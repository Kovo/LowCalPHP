<?php
declare(strict_types=1);
namespace LowCal\Module\View;
use LowCal\Module\Module;

/**
 * Class View
 * @package LowCal\Module\View
 */
class View extends Module
{
	/**
	 * @var string
	 */
	protected $_view_dir = '';

	/**
	 * @param string $dir
	 * @return View
	 */
	public function setViewDir(string $dir): View
	{
		$this->_view_dir = $dir;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getViewDir(): string
	{
		return $this->_view_dir;
	}
}