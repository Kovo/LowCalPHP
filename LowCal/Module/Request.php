<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Module;

use LowCal\Base;
use LowCal\Helper\Arrays;
use LowCal\Helper\Strings;

/**
 * Class Request
 * The Request module allows for interacting with request data.
 * @package LowCal\Module
 */
class Request extends Module
{
	/**
	 * Flag for if this request is ajax or not.
	 * @var bool
	 */
	protected $_is_ajax = false;

	/**
	 * The query string for this request.
	 * @var string
	 */
	protected $_query_string = '';

	/**
	 * The different media types assigned to this request.
	 * @var array
	 */
	protected $_media_types = array();

	/**
	 * The different charsets assigned to this request.
	 * @var array
	 */
	protected $_charsets = array();

	/**
	 * This request's encodings.
	 * @var array
	 */
	protected $_encodings = array();

	/**
	 * This request's languages.
	 * @var array
	 */
	protected $_languages = array();

	/**
	 * This request's referrer.
	 * @var string
	 */
	protected $_referer = '';

	/**
	 * If this request is secure or not.
	 * @var bool
	 */
	protected $_secure = false;

	/**
	 * Request constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);

		$this->_detectAjax();
		$this->_detectQueryString();
		$this->_detectReferer();
		$this->_detectHttps();
		$this->_detectMediaTypes();
		$this->_detectCharsets();
		$this->_detectEncodings();
		$this->_detectLanguages();
	}

	/**
	 * Detects the referrer info if available.
	 */
	protected function _detectReferer(): void
	{
		$raw_data = $_SERVER['HTTP_REFERER'] ?? '';

		if(!empty($raw_data))
		{
			$this->_referer = Strings::trim($raw_data);
		}
	}

	/**
	 * Detects if the request is using HTTPS.
	 */
	protected function _detectHttps(): void
	{
		$raw_data = $_SERVER['HTTPS'] ?? '';

		if(!empty($raw_data))
		{
			$raw_data = strtolower(Strings::trim($raw_data));

			if($raw_data !== '' && $raw_data !== 'off')
			{
				$this->_secure = true;
			}
		}
	}

	/**
	 * Extracts query string if available.
	 */
	protected function _detectQueryString(): void
	{
		$raw_data = $_SERVER['QUERY_STRING'] ?? '';

		if(!empty($raw_data))
		{
			$this->_query_string = $this->cleanQueryString($raw_data);
		}
	}

	/**
	 * Detects the media type info if available.
	 */
	protected function _detectMediaTypes(): void
	{
		$raw_data = $_SERVER['HTTP_ACCEPT'] ?? '';

		if(!empty($raw_data))
		{
			$raw_data = Strings::trim($raw_data);

			if($raw_data !== '')
			{
				$extract_info = $this->parseAcceptHeader($raw_data);

				if($extract_info !== NULL)
				{
					$this->_media_types = $extract_info;
				}
			}
		}

		if(count($this->_media_types) === 0)
		{
			$this->_media_types[] = array(
				'main_type' => '*/*',
				'sub_type' => '',
				'precedence' => 1,
				'tokens' => false
			);
		}
	}

	/**
	 * Detects the charset info if available.
	 */
	protected function _detectCharsets(): void
	{
		$raw_data = $_SERVER['HTTP_ACCEPT_CHARSET'] ?? '';

		if(!empty($raw_data))
		{
			$raw_data = Strings::trim($raw_data);

			if($raw_data !== '')
			{
				$extract_info = $this->parseAcceptHeader($raw_data);

				if($extract_info !== NULL)
				{
					$this->_charsets = $extract_info;
				}
			}
		}

		if(count($this->_charsets) === 0)
		{
			$this->_charsets[] = array(
				'main_type' => 'ISO-8859-1',
				'sub_type' => '',
				'precedence' => 1,
				'tokens' => false
			);
		}
	}

	/**
	 * Detects the encoding info if available.
	 */
	protected function _detectEncodings(): void
	{
		$raw_data = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';

		if(!empty($raw_data))
		{
			$raw_data = Strings::trim($raw_data);

			if($raw_data !== '')
			{
				$extract_info = $this->parseAcceptHeader($raw_data);

				if($extract_info !== NULL)
				{
					$this->_encodings = $extract_info;
				}
			}
		}

		if(count($this->_encodings) === 0)
		{
			$this->_encodings[] = array(
				'main_type' => '*',
				'sub_type' => '',
				'precedence' => 1,
				'tokens' => false
			);
		}
	}

	/**
	 * Detects the language info if available.
	 */
	protected function _detectLanguages(): void
	{
		$raw_data = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

		if(!empty($raw_data))
		{
			$raw_data = Strings::trim($raw_data);

			if($raw_data !== '')
			{
				$extract_info = $this->parseAcceptHeader($raw_data);

				if($extract_info !== NULL)
				{
					$this->_languages = $extract_info;
				}
			}
		}

		if(count($this->_languages) === 0)
		{
			$this->_languages[] = array(
				'main_type' => '*',
				'sub_type' => '',
				'precedence' => 1,
				'tokens' => false
			);
		}
	}

