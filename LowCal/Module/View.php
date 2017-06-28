<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Module\View\PHP;

/**
 * Class View
 * @package LowCal\Module
 */
class View extends Module
{
	/**
	 * @var string
	 */
	protected $_view_dir = '';

	/**
	 * @var null|int
	 */
	protected $_engine_type = null;

	/**
	 * View constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);

		$this->_view_dir = Config::get('VIEWS_DIR');
		$this->_engine_type = Config::get('VIEW_ACTIVE_ENGINE');
	}

	/**
	 * @param int $view_engine_id
	 * @return View
	 */
	public function setViewEngine(int $view_engine_id): View
	{
		$this->_engine_type = $view_engine_id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getViewEngine(): int
	{
		return $this->_engine_type;
	}

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

	/**
	 * @param string $view
	 * @param array $parameters
	 * @param string|null $override_view_dir
	 * @return string
	 * @throws \Exception
	 */
	public function render(string $view, array $parameters = array(), string $override_view_dir = null): string
	{
		switch($this->_engine_type)
		{
			case Config::get('VIEW_ENGINE_PHP'):
				return PHP::render($view, $parameters, $override_view_dir);
			default:
				throw new \Exception('Invalid view engine provided ('.$this->_engine_type.').', Codes::VIEW_INVALID_ENGINE);
		}
	}
}