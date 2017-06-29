<?php
declare(strict_types=1);
namespace LowCal;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Module\Cache;
use LowCal\Module\Db;
use LowCal\Module\Locale;
use LowCal\Module\Log;
use LowCal\Module\Request;
use LowCal\Module\Response;
use LowCal\Module\Routing;
use LowCal\Module\Security;
use LowCal\Module\View;

/**
 * Class Base
 * The Base LowCal class. Most LowCal functionality begins here. Your application should always start with creating a new
 * LowCal Base object. Your application should never have more than one Base object instantiated at a time, but the possibility
 * is left open for you.
 * @package LowCal
 */
class Base
{
	/**
	 * The Security module object is stored here.
	 * @var null|Security
	 */
	protected $_Security = null;

	/**
	 * The View module object is stored here.
	 * @var null|View
	 */
	protected $_View = null;

	/**
	 * The Cache module object is stored here.
	 * @var null|Cache
	 */
	protected $_Cache = null;

	/**
	 * The Db module object is stored here.
	 * @var null|Db
	 */
	protected $_Db = null;

	/**
	 * The Locale module object is stored here.
	 * @var null|Locale
	 */
	protected $_Locale = null;

	/**
	 * The Log module object is stored here.
	 * @var null|Log
	 */
	protected $_Log = null;

	/**
	 * The Request module object is stored here.
	 * @var null|Request
	 */
	protected $_Request = null;

	/**
	 * The Response module object is stored here.
	 * @var null|Response
	 */
	protected $_Response = null;

	/**
	 * The Rotuing module object is stored here.
	 * @var null|Routing
	 */
	protected $_Routing = null;

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
						$this->view()->render($solution['value']);
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
	 * This method compresses HTML output to save on bandwidth and decrease browser load times.
	 * You can also define areas of your HTML that should not be compressed. Your HTML will require a
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
				if(stristr($buffer[$i], '<!--compress-html no-compression-->'))
				{
					$buffer[$i] = str_replace("<!--compress-html no-compression-->", " ", $buffer[$i]);
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
	 * Returns an instantiated Cache module object.
	 * @return Cache
	 */
	public function cache(): Cache
	{
		if($this->_Cache === null)
		{
			$this->_Cache = new Cache($this);
		}

		return $this->_Cache;
	}

	/**
	 * Returns an instantiated Db module object.
	 * @return Db
	 */
	public function db(): Db
	{
		if($this->_Db === null)
		{
			$this->_Db = new Db($this);
		}

		return $this->_Db;
	}

	/**
	 * Returns an instantiated Locale module object.
	 * @return Locale
	 */
	public function locale(): Locale
	{
		if($this->_Locale === null)
		{
			$this->_Locale = new Locale($this);
		}

		return $this->_Locale;
	}

	/**
	 * Returns an instantiated Log module object.
	 * @return Log
	 */
	public function log(): Log
	{
		if($this->_Log === null)
		{
			$this->_Log = new Log($this);
		}

		return $this->_Log;
	}

	/**
	 * Returns an instantiated Request module object.
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

	/**
	 * Returns an instantiated Response module object.
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
	 * Returns an instantiated Routing module object.
	 * @return Routing
	 */
	public function routing(): Routing
	{
		if($this->_Routing === null)
		{
			$this->_Routing = new Routing($this);
		}

		return $this->_Routing;
	}

	/**
	 * Returns an instantiated Security module object.
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
	 * Returns an instantiated View module object.
	 * @return View
	 */
	public function view(): View
	{
		if($this->_View === null)
		{
			$this->_View = new View($this);
		}

		return $this->_View;
	}
}