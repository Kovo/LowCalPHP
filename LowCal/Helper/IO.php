<?php
declare(strict_types=1);
namespace LowCal\Helper;

/**
 * Class IO
 * @package LowCal\Helper
 */
class IO
{
	/**
	 * @var int
	 */
	const ABSOLUTE = 1;

	/**
	 * @var int
	 */
	const RELATIVE = 2;

	/**
	 * @var int
	 */
	const URL = 3;

	/**
	 * @var int
	 */
	public static $default_chmod_level_file = 0775;

	/**
	 * @var int
	 */
	public static $default_chmod_level_dir = 0775;

	/**
	 * @param string $dir
	 * @return bool
	 */
	public static function isValidDir(string $dir): bool
	{
		return (is_dir($dir) && is_readable($dir) && is_writable($dir));
	}

	/**
	 * @param string $file
	 * @return bool
	 */
	public static function isValidFile(string $file): bool
	{
		return (file_exists($file) && is_readable($file) && is_writable($file));
	}

	/**
	 * @param string $source
	 * @return bool
	 * @throws \Exception
	 */
	public static function isValidSource(string $source): bool
	{
		if(is_dir($source))
		{
			$source = self::addTrailingSlash($source);

			chmod($source, self::$default_chmod_level_dir);

			if(self::isValidDir($source))
			{
				$scannedDir = scandir($source);

				if(!empty($scannedDir))
				{
					foreach($scannedDir as $fileName)
					{
						if($fileName !== '..' && $fileName !== '.')
						{
							self::isValidSource($source.$fileName);
						}
					}
				}
			}
			else
			{
				throw new \Exception($source.' is not a fully accessible directory.', Codes::IO_DIR_ACCESS_ACTION_DENIED);
			}
		}
		else
		{
			chmod($source, self::$default_chmod_level_file);

			if(!self::isValidFile($source))
			{
				throw new \Exception($source.' is not a fully accessible file.', Codes::IO_FILE_ACCESS_ACTION_DENIED);
			}
		}

		return true;
	}

	/**
	 * @param string $dir
	 * @param int|null $permissions
	 * @param bool $recursive
	 * @return bool
	 */
	public static function createDir(string $dir, ?int $permissions = null, bool $recursive = true): bool
	{
		if(!file_exists($dir))
		{
			return mkdir(
				$dir,
				($permissions===null?self::$default_chmod_level_dir:$permissions),
				$recursive
			);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	public static function removeFileFolder(string $filename): bool
	{
		if(file_exists($filename))
		{
			if(!is_dir($filename))
			{
				return unlink($filename);
			}
			else
			{
				return self::_recursiveRmdir($filename);
			}
		}
		else
		{
			return true;
		}
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	public static function removeFileFolderEnforce(string $filename): bool
	{
		if(!self::removeFileFolder($filename))
		{
			chmod($filename, (!is_dir($filename)?self::$default_chmod_level_file:self::$default_chmod_level_dir));

			return self::removeFileFolder($filename);
		}
		else
		{
			return true;
		}
	}

	/**
	 * @param string $source_folder_name
	 * @param string $target_folder_name
	 * @param string $filename
	 * @return bool
	 */
	public static function moveFile(string $source_folder_name, string $target_folder_name, string $filename): bool
	{
		if(self::recursiveCopy($source_folder_name.$filename, $target_folder_name.$filename))
		{
			self::removeFileFolder($source_folder_name.$filename);

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $source_folder_name
	 * @param $target_folder_name
	 * @param $filename
	 * @return bool
	 */
	public static function moveFileEnforce(string $source_folder_name, string $target_folder_name, string $filename): bool
	{
		if(!self::moveFile($source_folder_name, $target_folder_name, $filename))
		{
			chmod($source_folder_name, self::$default_chmod_level_dir);
			chmod($source_folder_name.$filename, self::$default_chmod_level_file);
			chmod($target_folder_name, self::$default_chmod_level_dir);

			return self::moveFile($source_folder_name, $target_folder_name, $filename);
		}
		else
		{
			return true;
		}
	}

	/**
	 * @param $sourcefoldername
	 * @param $targetfoldername
	 * @param $filename
	 * @return bool
	 */
	public static function copyFile($sourcefoldername, $targetfoldername, $filename)
	{
		return self::recursiveCopy($sourcefoldername.$filename, $targetfoldername.$filename);
	}

	/**
	 * @param $filename
	 * @param $foldername
	 * @param $newfilename
	 * @return bool
	 */
	public static function renameFile($filename, $foldername, $newfilename)
	{
		return rename($foldername.$filename, $foldername.$newfilename);
	}

	/**
	 * @param $dir
	 * @return bool
	 */
	private static function _recursiveRmdir($dir)
	{
		if(is_dir($dir))
		{
			$files = scandir($dir);
			foreach ($files as $file)
			{
				if($file != "." && $file != "..")
				{
					self::_recursiveRmdir("$dir/$file");
				}
			}

			return rmdir($dir);
		}
		elseif(file_exists($dir))
		{
			return unlink($dir);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $src
	 * @param $dst
	 * @return bool
	 */
	public static function recursiveCopy($src, $dst)
	{
		if(is_dir($src))
		{
			if(!is_dir($dst))
			{
				mkdir($dst, self::$default_chmod_level_dir, true);
			}

			$files = scandir($src);
			foreach($files as $file)
			{
				if($file != "." && $file != "..")
				{
					self::recursiveCopy("$src/$file", "$dst/$file");
				}
			}

			return true;
		}
		elseif(file_exists($src))
		{
			return copy($src, $dst);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $path
	 * @return int
	 */
	public static function pathType($path)
	{
		if(substr($path,0,7)==='http://' || substr($path,0,8)==='https://' || substr($path,0,6)==='ftp://')
		{
			return self::URL;
		}
		else
		{
			return self::RELATIVE;
		}
	}

	/**
	 * @param $path
	 * @return string
	 */
	public static function addTrailingSlash($path)
	{
		if(substr($path, -1) !== DIRECTORY_SEPARATOR)
		{
			return $path.DIRECTORY_SEPARATOR;
		}

		return $path;
	}

	/**
	 * @param $fullPath
	 * @return string
	 */
	public static function extractPath($fullPath)
	{
		if(!is_dir($fullPath))
		{
			$explode = explode(DIRECTORY_SEPARATOR, $fullPath);
			array_pop($explode);

			return implode(DIRECTORY_SEPARATOR, $explode).DIRECTORY_SEPARATOR;
		}
		else
		{
			return self::addTrailingSlash($fullPath);
		}
	}

	/**
	 * @param $fullPath
	 * @return string
	 */
	public static function extractFilename($fullPath)
	{
		$explode = explode(DIRECTORY_SEPARATOR, $fullPath);

		return array_pop($explode);
	}
}
