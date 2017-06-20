<?php
declare(strict_types=1);
namespace LowCal\Helper;

/**
 * Class Codes
 * @package LowCal\Helper
 */
class Codes
{
	const INTERNAL_EXCEPTION_MISSING_CLASS = 10;

	const SECURITY_EXCEPTION_DOMAINCHECK = 20;
	const SECURITY_EXCEPTION_CHECKSUM = 21;

	const VIEW_NOT_FOUND = 30;

	const ROUTING_ERROR_NO_CLASS_OR_ACTION = 40;
	const ROUTING_ERROR_NO_ROUTE = 41;
	const ROUTING_ERROR_REGEX_MATCH_ERROR = 42;
	const ROUTING_ERROR_MISSING_REQ_TERMS = 43;
	const ROUTING_ERROR_NO_URI = 44;

	const LOCALE_FILE_NOT_FOUND = 50;
}