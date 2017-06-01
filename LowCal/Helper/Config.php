<?php
declare(strict_types=1);
namespace LowCal\Helper;
/**
 * Class Config
 * @package LowCal\Helper
 */
class Config
{
	/**
	 * @var array
	 */
	protected static $_configs = array();

	/**
	 * @param array $array_of_configs
	 */
	public static function loadArray(array $array_of_configs): void
	{
		self::$_configs = array_merge(self::$_configs, $array_of_configs);
	}

	/**
	 * @param string $file_path
	 * @throws \Exception
	 */
	public static function loadFile(string $file_path): void
	{
		if(file_exists($file_path))
		{
			require $file_path;

			if(isset($LOWCAL_CONFIG_ARRAY) && is_array($LOWCAL_CONFIG_ARRAY) && !empty($LOWCAL_CONFIG_ARRAY))
			{
				self::loadArray($LOWCAL_CONFIG_ARRAY);
			}
		}
		else
		{
			throw new \Exception('Config file "'.$file_path.'" does not exist!');
		}
	}

	/**
	 * @param string $lowcal_config_file
	 */
	public static function loadConfig(string $lowcal_config_file): void
	{
		$lowcal_config_file = self::get('BASE_DIR').$lowcal_config_file.'_'.self::get('LOWCAL_ENV').'.php';

		self::loadFile($lowcal_config_file);
	}

	/**
	 * @param string $config_key_name
	 * @return mixed
	 * @throws \Exception
	 */
	public static function get(string $config_key_name)
	{
		if(array_key_exists($config_key_name, self::$_configs))
		{
			return self::$_configs[$config_key_name];
		}
		else
		{
			throw new \Exception('Config "'.$config_key_name.'" does not exist!');
		}
	}
}