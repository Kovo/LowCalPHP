<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;

/** 
 * Class View
 * @package LowCal\Module
 */
class View extends Module
{
	/**
	 * @param string $view
	 * @param array $parameters
	 * @param string|null $override_view_dir
	 * @return string
	 * @throws \Exception
	 */
	public function render(string $view, array $parameters = array(), string $override_view_dir = null): string
	{
		$file = (empty($override_view_dir)?Config::get('VIEWS_DIR'):$override_view_dir).$view.'.php';

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