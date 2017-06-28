<?php
declare(strict_types=1);
namespace LowCal\Helper;

/**
 * Class Strings
 * @package LowCal\Helper
 */
class Strings
{
	const ALPHANUMERIC = 0;
	const ALPHANUMERIC_PLUS = 1;
	const HEX = 2;

	/**
	 * @param string $string
	 * @param string $charlist
	 * @return string
	 */
	public static function trim(string $string, string $charlist = " \t\n\r\0\x0B\xC2\xA0"): string
	{
		$string = str_replace("\xC2\xA0", ' ', $string);
		$string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

		return trim($string, $charlist);
	}

	/**
	 * @param int $length
	 * @param int $type
	 * @param bool $regenerateSeed
	 * @return string
	 */
	public static function createCode(int $length, int $type = self::ALPHANUMERIC): string
	{
		if($type === self::ALPHANUMERIC)
		{
			$chars = "0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz";
		}
		elseif($type === self::ALPHANUMERIC_PLUS)
		{
			$chars = "0123456789AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz`~!@#$%^&*()_+|}{:?><,./;'[]-=";
		}
		else
		{
			$chars = "0123456789abcdef";
		}

		$amount_chars = strlen($chars);

		$pass = '';

		for($i=0;$i<$length;$i++)
		{
			$num = random_int(0,PHP_INT_MAX)%$amount_chars;
			$tmp = substr($chars, $num, 1);
			$pass = $pass.$tmp;
		}

		return $pass;
	}

	/**
	 * @param $string
	 * @return bool
	 */
	public static function unserializable(string $string): bool
	{
		if(!is_string($string))
		{
			return false;
		}

		$string = trim($string);

		if($string === '')
		{
			return false;
		}

		if($string === 'b:0;')
		{
			return true;
		}

		$length	= strlen($string);
		$end = '';

		switch($string[0])
		{
			case 's':
				if($string[$length - 2] !== '"')
				{
					return false;
				}
			case 'b':
			case 'i':
			case 'd':
				$end .= ';';
			case 'a':
			case 'O':
				$end .= '}';

				if($string[1] !== ':')
				{
					return false;
				}

				switch($string[2])
				{
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
						break;
					default:
						return false;
				}
			case 'N':
				$end .= ';';
				if($string[$length - 1] !== $end[0])
				{
					return false;
				}

				break;
			default:
				return false;
		}

		if(@unserialize($string) === false)
		{
			return false;
		}

		return true;
	}
}
