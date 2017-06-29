<?php
declare(strict_types=1);
namespace LowCal\Interfaces;

/**
 * Interface View
 * @package LowCal\Interfaces
 */
interface View
{
	/**
	 * @param string $view
	 * @param array $parameters
	 * @return String
	 */
	public function render(string $view, array $parameters = array()): String;

	/**
	 * @param string $dir
	 * @return View
	 */
	public function setViewDir(string $dir): View;

	/**
	 * @return string
	 */
	public function getViewDir(): string;
}