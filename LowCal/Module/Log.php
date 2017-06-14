<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Helper\Codes;

/** 
 * Class Log
 * @package LowCal\Module
 */
class Log extends Module
{
	/**
	 * @var array
	 */
	protected $_log_files = array();

	/**
	 * @param string $identifier
	 * @param string $directory
	 * @param bool $rotate
	 * @param int|null $delete_after_days
	 * @return Log
	 * @throws \Exception
	 */
	public function registerFile(string $identifier, string $directory, bool $rotate = true, ?int $delete_after_days = 7): Log
	{
		if(empty($identifier))
		{
			throw new \Exception('Identifier cannot be empty', Codes::LOG_IDENTIFIER_MISSING);
		}

		if(file_exists($directory) && !is_dir($directory))
		{
			throw new \Exception('Directory supplied is not a directory.', Codes::LOG_INVALID_DIR);
		}
		elseif(!file_exists($directory))
		{
			mkdir($directory, 0775, true);
		}

		$filename = $identifier;

		if($rotate)
		{
			$filename .= date('-Y-m-d');

			if(!empty($delete_after_days))
			{
				$files = scandir($directory);

				if(!empty($files))
				{
					foreach($files as $fileName)
					{
						if(PzPHP_Helper_IO::isValidDir($path.$fileName))
						{
							continue;
						}

						if(!preg_match(self::REGEX_LOG_PATTERN, $fileName, $matches))
						{
							continue;
						}

						if(isset($matches[1]))
						{
							$fileDate		= new DateTime($matches[1]);
							$currentDate	= new DateTime(date('Y-m-d'));

							$interval		= $fileDate->diff($currentDate);

							if($interval->days >= PzPHP_Config::get('SETTING_DELETE_LOG_FILES_AFTER_DAYS'))
							{
								PzPHP_Helper_IO::removeFileFolderEnforce($path.$fileName);
							}
						}
					}
				}
			}
		}

		$this->_log_files[$identifier] = array(
			'directory' => $directory,
			'filename' => $filename.'.log'
		);

		return $this;
	}
}