<?php
declare(strict_types=1);
namespace LowCal\Controller;
use LowCal\Base;
use LowCal\Helper\Codes;

/**
 * Class Controller
 * This is the "master" controller that you can choose to extend your application controllers from.
 * @package LowCal\Controller
 */
class Controller
{
	/**
	 * Base LowCal object is stored in the master controller.
	 * @var null|Base
	 */
	protected $_Base = null;

	/**
	 * Controller constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		$this->_Base = $Base;
	}

	/**
	 * A method that is executed (when available) before desired action is executed.
	 * @param string $lang
	 * @param string $action
	 * @throws \Exception
	 */
	public function before(string $lang, string $action): void
	{
		if(!empty($lang))
		{
			if($this->_Base->locale()->languageExists($lang))
			{
				$this->_Base->locale()->setCurrentLocale($lang);
			}
			else
			{
				throw new \Exception('Invalid language id given in the url! Id was: '.$lang, Codes::LOCALE_INVALID_LOCALE);
			}
		}
		elseif($this->_Base->locale()->languagesExist())
		{
			throw new \Exception('No language id given in the url! Id was: '.$lang, Codes::LOCALE_NO_LOCALE);
		}
	}

	/**
	 * A method that is executed (when available) after the desired action is executed.
	 * @param string $lang
	 * @param string $action
	 */
	public function after(string $lang, string $action): void
	{

	}
}
