<?php
declare(strict_types=1);
namespace LowCal\Helper;

/**
 * Class Strings
 * Static class that provides useful string manipulations or string related methods.
 * @package LowCal\Helper
 */
class Strings
{
	/**
	 * Flag for generating alphanumeric strings.
	 * @var int
	 */
	const ALPHANUMERIC = 0;

	/**
	 * Flag for generating alphanumeric plus strings.
	 * @var int
	 */
	const ALPHANUMERIC_PLUS = 1;

	/**
	 * Flag for generating hex compatible strings.
	 * @var int
	 */
	const HEX = 2;

	/**
	 * Advanced trim function that detects various hidden characters that show-up in certain strings from different languages.
	 * When possible, use this method over PHP's standard trim.
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
	 * Generate a cryptographically secure random string of characters.
	 * @param int $length
	 * @param int $type
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
	 * Tests string to see if it is unserializable.
	 * @param string $string
	 * @param array $options
	 * @return bool
	 */
	public static function unserializable(string $string, array $options = array()): bool
	{
		$string = self::trim($string);

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

		if(@unserialize($string, $options) === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Generates a random, pronounceable word.
	 * @param int $length
	 * @return string
	 */
	public static function randomPronounceableWord(int $length = 6): string
	{
		// consonant sounds
		$cons = array(
			// single consonants. Beware of Q, it's often awkward in words
			'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
			'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'z',
			// possible combinations excluding those which cannot start a word
			'pt', 'gl', 'gr', 'ch', 'ph', 'ps', 'sh', 'st', 'th', 'wh',
		);

		// consonant combinations that cannot start a word
		$cons_cant_start = array(
			'ck', 'cm',
			'dr', 'ds',
			'ft',
			'gh', 'gn',
			'kr', 'ks',
			'ls', 'lt', 'lr',
			'mp', 'mt', 'ms',
			'ng', 'ns',
			'rd', 'rg', 'rs', 'rt',
			'ss',
			'ts', 'tch',
		);

		// wovels
		$vows = array(
			// single vowels
			'a', 'e', 'i', 'o', 'u', 'y',
			// vowel combinations your language allows
			'ee', 'oa', 'oo',
		);

		// start by vowel or consonant ?
		$current = (random_int(0, 1) == '0'?'cons':'vows');

		$word = '';

		while(strlen($word) < $length)
		{
			// After first letter, use all consonant combos
			if(strlen($word) == 2)
			{
				$cons = array_merge($cons, $cons_cant_start);
			}

			// random sign from either $cons or $vows
			$rnd = ${$current}[ mt_rand( 0, count( ${$current} ) -1 ) ];

			// check if random sign fits in word length
			if(strlen($word.$rnd) <= $length)
			{
				$word .= $rnd;
				// alternate sounds
				$current = ($current=='cons'?'vows':'cons');
			}
		}

		return $word;
	}
}
