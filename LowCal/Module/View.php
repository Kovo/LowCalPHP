<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Module;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Module\View\PHP;

/**
 * Class View
 * This is the main View module class. It handles registering and interacting with different templating engines.
 * @package LowCal\Module
 */
class View extends Module
{
	/**
	 * The view engine type.
	 * @var null|int
	 */
	protected $_engine_type = null;

	/**
	 * The view engine object.
	 * @var null|PHP
	 */
	protected $_engine_object = null;

	/**
	 * View constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);
	}

	/**
	 * Set the view engine type.
	 * @param int $view_engine_id
	 * @return View
	 */
	public function setViewEngineType(int $view_engine_id): View
	{
		$this->_engine_type = $view_engine_id;

		return $this;
	}

	/**
	 * Get the view engine type.
	 * @return int
	 */
	public function getViewEngineType(): int
	{
		return $this->_engine_type;
	}

	/**
	 * Set the view engine object.
	 * @param PHP $ViewEngine
	 * @return View
	 */
	public function setViewEngineObject($ViewEngine): View
	{
		$this->_engine_object = $ViewEngine;

		return $this;
	}

	/**
	 * Get the view engine object.
	 * @return PHP|null
	 */
	public function getViewEngineObject()
	{
		return $this->_engine_object;
	}

	/**
	 * Abstracted render method.
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