<?php
declare(strict_types=1);

namespace LowCal\Helper;

/**
 * Class Format
 * A helper class used for JSON document content for NoSQL databases.
 * @package LowCal\Helper
 */
class Format
{
	/**
	 * Get a properly formatted timestamp.
	 * @param int|null $starting_point
	 * @return string
	 */
	public static function getTimestamp(?int $starting_point = null): ?string
	{
		if(!empty($starting_point))
		{
			$final_format = date('c', $starting_point);
		}
		else
		{
			$final_format = date('c');
		}

		return (!$final_format?null:$final_format);
	}

	/**
	 * A type-safe implode method that prepares data in an array for a comma separated
	 * list in a query.
	 * @param array $value
	 * @param string $delimiter
	 * @return string
	 * @throws \Exception
	 */
	public static function typeSafeImplodeForQuery(array $value, string $delimiter = '"'): string
	{
		$return = '';

		if(!empty($value))
		{
			foreach($value as $val)
			{
				if(is_bool($val))
				{
					$return .= ($val||$val==='true'?'true':'false').',';
				}
				elseif(is_numeric($val))
				{
					$return .= $val.',';
				}
				elseif(is_array($val))
				{
					$return .= self::typeSafeImplodeForQuery($val, $delimiter).',';
				}
				else
				{
					$return .= $delimiter.$val.$delimiter.',';
				}
			}
		}

		return substr($return,0,-1);
	}

	/**
	 * A type-safe method that prepares a value for concatenation in a query.
	 * @param $value
	 * @param null|string $array_key
	 * @param bool $json_encode_arrays
	 * @param bool $array_values_arrays
	 * @return array|mixed|string
	 */
	public static function typeSafeJSONValueForQuery($value, ?string $array_key = null, bool $json_encode_arrays = true, bool $array_values_arrays = true)
	{
		global $LowCal;

		if(is_array($value))
		{
			if(empty($array_key))
			{
				$value = $LowCal->db()->sanitizeQueryValueTypeSafe($array_values_arrays?array_values($value):$value);
			}
			else
			{
				$value[$array_key] = $LowCal->db()->sanitizeQueryValueTypeSafe($array_values_arrays?array_values($value[$array_key]):$value[$array_key]);
			}

			return $json_encode_arrays?json_encode($value):$value;
		}
		elseif(is_numeric($value) && $value !== true && $value !== false)
		{
			return $LowCal->db()->sanitizeQueryValueNumeric($value);
		}
		elseif(is_bool($value))
		{
			return $value||$value==='true'?'true':'false';
		}
		else
		{
			return "\"".$LowCal->db()->sanitizeQueryValueNonNumeric($value)."\"";
		}
	}

	/**
	 * Return a prefixed id used as a document key in a NoSQL database.
	 * @param int $type
	 * @param array $additional_values
	 * @return string
	 */
	public static function getPrefixedId(int $type, array $additional_values): string
	{
		return $type.":".implode(":", $additional_values);
	}
}