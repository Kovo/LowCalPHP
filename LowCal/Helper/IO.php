<?php
declare(strict_types=1);
namespace LowCal\Helper;

/**
 * Class IO
 * A static class that exposes PHP's common IO functions in a more understandable way, as well as introducing
 * new functionality that does not currently exist with built-in functions.
 * @package LowCal\Helper
 */
class IO
{
	/**
	 * Path detection flag for absolute paths.
	 * @var int
	 */
	const ABSOLUTE = 1;

	/**
	 * Path detection flag for relative paths.
	 * @var int
	 */
	const RELATIVE = 2;

	/**
	 * Path detection flag for urls.
	 * @var int
	 */
	const URL = 3;

	/**
	 * Default CHMOD flags for files.
	 * @var int
	 */
	public static $default_chmod_level_file = 0775;

	/**
	 * Default CHMOD flags for directories.
	 * @var int
	 */
	public static $default_chmod_level_dir = 0775;

	/**
	 * Verifies if provided variable represents a valid and accessible directory.
	 * @param string $dir
	 * @return bool
	 */
	public static function isValidDir(string $dir): bool
	{
		return self::isAccessibleDirectory($dir, 'rw');
	}

	/**
	 * Verifies if provided variable represents a valid and accessible file.
	 * @param string $file
	 * @return bool
	 */
	public static function isValidFile(string $file): bool
	{
		return self::isAccessibleFile($file, 'rw');
	}

	/**
	 * Check permission of directory or file based on specified requirements.
	 * @param string $dirOrFile
	 * @param string $accessLevels
	 * @return bool
	 */
	public static function checkPermissionLevels(string $dirOrFile, string $accessLevels): bool
	{
		foreach(str_split($accessLevels) as $need)
		{
			switch($need)
			{
				case 'r':
					if(!is_readable($dirOrFile))
					{
						return false;
					}

					break;
				case 'w':
					if(!is_writable($dirOrFile))
					{
						return false;
					}

					break;
				case 'e':
					if(!is_executable($dirOrFile))
					{
						return false;
					}

					break;
			}
		}

		return true;
	}

	/**
	 * Check permission of directory based on specified requirements.
	 * @param string $dir
	 * @param string $accessLevels
	 * @return bool
	 */
	public static function isAccessibleDirectory(string $dir, string $accessLevels): bool
	{
		if(!is_dir($dir))
		{
			if(!mkdir($dir, self::$default_chmod_level_dir, true))
			{
				return false;
			}
		}

		if(!self::checkPermissionLevels($dir, $accessLevels))
		{
			if(!chmod($dir, self::$default_chmod_level_dir) || !self::checkPermissionLevels($dir, $accessLevels))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check permission of file based on specified requirements.
	 * @param string $file
	 * @param string $accessLevels
	 * @return bool
	 */
	public static function isAccessibleFile(string $file, string $accessLevels): bool
	{
		if(!is_file($file))
		{
			return false;
		}

		if(!self::checkPermissionLevels($file, $accessLevels))
		{
			if(!chmod($file, self::$default_chmod_level_file) || !self::checkPermissionLevels($file, $accessLevels))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Verifies if provided variable represents a valid and accessible directory, file, or directory of files and directories.
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
	 * Creates a directory.
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
	 * Removes a file or folder if permissions allow it.
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
				return self::recursiveRmdir($filename);
			}
		}
		else
		{
			return true;
		}
	}

	/**
	 * Removes a file or folder, and if it does not have permission, will try to get it.
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
	 * Moves a file or folder if permissions allow it.
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
	 * Moves a file or folder, and if it does not have permission, will try to get it.
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
	 * Copy a file, or copy a folder recursively.
	 * @param string $source_folder_name
	 * @param string $target_folder_name
	 * @param string $filename
	 * @return bool
	 */
	public static function copyFile(string $source_folder_name, string $target_folder_name, string $filename): bool
	{
		return self::recursiveCopy($source_folder_name.$filename, $target_folder_name.$filename);
	}

	/**
	 * Renames (or moves) a file or directory.
	 * @param string $filename
	 * @param string $foldername
	 * @param string $newfilename
	 * @return bool
	 */
	public static function renameFile(string $filename, string $foldername, string $newfilename): bool
	{
		return rename($foldername.$filename, $foldername.$newfilename);
	}

	/**
	 * Recursively removes a file or directory.
	 * @param string $dir
	 * @return bool
	 */
	public static function recursiveRmdir(string $dir): bool
	{
		if(is_dir($dir))
		{
			$files = scandir($dir);
			foreach ($files as $file)
			{
				if($file != "." && $file != "..")
				{
					self::recursiveRmdir("$dir/$file");
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
	 * Recursively copies a file or folder.
	 * @param string $src
	 * @param string $dst
	 * @return bool
	 */
	public static function recursiveCopy(string $src, string $dst): bool
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
	 * Detects the type of path passed to it.
	 * @param string $path
	 * @return int
	 */
	public static function pathType(string $path): int
	{
		if(strpos($path,'://') !== false)
		{
			return self::URL;
		}
		elseif(substr($path,0,1) !== DIRECTORY_SEPARATOR)
		{
			return self::RELATIVE;
		}
		else
		{
			return self::ABSOLUTE;
		}
	}

	/**
	 * Add a trailing slash to provided path if necessary.
	 * @param string $path
	 * @return string
	 */
	public static function addTrailingSlash(string $path): string
	{
		if(substr($path, -1) !== DIRECTORY_SEPARATOR)
		{
			return $path.DIRECTORY_SEPARATOR;
		}

		return $path;
	}

	/**
	 * Extracts path from provided variable (omits files at the end), or simply returns the variable if no file is found.
	 * @param string $full_path
	 * @return string
	 */
	public static function extractPath(string $full_path): string
	{
		if(!is_dir($full_path))
		{
			$explode = explode(DIRECTORY_SEPARATOR, $full_path);
			array_pop($explode);

			return implode(DIRECTORY_SEPARATOR, $explode).DIRECTORY_SEPARATOR;
		}
		else
		{
			return self::addTrailingSlash($full_path);
		}
	}

	/**
	 * Extract file from provided variable (omits directory at the beginning), or simply returns variable if no directory is found.
	 * @param string $full_path
	 * @return string
	 */
	public static function extractFilename(string $full_path): string
	{
		$explode = explode(DIRECTORY_SEPARATOR, $full_path);

		return array_pop($explode);
	}
}
