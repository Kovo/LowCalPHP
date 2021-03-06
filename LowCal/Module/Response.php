<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Module;

use LowCal\Base;

/**
 * Class Response
 * Main Response class used to set response variables, etc...
 * @package LowCal\Module
 */
class Response extends Module
{
	/**
	 * Headers to send with response.
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * Status code to send with response.
	 * @var int
	 */
	protected $_status_code = 200;

	/**
	 * HTTP version to send with the response.
	 * @var string
	 */
	protected $_http_version = '1.1';

	/**
	 * Array of http status codes.
	 * @var array
	 */
	protected $_status_text = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Reserved',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Reserved for WebDAV advanced collections expired proposal',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates (Experimental)',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		510 => 'Not Extended',
		511 => 'Network Authentication Required'
	);

	/**
	 * Response constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);

		$headers_to_send = headers_list();

		if(!empty($headers_to_send))
		{
			foreach($headers_to_send as $header)
			{
				$explode = explode(':', $header);

				$this->_headers[array_shift($explode)] = implode(':', $explode);
			}
		}
	}

	/**
	 * Set a header value.
	 * @param string $name
	 * @param string $value
	 * @param bool $replace
	 * @param int|null $response_code
	 * @return Response
	 */
	public function setHeader(string $name, string $value = '', bool $replace = false, ?int $response_code = null): Response
	{
		if($response_code)
		{
			header($name.($value !== ''?': '.$value:''), $replace, $response_code);
		}
		else
		{
			header($name.($value !== ''?': '.$value:''), $replace);
		}

		$this->_headers[$name] = ($value !== ''?$value:$name);

		return $this;
	}

	/**
	 * Get set headers.
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->_headers;
	}

	/**
	 * Get a specific set header.
	 * @param string $name
	 * @return string
	 */
	public function getHeader(string $name): string
	{
		return $this->_headers[$name];
	}

	/**
	 * Redirect this request as opposed to responding to it directly.
	 * @param string $url
	 * @param bool $exit
	 */
	public function redirect(string $url, bool $exit = true): void
	{
		$this->setHeader('HTTP/1.1 301 Moved Permanently');
		$this->setHeader('Location', $url);
		$this->setHeader('Connection', 'close');

		if($exit)
		{
			exit();
		}
	}

	/**
	 * Set the http status.
	 * @param int $code
	 * @return Response
	 */
	public function setStatusCode(int $code): Response
	{
		$this->_status_code = $code;

		$this->setHeader(sprintf('HTTP/%s %s %s', $this->_http_version, $this->_status_code, $this->_status_text[$this->_status_code]), '', true, $this->_status_code);

		return $this;
	}

	/**
	 * Set the http version.
	 * @param string $version
	 * @return Response
	 */
	public function setHttpVersion(string $version): Response
	{
		$this->_http_version = $version;

		return $this;
	}

	/**
	 * Get set status code.
	 * @return int
	 */
	public function getStatusCode(): int
	{
		return $this->_status_code;
	}

	/**
	 * Get set http version.
	 * @return string
	 */
	public function getHttpVersion(): string
	{
		return $this->_http_version;
	}

	/**
	 * Get set status code text.
	 * @return string
	 */
	public function getStatusText(): string
	{
		return $this->_status_text[$this->_status_code];
	}
}