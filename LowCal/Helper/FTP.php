<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Helper;

/**
 * Class FTP
 * @package LowCal\Helper
 */
class FTP
{
	/**
	 * @param string $username
	 * @param string $password
	 * @return array
	 * @throws \Exception
	 */
	public static function connect(string $host, string $username, string $password, int $port = 21)
	{
		// set up basic connection
		$conn_id = ftp_connect($host, $port);

		// login with username and password
		$login_result = ftp_login($conn_id, $username, $password);

		// check connection
		if((!$conn_id) || (!$login_result))
		{
			throw new \Exception('FTP connection has failed! Attempted to connect to '.$host.' with user '.$username);
		}

		return $conn_id;
	}

	/**
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 * @param int $port
	 * @return resource
	 * @throws \Exception
	 */
	public static function sconnect(string $host, string $username, string $password, int $port = 21)
	{
		// set up basic connection
		$conn_id = ftp_ssl_connect($host, $port);

		// login with username and password
		$login_result = ftp_login($conn_id, $username, $password);

		// check connection
		if((!$conn_id) || (!$login_result))
		{
			throw new \Exception('SSL FTP connection has failed! Attempted to connect to '.$host.' with user '.$username);
		}

		return $conn_id;
	}

	/**
	 * @param $conn_id
	 * @return bool
	 */
	public static function close($conn_id): bool
	{
		return ftp_close($conn_id);
	}

	/**
	 * @param $conn_id
	 * @param string $destination_file
	 * @param string $source_file
	 * @return bool
	 */
	public static function upload($conn_id, string $destination_file, string $source_file): bool
	{
		return ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);
	}

	/**
	 * @param $conn_id
	 * @param string $destination_file
	 * @param string $source_file
	 * @return bool
	 */
	public static function download($conn_id, string $destination_file, string $source_file): bool
	{
		return ftp_get($conn_id, $destination_file, $source_file, FTP_BINARY);
	}

	/**
	 * @param $conn_id
	 * @param string $destination_file
	 * @return bool
	 */
	public static function deleteFile($conn_id, string $destination_file): bool
	{
		return ftp_delete($conn_id, $destination_file);
	}

	/**
	 * @param $conn_id
	 * @param string $destination_dir
	 * @return bool
	 */
	public static function deleteDir($conn_id, string $destination_dir): bool
	{
		return ftp_rmdir($conn_id, $destination_dir);
	}

	/**
	 * @param $conn_id
	 * @param string $destination_file
	 * @param string $source_file
	 * @return bool
	 */
	public static function rename($conn_id, string $destination_file, string $source_file): bool
	{
		return ftp_rename ($conn_id, $source_file, $destination_file);
	}

	/**
	 * @param $conn_id
	 * @return bool
	 */
	public static function cdup($conn_id): bool
	{
		return ftp_cdup($conn_id);
	}

	/**
	 * @param $conn_id
	 * @param string $destination_dir
	 * @return bool
	 */
	public static function chdir($conn_id, string $destination_dir): bool
	{
		return ftp_chdir($conn_id, $destination_dir);
	}

	/**
	 * @param $conn_id
	 * @param string $destination_dir
	 * @return array
	 */
	public static function nlist($conn_id, string $destination_dir): array
	{
		$return = ftp_nlist($conn_id, $destination_dir);

		if($return === false)
		{
			return array();
		}
		else
		{
			return $return;
		}
	}

	/**
	 * @param $conn_id
	 * @return string
	 */
	public static function pwd($conn_id): string
	{
		return ftp_pwd($conn_id);
	}

	/**
	 * @param $conn_id
	 * @param $mode
	 * @param string $destination_file
	 * @return int
	 */
	public static function chmod($conn_id, $mode, string $destination_file)
	{
		return ftp_chmod($conn_id, $mode, $destination_file);
	}

	/**
	 * @param $conn_id
	 * @param string $destination_file
	 * @return int
	 */
	public static function mdtm($conn_id, string $destination_file): int
	{
		return ftp_mdtm($conn_id, $destination_file);
	}

	/**
	 * @param $conn_id
	 * @param bool $endis
	 * @return bool
	 */
	public static function pasv($conn_id, bool $endis): bool
	{
		return ftp_pasv($conn_id, $endis);
	}
}