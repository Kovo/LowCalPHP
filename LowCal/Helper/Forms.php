<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Helper;

/**
 * Class Forms
 * @package LowCal\Helper
 */
class Forms
{
	/**
	 * @return string
	 * @throws \Exception
	 */
	public static function getTimeBasedInput(): string
	{
		return '<input type="hidden" name="'.Config::get('APP_FORMS_INPUT_NAME').'" value="'.self::getTimeBasedHash().'" />';
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public static function getTimeBasedHash(): string
	{
		global $LowCal;

		return $LowCal->security()->twoWayEncrypt(Config::get('APP_FORMS_TIME_BASED_DELAY_SECONDS').'_'.time());
	}

	/**
	 * @param string $hash
	 * @return bool
	 * @throws \Exception
	 */
	public static function timeBasedThresholdPassed(string $hash): bool
	{
		global $LowCal;

		if(empty($hash))
		{
			return false;
		}

		$decrypted = $LowCal->security()->twoWayDecrypt($hash);

		$ex = explode('_', $decrypted);

		if(count($ex) < 2)
		{
			return false;
		}

		if(time()-(int)$ex[1] >= Config::get('APP_FORMS_TIME_BASED_DELAY_SECONDS'))
		{
			return true;
		}

		return false;
	}
}