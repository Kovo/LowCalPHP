<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Helper;

/**
 * Class Curl
 * @package LowCal\Helper
 */
class Curl
{
	/**
	 * @param string $url
	 * @param string|array $payload
	 * @param array $options
	 * @param bool $do_not_send
	 * @param bool $close_curl_at_end
	 * @return array
	 */
	public static function post(string $url, $payload, array $options = array(), bool $do_not_send = false, bool $close_curl_at_end = true): array
	{
		$curl_response = array();

		$curl_resource = curl_init();

		$options = array_replace(array(
			CURLOPT_URL => $url,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $payload,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1,
		), $options);

		curl_setopt_array($curl_resource, $options);

		if(!$do_not_send)
		{
			$curl_response['result'] = curl_exec($curl_resource);

			$curl_response['errno'] = curl_errno($curl_resource);
			$curl_response['info'] = curl_getinfo($curl_resource);

			if($close_curl_at_end)
			{
				curl_close($curl_resource);
			}

			return $curl_response;
		}
		else
		{
			$curl_response['resource'] = $curl_resource;

			return $curl_response;
		}
	}

	/**
	 * @param string $url
	 * @param array $options
	 * @param bool $do_not_send
	 * @param bool $close_curl_at_end
	 * @return array
	 */
	public static function get(string $url, array $options = array(), bool $do_not_send = false, bool $close_curl_at_end = true): array
	{
		$curl_response = array();

		$curl_resource = curl_init();

		$options = array(
			CURLOPT_URL => $url,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1,
		)+$options;

		curl_setopt_array($curl_resource, $options);

		if(!$do_not_send)
		{
			$curl_response['result'] = curl_exec($curl_resource);

			$curl_response['errno'] = curl_errno($curl_resource);
			$curl_response['info'] = curl_getinfo($curl_resource);

			if($close_curl_at_end)
			{
				curl_close($curl_resource);
			}

			return $curl_response;
		}
		else
		{
			$curl_response['resource'] = $curl_resource;

			return $curl_response;
		}
	}
}