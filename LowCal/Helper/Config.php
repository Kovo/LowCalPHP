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
	 * @param string $dir
	 * @throws \Exception
	 */
	public static function loadFile(string $file_path, string $dir = ''): void
	{
		$file_path = (!empty($dir)?$dir:self::get('BASE_DIR')).$file_path;

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
	 * @param string $dir
	 * @return bool
	 */
	public static function loadConfigForEnv(string $lowcal_config_file, string $dir = ''): bool
	{
		$explode = explode('.', $lowcal_config_file);
		$ext = array_pop($explode);
		$lowcal_config_file = implode('.', $explode).'_'.self::get('LOWCAL_ENV').$ext;

		try
		{
			self::loadFile($lowcal_config_file, $dir);
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
	 * @param string $variable_name
	 * @return bool
	 * @throws \Exception
	 */
	public static function setInFile(string $config_file_path, string $config_key, $value, string $variable_name = ''): bool
	{
		if(substr($config_file_path, -4) === '.php')
		{
			return self::_setConfigInPHPFile($config_file_path, $config_key, $variable_name, $value);
		}
		elseif(substr($config_file_path, -4) === '.ini')
		{
			return self::_setConfigInINIFile($config_file_path, $config_key, $value);
		}
		else
		{
			throw new \Exception('Unsupported config file "'.$config_file_path.'" provided!', Codes::INTERNAL_CONFIG_UNSUPPORTED_FILE);
		}
	}

	/**
	 * A thread-safe method to set or change a config value in a PHP config file.
	 * @param string $config_file_path
	 * @param string $config_key
	 * @param string $variable_name
	 * @param $value
	 * @return bool
	 * @throws \Exception
	 */
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
					$final_value = "'".str_replace("'", "\\'", Strings::trim($value))."';";
					break;
				case 'integer':
				case 'double':
				case 'boolean':
					$final_value = $value.";";
					break;
				case 'array':
					$final_value = Strings::trim(var_export($value, true)).";";
					break;
				default:
					IO::removeFileFolderEnforce($lock_file);

					throw new \Exception('Unsupported configuration value "'.gettype($value).'" provided.', Codes::INTERNAL_CONFIG_UNSUPPORTED_VALUE_TYPE);
			}

			$lines = file($config_file_path);

			if(!empty($lines))
			{
				$temp_file_path = $config_file_path.'.tmp.php';

				file_put_contents($temp_file_path, '');

				$multiline = false;

				foreach($lines as $line_number => $line_value)
				{
					$line_value = Strings::trim($line_value);

					if($multiline)
					{
						if(substr($line_value, -1) === ';')
						{
							$line_value = "$".$variable_name."['".$config_key."'] = ".$final_value;

							$multiline = false;
						}
						else
						{
							continue;
						}
					}
					elseif(strpos($line_value, "$".$variable_name."['".$config_key."']") !== false)
					{
						if(substr($line_value, -1) === ';')
						{
							$line_value = "$".$variable_name."['".$config_key."'] = ".$final_value;
						}
						else
						{
							$multiline = true;

							continue;
						}
					}

					if(!empty($line_value))
					{
						file_put_contents($temp_file_path, $line_value."\r\n", FILE_APPEND);
					}
				}

				IO::copyFile($temp_file_path, $config_file_path);
				IO::removeFileFolderEnforce($temp_file_path);
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

	/**
	 * A thread-safe method to set or change a config value in an INI config file.
	 * @param string $config_file_path
	 * @param string $config_key
	 * @param $value
	 * @return bool
	 * @throws \Exception
	 */
	public static function _setConfigInINIFile(string $config_file_path, string $config_key, $value): bool
	{
		if(IO::isValidFile($config_file_path))
		{
			$lock_file = __DIR__.DIRECTORY_SEPARATOR.md5($config_file_path).'lock';

			if(IO::isValidFile($lock_file) || file_put_contents($lock_file, '') === false)
			{
				throw new \Exception('Cannot set lock for config file "'.$config_file_path.'". Another program may already be modifying it.', Codes::INTERNAL_CONFIG_FILE_CANNOT_LOCK);
			}

			$lines = parse_ini_file($config_file_path, true, INI_SCANNER_TYPED);

			if(!empty($lines))
			{
				$temp_file_path = $config_file_path.'.tmp.php';

				file_put_contents($temp_file_path, '');

				foreach($lines as $line_number => $line_value)
				{
					$line_value = Strings::trim($line_value);

					if(substr($line_value,0,strlen($config_key)) === $config_key)
					{
						$line_value = self::_getINIFinalValue($line_value, $value, $config_key, $lock_file);
					}

					if(!empty($line_value))
					{
						file_put_contents($temp_file_path, $line_value."\r\n", FILE_APPEND);
					}
				}

				IO::copyFile($temp_file_path, $config_file_path);
				IO::removeFileFolderEnforce($temp_file_path);
			}
			else
			{
				$line_value = self::_getINIFinalValue('', $value, $config_key, $lock_file);

				if(!empty($line_value))
				{
					file_put_contents($config_file_path,$line_value."\r\n");
				}
			}

			IO::removeFileFolderEnforce($lock_file);

			return true;
		}
		else
		{
			throw new \Exception('Cannot find config file "'.$config_file_path.'".', Codes::INTERNAL_CONFIG_FILE_NOT_FOUND);
		}
	}

	/**
	 * Prepares the value to be inserted into the INI file.
	 * @param string $line_value
	 * @param $value
	 * @param string $config_key
	 * @param string $lock_file
	 * @return string
	 * @throws \Exception
	 */
	protected static function _getINIFinalValue(string $line_value, $value, string $config_key, string $lock_file): string
	{
		switch(gettype($value))
		{
			case 'string':
				$line_value = $config_key.' = "'.str_replace('"', '\\"', Strings::trim($value)).'"';
				break;
			case 'integer':
			case 'double':
			case 'boolean':
				$line_value = $config_key.' = '.Strings::trim($value);
				break;
			case 'array':
				if(!empty($value))
				{
					$line_value = '';
					foreach($value as $inner_key => $inner_value)
					{
						switch(gettype($inner_value))
						{
							case 'string':
								$inner_value = '"'.str_replace('"', '\\"', Strings::trim($value)).'"';
								break;
							case 'integer':
							case 'double':
							case 'boolean':
								$inner_value = Strings::trim($inner_value);
								break;
							default:
								throw new \Exception('Unsupported configuration value "'.gettype($inner_value).'" provided.', Codes::INTERNAL_CONFIG_UNSUPPORTED_VALUE_TYPE);
						}

						if(is_numeric($inner_key))
						{
							$line_value .= $config_key.'[] = '.$inner_value;
						}
						else
						{
							$line_value .= $config_key.'['.$inner_key.'] = '.$inner_value;
						}
					}
				}
				break;
			default:
				IO::removeFileFolderEnforce($lock_file);

				throw new \Exception('Unsupported configuration value "'.gettype($value).'" provided.', Codes::INTERNAL_CONFIG_UNSUPPORTED_VALUE_TYPE);
		}

		return $line_value;
	}
}