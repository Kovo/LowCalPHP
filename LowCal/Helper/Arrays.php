<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Helper;

/**
 * Class Arrays
 * A static class that offers some useful array functions not built-in to PHP.
 * @package LowCal\Helper
 */
class Arrays
{
	/**
	 * This method will sort an associative array by a specified key.
	 * @param array $source_array
	 * @param string $key_to_sort_by
	 * @param int $sort_type
	 * @param bool $sort_ascending
	 */
	public static function aasort(array &$source_array, string $key_to_sort_by, int $sort_type = SORT_REGULAR, bool $sort_ascending = true): void
	{
		$temporaryArray = array();
		$replacementArray = array();

		reset($source_array);

		foreach($source_array as $index => $value)
		{
			$temporaryArray[$index] = $value[$key_to_sort_by];
		}

		if($sort_ascending)
		{
			asort($temporaryArray, $sort_type);
		}
		else
		{
			arsort($temporaryArray, $sort_type);
		}

		foreach($temporaryArray as $index => $value)
		{
			$replacementArray[$index] = $source_array[$index];
		}

		$source_array = $replacementArray;
	}

	/**
	 * This method will insert a value into a particular position of an array.
	 * @param array $array
	 * @param int $pos
	 * @param $value
	 * @return array
	 */
	public static function insertValueAtPos(array $array, int $pos, $value): array
	{
		return array_slice($array, 0, $pos, true)
			+$value
			+array_slice($array, $pos, count($array)-$pos, true);
	}

	/**
	 * A recursive method to change values deep inside mutli-dimensional arrays (or create them if non-existent).
	 * @param array $target_array
	 * @param array $target_keys
	 * @param $target_value
	 * @return bool
	 */
	public static function setValueMulti(array &$target_array, array $target_keys, $target_value): bool
	{
		$next_key = array_shift($target_keys);
		$count = count($target_keys);

		foreach($target_array as $key => $value)
		{
			if($key == $next_key && $count === 0)
			{
				$target_array[$key] = $target_value;

				return true;
			}
			elseif($key == $next_key && $count > 0 && is_array($value))
			{
				return self::setValueMulti($target_array[$key], $target_keys, $target_value);
			}
			elseif($key == $next_key && $count > 0)
			{
				return false;
			}
			else
			{
				continue;
			}
		}

		$target_array[$next_key] = $target_value;

		return true;
	}

	/**
	 * Remove keys in target array that are not in allowed_keys variable.
	 * @param array $array
	 * @param array $allowed_keys
	 */
	public static function enforceKeys(array &$array, array $allowed_keys): void
	{
		if(!empty($array))
		{
			foreach($array as $key => $value)
			{
				if(!in_array($key, $allowed_keys))
				{
					unset($array[$key]);
				}
			}
		}
	}

	/**
	 * Sum values of array using bcmath methods to support larger numbers and high precision.
	 * @param array $input_array
	 * @param int $precision
	 * @return string
	 */
	public static function bcArraySum(array &$input_array, int $precision = 0): string
	{
		$return = null;

		if(empty($input_array))
		{
			return '0';
		}

		foreach($input_array as $value)
		{
			if($return === null)
			{
				$return = (string)$value;
			}
			else
			{
				$return = bcadd($return, (string)$value, $precision);
			}
		}

		return $return;
	}

	/**
	 * Subtract values of array using first value as starting value, using bcmath methods to support larger numbers and high precision.
	 * @param array $input_array
	 * @param int $precision
	 * @return string
	 */
	public static function bcArraySub(array &$input_array, int $precision = 0): string
	{
		$return = null;

		if(empty($input_array))
		{
			return '0';
		}

		foreach($input_array as $value)
		{
			if($return === null)
			{
				$return = (string)$value;
			}
			else
			{
				$return = bcsub($return, (string)$value, $precision);
			}
		}

		return $return;
	}

	/**
	 * Multiply values of array using bcmath methods to support larger numbers and high precision.
	 * @param array $input_array
	 * @param int $precision
	 * @return string
	 */
	public static function bcArrayMul(array &$input_array, int $precision = 0): string
	{
		$return = null;

		if(empty($input_array))
		{
			return '0';
		}

		foreach($input_array as $value)
		{
			if($return === null)
			{
				$return = (string)$value;
			}
			else
			{
				$return = bcmul($return, (string)$value, $precision);
			}
		}

		return $return;
	}

	/**
	 * Divide values of array using bcmath methods to support larger numbers and high precision.
	 * @param array $input_array
	 * @param int $precision
	 * @return string
	 */
	public static function bcArrayDiv(array &$input_array, int $precision = 0): string
	{
		$return = null;

		if(empty($input_array))
		{
			return '0';
		}

		foreach($input_array as $value)
		{
			if($return === null)
			{
				$return = (string)$value;
			}
			else
			{
				$return = bcdiv($return, (string)$value, $precision);
			}
		}

		return $return;
	}

	/**
	 * array_slice but preserve keys
	 * @param $input
	 * @param $offset
	 * @param $length
	 * @param array $replacement
	 */
	public static function array_splice_assoc(&$input, $offset, $length, $replacement = array())
	{
		$replacement = (array)$replacement;
		$key_indices = array_flip(array_keys($input));

		if(isset($input[$offset]) && is_string($offset))
		{
			$offset = $key_indices[$offset];
		}

		if(isset($input[$length]) && is_string($length))
		{
			$length = $key_indices[$length] - $offset;
		}

		$input = array_slice($input, 0, $offset, true)
			+ $replacement
			+ array_slice($input, $offset + $length, null, true);
	}
}
