<?php
declare(strict_types=1);
namespace LowCal;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;

/**
 * Class Base
 * @package LowCal
 */
class Base
{
	/**
	 * @var array
	 */
	protected $_registeredModules = array();

	function __construct()
	{
		if(Config::get('DOMAIN_PROTECTION'))
		{
			try
			{
				//$this->security()->domainCheck();
			}
			catch(\Exception $e)
			{
				$solution = Config::get('DOMAIN_SOLUTION');

				if($solution['type'] === 'redirect')
				{
					//$this->response()->redirect($solution['value']);
				}
				else
				{
					//$this->view()->render($solution['value']);
				}
			}
		}

		if(Config::get('OUTPUT_COMPRESSION'))
		{
			ob_start(array($this, 'compressOutput'));
		}
		elseif(Config::get('OUTPUT_BUFFERING'))
		{
			ob_start();
		}
	}

	/**
	 * @param $buffer
	 * @return string
	 */
	public function compressOutput($buffer): string
	{
		$buffer = explode("<!--compress-html-->", $buffer);
		$count = count($buffer);
		$buffer_out = '';

		for($i =0;$i<=$count;$i++)
		{
			if(isset($buffer[$i]))
			{
				if(stristr($buffer[$i], '<!--compress-html no compression-->'))
				{
					$buffer[$i] = str_replace("<!--compress-html no compression-->", " ", $buffer[$i]);
				}
				else
				{
					$buffer[$i] = str_replace(array("\t","\n\n","\n","\r"), array(" ","\n","",""), $buffer[$i]);

					while(stristr($buffer[$i], '  '))
					{
						$buffer[$i] = str_replace("  ", " ", $buffer[$i]);
					}
				}

				$buffer_out .= $buffer[$i];

				$buffer[$i] = null;
				unset($buffer[$i]);
			}
		}

		$buffer = null;
		unset($buffer);

		return $buffer_out;
	}

	/**
	 * @param string $module_name
	 * @return Base
	 */
	public function registerModule(string $module_name): Base
	{
		if(!isset($this->_registeredModules[$module_name]))
		{
			$this->_registeredModules[$module_name] = false;
		}

		return $this;
	}

	/**
	 * @param string $module_name
	 * @return mixed
	 * @throws \Exception
	 */
	public function module(string $module_name)
	{
		if(isset($this->_registeredModules[$module_name]))
		{
			if($this->_registeredModules[$module_name] === false)
			{
				$this->_registeredModules[$module_name] = new $module_name();

				if(method_exists($this->_registeredModules[$module_name], 'init'))
				{
					$this->_registeredModules[$module_name]->init($this);
				}
			}

			return $this->_registeredModules[$module_name];
		}
		else
		{
			throw new \Exception($module_name.' module not found!', Codes::EXCEPTION_MISSING_CLASS);
		}
	}
}