<?php
declare(strict_types=1);
namespace LowCal;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Module\Request;
use LowCal\Module\Response;
use LowCal\Module\Security;

/**
 * Class Base
 * @package LowCal
 */
class Base
{
	/**
	 * @var null|Response
	 */
	protected $_Response = null;

	/**
	 * @var null|Security
	 */
	protected $_Security = null;

	/**
	 * @var null|Request
	 */
	protected $_Request = null;

	/**
	 * Base constructor.
	 * @throws \Exception
	 */
	function __construct()
	{
		if(Config::get('DOMAIN_PROTECTION'))
		{
			try
			{
				$this->security()->domainCheck();
			}
			catch(\Throwable $t)
			{
				if($t->getCode() === Codes::SECURITY_EXCEPTION_DOMAINCHECK)
				{
					$solution = Config::get('DOMAIN_SOLUTION');

					if($solution['type'] === 'redirect')
					{
						$this->response()->redirect($solution['value']);
					}
					else
					{
						//$this->view()->render($solution['value']);
					}
				}
				else
				{
					throw new \Exception($t->getMessage(), $t->getCode());
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
	 * @param string $buffer
	 * @return string
	 */
	public function compressOutput(string $buffer): string
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
	 * @return Response
	 */
	public function response(): Response
	{
		if($this->_Response === null)
		{
			$this->_Response = new Response($this);
		}

		return $this->_Response;
	}

	/**
	 * @return Security
	 */
	public function security(): Security
	{
		if($this->_Security === null)
		{
			$this->_Security = new Security($this);
		}

		return $this->_Security;
	}

	/**
	 * @return Request
	 */
	public function request(): Request
	{
		if($this->_Request === null)
		{
			$this->_Request = new Request($this);
		}

		return $this->_Request;
	}
}