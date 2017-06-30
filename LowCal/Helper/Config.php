<?php
declare(strict_types=1);
namespace LowCal\Helper;

/**
 * Class Config
 * A static class used to set and get global application configurations.
 * @package LowCal\Helper
 */
class Config
{
	/**
	 * All configuration variables will be saved into this array.
	 * @var array
	 */
	protected static $_configs = array();

	/**
	 * Load an array of configuration variables. Existing configurations will be overwritten if keys match.
	 * @param array $array_of_configs
	 */
	public static function loadArray(array $array_of_configs): void
	{
		self::$_configs = array_merge(self::$_configs, $array_of_configs);
	}

	/**
	 * Load a PHP file that contains configuration variables stored in a global LOWCAL_CONFIG_ARRAY variable.
	 * Existing configurations will be overwritten if keys match.
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
	 * Load a configuration file based on the current environment status (local, dev, preprod, prod).
	 * Existing configurations will be overwritten if keys match.
	 * @param string $lowcal_config_file
	 * @return bool
	 */
	public static function loadConfigForEnv(string $lowcal_config_file): bool
	{
		$lowcal_config_file = self::get('BASE_DIR').$lowcal_config_file.'_'.self::get('LOWCAL_ENV').'.php';

		try
		{
			self::loadFile($lowcal_config_file);
		}
		catch(\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Gets a configuration variable.
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
			throw new \Exception('Config "'.$config_key_name.'" does not exist!', Codes::INTERNAL_CONFIG_MISSING_KEY);
		}
	}
}