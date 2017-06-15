<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Helper\Codes;
use LowCal\Helper\IO;

/** 
 * Class Log
 * @package LowCal\Module
 */
class Log extends Module
{
	/**
	 * @var string
	 */
	const REGEX_LOG_PATTERN = "#(?:.*)-([0-9]{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[1-2][0-9]|3[0-1]))\\.log#";

	/**
	 * @var int
	 */
	const TYPE_FILE = 1;

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
					foreach($files as $file)
					{
						if($file === '.' || $file === '..')
						{
							continue;
						}

						if(!preg_match(self::REGEX_LOG_PATTERN, $file, $matches))
						{
							continue;
						}

						if(IO::isValidDir($directory.$file))
						{
							continue;
						}

						if(isset($matches[1]) && explode('-', $file)[0] === $identifier)
						{
							$fileDate = new \DateTime($matches[1]);
							$currentDate = new \DateTime(date('Y-m-d'));

							$interval = $fileDate->diff($currentDate);

							if($interval->days >= $delete_after_days)
							{
								IO::removeFileFolderEnforce($directory.$file);
							}
						}
					}
				}
			}
		}

		$this->_log_files[$identifier] = array(
			'directory' => $directory,
			'filename' => $filename.'.log',
			'type' => self::TYPE_FILE
		);

		return $this;
	}

	/**
	 * @param string $identifier
	 * @param string $message
	 * @return Log
	 * @throws \Exception
	 */
	public function add(string $identifier, string $message): Log
	{
		if(isset($this->_log_files[$identifier]))
		{
			switch($this->_log_files[$identifier]['type'])
			{
				case self::TYPE_FILE:
					$this->addFile($message, $this->_log_files[$identifier]['directory'], $this->_log_files[$identifier]['filename']);
					break;
				default:
					throw new \Exception('Invalid log type provided ('.$this->_log_files[$identifier]['type'].').', Codes::LOG_INVALID_LOG_TYPE);
			}
		}

		return $this;
	}

	/**
	 * @param string $message
	 * @param string $directory
	 * @param string $filename
	 * @return bool
	 * @throws \Exception
	 */
	public function addFile(string $message, string $directory, string $filename): bool
	{
		if(IO::isValidFile($directory.$filename))
		{
			if(file_put_contents($directory.$filename, date('Y-m-d H:i:s')." | ".$message."\r\n", FILE_APPEND))
			{
				return true;
			}

			throw new \Exception('Cannot write to log file ('.$filename.').', Codes::LOG_CANNOT_WRITE_TO_FILE);
		}

		throw new \Exception('Cannot find log file ('.$filename.').', Codes::LOG_INVALID_FILE);
	}
}