<?php
declare(strict_types=1);
namespace LowCal\Module\View;
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
	 * Set the base view directory.
	 * @param string $dir
	 * @return View
	 */
	public function setViewDir(string $dir): View
	{
		$this->_view_dir = $dir;

		return $this;
	}

	/**
	 * Get the base view directory.
	 * @return string
	 */
	public function getViewDir(): string
	{
		return $this->_view_dir;
	}
}