	/**
	 * Detects if the request is ajax or not.
	 */
	protected function _detectAjax(): void
	{
		$serverXmlHttpVar = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

		$this->_is_ajax = (!empty($serverXmlHttpVar) && strtolower($serverXmlHttpVar) === 'xmlhttprequest');
	}

	/**
	 * Parse the accept header for the specified header value.
	 * @param string $header
	 * @return array|null
	 */
	public function parseAcceptHeader(string $header): ?array
	{
		$return = null;
		$header = str_replace(array("\r\n", "\r", "\n"), ' ', Strings::trim($header));
		$types = explode(',', $header);
		$types = array_map('trim', $types);

		if($header !== '')
		{
			foreach($types as $rule_sets)
			{
				$ruleSet = array_map('trim', explode(';', $rule_sets));
				$rule = array_shift($ruleSet);

				if($rule)
				{
					$array = array_map('trim', explode('/', $rule));

					if(!isset($array[1]))
					{
						$array[1] = $array[0];
					}

					list($precedence, $tokens) = $this->acceptHeaderOptions($ruleSet);
					list($mainOption, $subOption) = $array;

					$return[] = array(
						'main_type' => $mainOption,
						'sub_type' => $subOption,
						'precedence' => (float)$precedence,
						'tokens' => $tokens
					);
				}
			}

			Arrays::aasort($return, 'precedence', SORT_NUMERIC, false);
		}

		return $return;
	}

	/**
	 * Get accept options from header.
	 * @param array $rule_set
	 * @return array
	 */
	public function acceptHeaderOptions(array $rule_set): array
	{
		$precedence = 1;
		$tokens = array();

		$rule_set = array_map(array('\LowCal\Helper\Strings', 'trim'), $rule_set);

		foreach($rule_set as $option)
		{
			$option = explode('=', $option);
			$option = array_map(array('\LowCal\Helper\Strings', 'trim'), $option);

			if($option[0] === 'q')
			{
				$precedence = $option[1];
			}
			else
			{
				$tokens[$option[0]] = $option[1];
			}
		}

		$tokens = (!empty($tokens)?$tokens:false);

		return array($precedence, $tokens);
	}

	/**
	 * Return AJAX flag.
	 * @return bool
	 */
	public function isAjax(): bool
	{
		return $this->_is_ajax;
	}

	/**
	 * Get detected media types.
	 * @return array
	 */
	public function getMediaTypes(): array
	{
		return $this->_media_types;
	}

	/**
	 * Get detected charsets.
	 * @return array
	 */
	public function getCharsets(): array
	{
		return $this->_charsets;
	}

	/**
	 * Get detected encodings.
	 * @return array
	 */
	public function getEncodings(): array
	{
		return $this->_encodings;
	}

	/**
	 * Get detected languages.
	 * @return array
	 */
	public function getLanguages(): array
	{
		return $this->_languages;
	}

	/**
	 * See if request is secure.
	 * @return bool
	 */
	public function isSecure(): bool
	{
		return $this->_secure;
	}

	/**
	 * Get detected referrer.
	 * @return string
	 */
	public function getReferer(): string
	{
		return $this->_referer;
	}

	/**
	 * Get detected ip address of requester.
	 * @return string
	 */
	public function clientIpAddress(): string
	{
		$http_client_ip = $_SERVER['HTTP_CLIENT_IP'] ?? null;
		$http_x_forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
		$remote_addr = $_SERVER['REMOTE_ADDR'] ?? null;

		if(!empty($http_client_ip))
		{
			$ip = $http_client_ip;
		}
		elseif(!empty($http_x_forwarded_for))
		{
			$ip = $http_x_forwarded_for;
		}
		elseif(!empty($remote_addr))
		{
			$ip = $remote_addr;
		}
		else
		{
			$ip = 'unknown';
		}

		return $ip;
	}

	/**
	 * Get server's ip address.
	 * @return string
	 */
	public function serverIpAddress(): string
	{
		$local_addr = $_SERVER['LOCAL_ADDR'];
		$server_addr = $_SERVER['SERVER_ADDR'];

		if(!empty($local_addr))
		{
			$ip = $local_addr;
		}
		elseif(!empty($server_addr))
		{
			$ip = $server_addr;
		}
		else
		{
			$ip = 'unknown';
		}

		return $ip;
	}

	/**
	 * Cleans query string for validation purposes.
	 * @param string $query_string
	 * @return string
	 */
	public function cleanQueryString(string $query_string): string
	{
		$query_string = Strings::trim($query_string);

		if($query_string !== '')
		{
			$parts = array();
			$order = array();

			foreach(explode('&', $query_string) as $param)
			{
				if($param === '' || $param[0] === '=')
				{
					continue;
				}

				$key_value_pair = explode('=', $param, 2);

				$parts[] = (isset($key_value_pair[1])?
					rawurlencode(urldecode($key_value_pair[0])).'='.rawurlencode(urldecode($key_value_pair[1])):
					rawurlencode(urldecode($key_value_pair[0]))
				);

				$order[] = urldecode($key_value_pair[0]);
			}

			array_multisort($order, SORT_ASC, $parts);

			$query_string = implode('&', $parts);
		}

		return $query_string;
	}
}