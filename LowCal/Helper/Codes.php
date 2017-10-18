<?php
declare(strict_types=1);

namespace LowCal\Helper;

/**
 * Class Codes
 * A static class that holds constants for error codes used around LowCal.
 * @package LowCal\Helper
 */
class Codes
{
	/*
	 * LowCal startup errors.
	 */
	const INTERNAL_EXCEPTION_MISSING_CLASS = 110;
	const INTERNAL_CONFIG_MISSING_KEY = 111;
	const INTERNAL_CONFIG_UNSUPPORTED_FILE = 112;
	const INTERNAL_CONFIG_FILE_NOT_FOUND = 113;
	const INTERNAL_CONFIG_UNSUPPORTED_VALUE_TYPE = 114;
	const INTERNAL_CONFIG_FILE_CANNOT_LOCK = 115;

	/*
	 * Security module related errors.
	 */
	const SECURITY_EXCEPTION_DOMAINCHECK = 120;
	const SECURITY_EXCEPTION_CHECKSUM = 121;

	/*
	 * View module related errors.
	 */
	const VIEW_INVALID_ENGINE = 130;
	const VIEW_NOT_FOUND = 131;
	const VIEW_ENGINE_NOT_STARTED = 132;

	/*
	 * Routing module related errors.
	 */
	const ROUTING_ERROR_NO_CLASS_OR_ACTION = 140;
	const ROUTING_ERROR_NO_ROUTE = 141;
	const ROUTING_ERROR_REGEX_MATCH_ERROR = 142;
	const ROUTING_ERROR_MISSING_REQ_TERMS = 143;
	const ROUTING_ERROR_NO_URI = 144;
	const ROUTING_ERROR_INVALID_ROUTE = 145;
	const ROUTING_ERROR_FILE_NOT_FOUND = 146;

	/*
	 * Locale module related errors.
	 */
	const LOCALE_FILE_NOT_FOUND = 150;
	const LOCALE_INVALID_LOCALE = 151;
	const LOCALE_NO_LOCALE = 152;

	/*
	 * Db module related errors.
	 */
	const DB_IDENTIFIER_MISSING = 160;
	const DB_BAD_TYPE = 161;
	const DB_CONNECT_ERROR = 162;
	const DB_INVALID_RESULT_TYPE = 163;
	const DB_INCORRECT_SANITIZE_DIRECTIVE = 164;
	const DB_CANNOT_OPEN_DATABASE = 165;
	const DB_AUTH_CONNECT_ERROR = 166;
	const  DB_CANNOT_SET_LOCK = 167;
	const DB_SDK_UNKNOWN = 168;

	/*
	 * Log module related errors.
	 */
	const LOG_IDENTIFIER_MISSING = 170;
	const LOG_INVALID_DIR = 171;
	const LOG_CANNOT_WRITE_TO_FILE = 172;
	const LOG_INVALID_FILE = 173;
	const LOG_INVALID_LOG_TYPE = 174;

	/*
	 * IO helper related errors.
	 */
	const IO_DIR_ACCESS_ACTION_DENIED = 180;
	const IO_FILE_ACCESS_ACTION_DENIED = 181;

	/*
	 * Cache module related errors.
	 */
	const CACHE_IDENTIFIER_MISSING = 190;
	const CACHE_BAD_TYPE = 191;
	const CACHE_CONNECT_ERROR = 192;
	const CACHE_CANNOT_OPEN_DATABASE = 193;
	const CACHE_CANNOT_SET_LOCK = 194;
	const CACHE_CANNOT_SET_KEYVALUE = 195;
}