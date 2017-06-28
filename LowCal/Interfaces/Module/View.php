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
	 * @param string $view_dir
	 * @return String
	 */
	public static function render(string $view, array $parameters = array(), string $view_dir = ''): String;
}