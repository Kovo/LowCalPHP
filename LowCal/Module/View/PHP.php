<?php
declare(strict_types=1);
namespace LowCal\Module\View;
use LowCal\Helper\Codes;
use LowCal\Interfaces\View;

/**
 * Class PHP
 * @package LowCal\Module\View
 */
class PHP extends \LowCal\Module\View\View implements View
{
	/**
	 * @param string $view
	 * @param array $parameters
	 * @return String
	 * @throws \Exception
	 */
	public function render(string $view, array $parameters = array()): String
	{
		$file = $this->_view_dir.$view.'.php';

		if(file_exists($file))
		{
			$parameters['LowCal'] = $this->_Base;

			extract($parameters);

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