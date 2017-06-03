<?php
declare(strict_types=1);
namespace LowCal\Module;

/**
 * Class Arrays
 * @package LowCal\Module
 */
class Arrays
{
	/**
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
}
