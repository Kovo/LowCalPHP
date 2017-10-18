<?php
declare(strict_types=1);

namespace LowCal\Interfaces\Module;

/**
 * Interface View
 * Interface for all view engines used in LowCal's module architecture.
 * @package LowCal\Interfaces\Module
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
	 * @return mixed
	 */
	public function setViewDir(string $dir);

	/**
	 * @return string
	 */
	public function getViewDir(): string;
}