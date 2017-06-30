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
	const INTERNAL_EXCEPTION_MISSING_CLASS = 10;
	const INTERNAL_CONFIG_MISSING_KEY = 11;

	/*
	 * Security module related errors.
	 */
	const SECURITY_EXCEPTION_DOMAINCHECK = 20;
	const SECURITY_EXCEPTION_CHECKSUM = 21;

	/*
	 * View module related errors.
	 */
	const VIEW_INVALID_ENGINE = 30;
	const VIEW_NOT_FOUND = 31;
	const VIEW_ENGINE_NOT_STARTED = 32;

	/*
	 * Routing module related errors.
	 */
	const ROUTING_ERROR_NO_CLASS_OR_ACTION = 40;
	const ROUTING_ERROR_NO_ROUTE = 41;
	const ROUTING_ERROR_REGEX_MATCH_ERROR = 42;
	const ROUTING_ERROR_MISSING_REQ_TERMS = 43;
	const ROUTING_ERROR_NO_URI = 44;

	/*
	 * Locale module related errors.
	 */
	const LOCALE_FILE_NOT_FOUND = 50;
	const LOCALE_INVALID_LOCALE = 51;
	const LOCALE_NO_LOCALE = 52;

	/*
	 * Db module related errors.
	 */
	const DB_IDENTIFIER_MISSING = 60;
	const DB_BAD_TYPE = 61;
	const DB_CONNECT_ERROR = 62;
	const DB_INVALID_RESULT_TYPE = 63;
	const DB_INCORRECT_SANITIZE_DIRECTIVE = 64;
	const DB_CANNOT_OPEN_DATABASE = 65;
	const DB_AUTH_CONNECT_ERROR = 66;

	/*
	 * Log module related errors.
	 */
	const LOG_IDENTIFIER_MISSING = 70;
	const LOG_INVALID_DIR = 71;
	const LOG_CANNOT_WRITE_TO_FILE = 72;
	const LOG_INVALID_FILE = 73;
	const LOG_INVALID_LOG_TYPE = 74;

	/*
	 * IO helper related errors.
	 */
	const IO_DIR_ACCESS_ACTION_DENIED = 80;
	const IO_FILE_ACCESS_ACTION_DENIED = 81;

	/*
	 * Cache module related errors.
	 */
	const CACHE_IDENTIFIER_MISSING = 90;
	const CACHE_BAD_TYPE = 91;
	const CACHE_CONNECT_ERROR = 92;
	const CACHE_CANNOT_OPEN_DATABASE = 93;
	const CACHE_CANNOT_SET_LOCK = 94;
	const CACHE_CANNOT_SET_KEYVALUE = 95;
}