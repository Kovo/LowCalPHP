<?php
declare(strict_types=1);
namespace LowCal\Module;
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
	 * @var null|int
	 */
	protected $_engine_type = null;

	/**
	 * @var null|PHP
	 */
	protected $_engine_object = null;

	/**
	 * @param int $view_engine_id
	 * @return View
	 */
	public function setViewEngineType(int $view_engine_id): View
	{
		$this->_engine_type = $view_engine_id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getViewEngineType(): int
	{
		return $this->_engine_type;
	}

	/**
	 * @param PHP $ViewEngine
	 * @return View
	 */
	public function setViewEngineObject($ViewEngine): View
	{
		$this->_engine_object = $ViewEngine;

		return $this;
	}

	/**
	 * @return PHP|null
	 */
	public function getViewEngineObject()
	{
		return $this->_engine_object;
	}

	/**
	 * @param string $view
	 * @param array $parameters
	 * @return string
	 * @throws \Exception
	 */
	public function render(string $view, array $parameters = array()): string
	{
		if(empty($this->_engine_object))
		{
			throw new \Exception('No View Engine object has been set.', Codes::VIEW_ENGINE_NOT_STARTED);
		}

		switch($this->_engine_type)
		{
			case Config::get('VIEW_ENGINE_PHP'):
				return $this->_engine_object->render($view, $parameters);
			default:
				throw new \Exception('Invalid view engine provided ('.$this->_engine_type.').', Codes::VIEW_INVALID_ENGINE);
		}
	}
}