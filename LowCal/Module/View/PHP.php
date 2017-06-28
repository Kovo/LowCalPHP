<?php
declare(strict_types=1);
namespace LowCal\Module\View;
use LowCal\Helper\Codes;
use LowCal\Interfaces\View;
use LowCal\Module\Module;

/**
 * Class PHP
 * @package LowCal\Module\View
 */
class PHP extends Module implements View
{
	/**
	 * @param string $view
	 * @param array $parameters
	 * @param string $view_dir
	 * @return String
	 * @throws \Exception
	 */
	public static function render(string $view, array $parameters = array(), string $view_dir = ''): String
	{
		$file = $view_dir.$view.'.php';

		if(file_exists($file))
		{
			if(!empty($parameters))
			{
				extract($parameters);
			}

			try
			{
				ob_start();

				require $file;

				$content = ob_get_clean();

				return $content;
			}
			catch(\Throwable $t)
			{
				ob_end_clean();

				throw new \Exception($t->getMessage(), $t->getCode());
			}
		}
		else
		{
			throw new \Exception('View "'.$file.'" not found!', Codes::VIEW_NOT_FOUND);
		}
	}
}