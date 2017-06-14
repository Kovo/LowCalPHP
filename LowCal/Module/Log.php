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

	public function registerFile(string $identifier, string $directory, string $filename, bool $rotate = true, int $delete_after_days = 7): Log
	{
		if(empty($identifier))
		{
			throw new \Exception('Identifier cannot be empty', Codes::LOG_IDENTIFIER_MISSING);
		}

		$this->_log_files[$identifier] = array(
			'directory' => $directory,
			'filename' => $filename,
		);

		return $this;
	}
}