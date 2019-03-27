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
	 * @return string
	 */
	public static function trim(string $string): string
	{
		$string = str_replace("\xC2\xA0", ' ', $string);
		$string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

		return trim($string);
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

	/**
	 * @param string $string
	 * @param string $replacement
	 * @param int $start
	 * @param int $length
	 * @param null|string $encoding
	 * @return string
	 */
	public static function mb_substr_replace(string $string, string $replacement, int $start, int $length = 0, ?string $encoding = null): string
	{
		$result  = mb_substr($string, 0, $start, $encoding);
		$result .= $replacement;

		if($length > 0)
		{
			$result .= mb_substr($string, ($start+$length), null, $encoding);
		}

		return $result;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	public static function friendlyUrl(string $url): string
	{
		// everything to lower and no spaces begin or end
		$url = strtolower(trim($url));

		//replace accent characters, depends your language is needed
		$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
		$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
		$url = str_replace($a, $b, $url);

		// adding - for spaces and union characters
		$find = array(' ', '&', '\r\n', '\n', '+',',');
		$url = str_replace ($find, '-', $url);

		//delete and replace rest of special chars
		$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
		$repl = array('', '-', '');
		$url = preg_replace ($find, $repl, $url);

		//return the friendly url
		return $url;
	}
}