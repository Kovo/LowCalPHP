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
	 * Load a PHP or INI file that contains configuration variables stored in a global LOWCAL_CONFIG_ARRAY variable (for PHP files).
	 * Existing configurations will be overwritten if keys match.
	 * @param string $file_path
	 * @throws \Exception
	 */
	public static function loadFile(string $file_path): void
	{
		if(file_exists($file_path))
		{
			if(substr($file_path, -4) === '.php')
			{
				require $file_path;

				if(isset($LOWCAL_CONFIG_ARRAY) && is_array($LOWCAL_CONFIG_ARRAY) && !empty($LOWCAL_CONFIG_ARRAY))
				{
					self::loadArray($LOWCAL_CONFIG_ARRAY);
				}
			}
			elseif(substr($file_path, -4) === '.ini')
			{
				$ini = parse_ini_file($file_path, true, INI_SCANNER_TYPED);

				if(!empty($ini))
				{
					self::loadArray($ini);
				}
			}
			else
			{
				throw new \Exception('Unsupported config file "'.$file_path.'" provided!', Codes::INTERNAL_CONFIG_UNSUPPORTED_FILE);
			}
		}
		else
		{
			throw new \Exception('Config file "'.$file_path.'" does not exist!', Codes::INTERNAL_CONFIG_FILE_NOT_FOUND);
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
	 * Gets a configuration variable. The key name can be a composite to define a multi-dimensional array.
	 * Like the Locale module, you can also replace values in the config value using the '%%' syntax.
	 * @param string $config_key_name
	 * @param array $replacements
	 * @return mixed
	 * @throws \Exception
	 */
	public static function get(string $config_key_name, array $replacements = array())
	{
		$explode = explode('.', $config_key_name);

		$value_to_return = self::$_configs;
		foreach($explode as $key_name)
		{
			if(is_array($value_to_return) && array_key_exists($key_name, $value_to_return))
			{
				$value_to_return = $value_to_return[$key_name];
			}
			else
			{
				throw new \Exception('Config "'.$key_name.'" does not exist!', Codes::INTERNAL_CONFIG_MISSING_KEY);
			}
		}

		if(!empty($replacements) && is_string($value_to_return))
		{
			foreach($replacements as $key => $value)
			{
				$value_to_return = str_replace('%'.$key.'%', $value, $value_to_return);
			}
		}

		return $value_to_return;
	}

	/**
	 * Sets a configuration variable. The key name can be a composite to define a multi-dimensional array.
	 * @param string $config_key_name
	 * @return bool
	 * @throws \Exception
	 */
	public static function set(string $config_key_name, $config_key_value): bool
	{
		return Arrays::setValueMulti(self::$_configs, explode('.', $config_key_name), $config_key_value);
	}

	/**
	 * Simple wrapper method to easily change php ini config values.
	 * @param string $config_name
	 * @param $config_value
	 * @return bool
	 */
	public static function changePHPConfig(string $config_name, $config_value): bool
	{
		return (ini_set($config_name, $config_value)?true:false);
	}

	/**
	 * A thread-safe method of changing or adding configuration values to existing confio files.
	 * @param string $config_file_path
	 * @param string $config_key
	 * @param $value
	 * @return bool
	 * @throws \Exception
	 */
	public static function setInFile(string $config_file_path, string $config_key, string $variable_name, $value): bool
	{
		if(substr($config_file_path, -4) === '.php')
		{
			return self::_setConfigInPHPFile($config_file_path, $config_key, $variable_name, $value);
		}
		elseif(substr($config_file_path, -4) === '.ini')
		{
			return self::_setConfigInINIFile($config_file_path, $config_key, $variable_name, $value);
		}
		else
		{
			throw new \Exception('Unsupported config file "'.$config_file_path.'" provided!', Codes::INTERNAL_CONFIG_UNSUPPORTED_FILE);
		}
	}

	public static function _setConfigInPHPFile(string $config_file_path, string $config_key, string $variable_name, $value): bool
	{
		if(IO::isValidFile($config_file_path))
		{
			$lock_file = __DIR__.DIRECTORY_SEPARATOR.md5($config_file_path).'lock';

			if(IO::isValidFile($lock_file) || file_put_contents($lock_file, '') === false)
			{
				throw new \Exception('Cannot set lock for config file "'.$config_file_path.'". Another program may already be modifying it.', Codes::INTERNAL_CONFIG_FILE_CANNOT_LOCK);
			}

			switch(gettype($value))
			{
				case 'string':
					$final_value = "'".str_replace("'", "\\'", $value)."'";
					break;
				case 'integer':
				case 'double':
				case 'boolean':
					$final_value = $value;
					break;
				case 'array':
					$final_value = var_export($value, true);
					break;
				default:
					IO::removeFileFolderEnforce($lock_file);

					throw new \Exception('Unsupported configuration value "'.gettype($value).'" provided.', Codes::INTERNAL_CONFIG_UNSUPPORTED_VALUE_TYPE);
			}

			$lines = file($config_file_path);

			if(!empty($lines))
			{
				file_put_contents($config_file_path, '');

				foreach($lines as $line_number => $line_value)
				{

				}
			}
			else
			{
				file_put_contents($config_file_path,
					"<?php\r\n".
					"$".$variable_name."['".$config_key."'] = ".$final_value.";\r\n"
				);
			}

			IO::removeFileFolderEnforce($lock_file);

			return true;
		}
		else
		{
			throw new \Exception('Cannot find config file "'.$config_file_path.'".', Codes::INTERNAL_CONFIG_FILE_NOT_FOUND);
		}
	}

	public static function _setConfigInINIFile(string $config_file_path, string $config_key, string $variable_name, $value): bool
	{

	}
}