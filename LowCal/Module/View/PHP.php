<?php
declare(strict_types=1);

namespace LowCal\Module\View;

use LowCal\Helper\Codes;

/**
 * Class PHP
 * This is the class for the PHP view engine.
 * @package LowCal\Module\View
 */
class PHP extends \LowCal\Module\View\View implements \LowCal\Interfaces\Module\View
{
	/**
	 * Set the base view directory.
	 * @param string $dir
	 * @return PHP
	 */
	public function setViewDir(string $dir): PHP
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
	/**
	 * This method will render the supplied view and expose any parameters to it.
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