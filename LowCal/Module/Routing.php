<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Module;

use LowCal\Base;
use LowCal\Helper\Arrays;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Helper\IO;
use LowCal\Helper\Strings;

/**
 * Class Routing
 * The main Routing module handles all routing for LowCal applications.
 * @package LowCal\Module
 */
class Routing extends Module
{
	/**
	 * Key value for pattern of a specific route.
	 * @var int
	 */
	const PATTERN = 0;

	/**
	 * Key value for controller of a specific route.
	 * @var int
	 */
	const CONTROLLER = 1;

	/**
	 * Key value for action of a specific route.
	 * @var int
	 */
	const ACTION = 2;

	/**
	 * Key value for constraints of a specific route.
	 * @var int
	 */
	const CONSTRAINTS = 3;

	/**
	 * Key value for routing rule pattern.
	 * @var int
	 */
	const RULES = 4;

	/**
	 * Regex pattern used for detecting terms in route rules.
	 * @var string
	 */
	const REGEX_TERM_PATTERN = "#<([^>]++)>#";

	/**
	 * Regex pattern used for detecting optional terms in route rules.
	 * @var string
	 */
	const REGEX_TERM_OPT_PATTERN = "#\\(([^()]++)\\)#";

	/**
	 * Regex pattern used for generating rule patterns.
	 * @var string
	 */
	const REGEX_RULE_ESCAPE = '#[.\\+*?[^\\]${}=!|]#';

	/**
	 * Regex pattern used for generating rule patterns.
	 * @var string
	 */
	const REGEX_RULE_SEGMENT = '[^/.,;?\n]++';

	/**
	 * Array of registered routing rules.
	 * @var array
	 */
	protected $_routes = array();

	/**
	 * The base site url.
	 * @var string
	 */
	protected $_site_url = '';

	/**
	 * The base site uri (if necessary).
	 * @var string
	 */
	protected $_base_uri = '';

	/**
	 * Whether the Routing module should throw exceptions when required terms are not provided.
	 * @var bool
	 */
	protected $_throw_exception_for_req_terms_miss = true;

	/**
	 * Whether the Routing module should throw exceptions when constraints fail.
	 * @var bool
	 */
	protected $_throw_exception_for_constraint_term_miss = true;

	/**
	 * Current route being considered.
	 * @var string
	 */
	protected $_current_route = '';

	/**
	 * Current terms being considered.
	 * @var array
	 */
	protected $_current_terms = array();

	/**
	 * Exposed routes.
	 * @var array
	 */
	protected $_exposed = array();

	/**
	 * Whether secure routes will be enforced or not.
	 * @var bool
	 */
	protected $_secure = false;

	/**
	 * Routing constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);
	}

	/**
	 * Enforce secure routes.
	 * @return Routing
	 */
	public function secure(): Routing
	{
		$this->_secure = true;

		return $this;
	}

	/**
	 * Do not enforce secure routes.
	 * @return Routing
	 */
	public function unsecure(): Routing
	{
		$this->_secure = false;

		return $this;
	}

	/**
	 * If secure routes are being enforced or not.
	 * @return bool
	 */
	public function secured(): bool
	{
		return $this->_secure;
	}

	/**
	 * This method allows you to override route rules by using GET parameters to identify which
	 * controllers/methods should be executed.
	 * @return null|string
	 * @throws \Exception
	 */
	protected function _overrideListen(): ?string
	{
		$class = $_GET['controller'];
		$method = $_GET['action'];
		$terms = $_GET['terms'];

		if($class !== null && $method !== null)
		{
			if(class_exists($class) && method_exists($class, $method))
			{
				if($terms === null)
				{
					$arguments = array();
				}
				elseif(!is_array($terms))
				{
					$arguments = array($terms);
				}
				else
				{
					$arguments = $terms;
				}

				$classObj = new $class($this->_Base);

				$arguments = Arrays::insertValueAtPos($arguments, 1, array('controllerCalled'=> $class, 'actionCalled'=> $method));

				if(method_exists($classObj, 'before'))
				{
					call_user_func_array(
						array($classObj, 'before'),
						$arguments
					);
				}

				$return = call_user_func_array(
					array($classObj, $method),
					$arguments
				);

				$arguments['returnFromAction'] = $return;
				if(method_exists($classObj, 'after'))
				{
					call_user_func_array(
						array($classObj, 'after'),
						$arguments
					);
				}

				return $return;
			}
			else
			{
				throw new \Exception('Requested class or action does not exist.', Codes::ROUTING_ERROR_NO_CLASS_OR_ACTION);
			}
		}

		return null;
	}

	/**
	 * Parse the URI of the incoming request and see if it matches any routes.
	 * @return array
	 */
	protected function _listenParseURI(): array
	{
		$result_from_parse = array(
			'foundKey' => null,
			'terms'	=> array()
		);

		$base_uri = $this->stripBaseUri($this->getUri());

		if($base_uri === '/')
		{
			$base_uri = '';
		}

		foreach($this->_routes as $routeKey => $routeValues)
		{
			if(preg_match($routeValues[self::RULES], $base_uri, $terms))
			{
				$result_from_parse['foundKey'] = $routeKey;
				$result_from_parse['finalRouteValues'] = $routeValues;
			}
			else
			{
				continue;
			}

			## Clean up the terms
			if(is_array($terms) && !empty($terms))
			{
				foreach($terms as $offset => $term)
				{
					if(is_int($offset))
					{
						unset($terms[$offset]);
					}
					else
					{
						$result_from_parse['terms'][$offset] = $term;
					}
				}
			}

			## Save current values
			$this->_current_route = $routeKey;
			$this->_current_terms = $result_from_parse['terms'] ?? [];

			break;
		}

		return $result_from_parse;
	}

	/**
	 * Get current route being considered.
	 * @return string
	 */
	public function getCurrentRoute(): string
	{
		return $this->_current_route;
	}

	/**
	 * Get current terms being considered.
	 * @return array
	 */
	public function getCurrentTerms(): array
	{
		return $this->_current_terms;
	}

	/**
	 * Listen for incoming request and execute necessary methods to validate and respond.
	 * @param bool $allow_get_override
	 * @return null|string
	 * @throws \Exception
	 */
	public function listen(bool $allow_get_override = false): ?string
	{
		if($allow_get_override)
		{
			$this->_overrideListen();
		}

		if(!empty($this->_routes))
		{
			$result_from_parse = $this->_listenParseURI();

			if(!isset($result_from_parse['finalRouteValues']))
			{
				$result_from_parse['finalRouteValues'] = array();
			}

			if(!isset($result_from_parse['terms']['lang']))
			{
				$result_from_parse['terms'] = array('lang' => '')+$result_from_parse['terms'];
			}

			if($result_from_parse['foundKey'] !== null)
			{
				return $this->_listenFinalExecutions($result_from_parse);
			}
			else
			{
				throw new \Exception('No valid route found for this request.', Codes::ROUTING_ERROR_NO_ROUTE);
			}
		}
		else
		{
			throw new \Exception('No routes to match this request to.', Codes::ROUTING_ERROR_NO_ROUTE);
		}
	}

	/**
	 * Reroutes existing route with new terms.
	 * @param string $identifier
	 * @param array $terms
	 * @return null|string
	 * @throws \Exception
	 */
	public function reroute(string $identifier, array $terms = array()): ?string
	{
		if(!empty($this->_routes))
		{
			if(isset($this->_routes[$identifier]))
			{
				$final_route_values	= array(
					'foundKey' => $identifier,
					'finalRouteValues' => $this->_routes[$identifier],
					'terms' => is_array($terms)? $terms:array()
				);

				if(!isset($final_route_values['terms']['lang']))
				{
					$final_route_values['terms'] = array('lang' => '')+$final_route_values['terms'];
				}

				$this->_current_route = $identifier;
				$this->_current_terms = $final_route_values['terms'] ?? [];

				return $this->_listenFinalExecutions($final_route_values);
			}
			else
			{
				throw new \Exception('No valid route found for this request.', Codes::ROUTING_ERROR_NO_ROUTE);
			}

		}
		else
		{
			throw new \Exception('No routes to match this request to.', Codes::ROUTING_ERROR_NO_ROUTE);
		}
	}

	/**
	 * Generates regex rules for given pattern.
	 * @param string $pattern
	 * @param array|null $constraints
	 * @return string
	 */
	protected function _generateRules(string $pattern, ?array $constraints = null): string
	{
		## Strip both slashes
		$pattern = $this->stripBothSlashes($pattern);

		## Treat the pattern literal, except for keys and optional parts.
		$pattern = preg_replace(self::REGEX_RULE_ESCAPE, '\\\\$0', $pattern);

		## Make optional parts of the URI non-capturing and optional
		$pattern = str_replace(array('(', ')'), array('(?:', ')?'), $pattern);

		## Default regex for keys
		$pattern = str_replace(array('<', '>'), array('(?P<', '>'.self::REGEX_RULE_SEGMENT.')'), $pattern);

		## Constraints
		if(!empty($constraints))
		{
			foreach($constraints as $term => $constraint)
			{
				$pattern = str_replace('<'.$term.'>'.self::REGEX_RULE_SEGMENT, '<'.$term.'>'.$constraint,$pattern);
			}
		}

		if(substr($pattern, -1) !== '?')
		{
			$pattern .= '/';
		}

		return '#^'.$pattern.'$#uD';
	}

	/**
	 * Final procedures after a valid route is found, including loading up controller, calling before, action, and after methods.
	 * @param array $result_from_parse
	 * @return null|string
	 * @throws \Exception
	 */
	protected function _listenFinalExecutions(array $result_from_parse): ?string
	{
		if(class_exists($result_from_parse['finalRouteValues'][self::CONTROLLER]) && method_exists($result_from_parse['finalRouteValues'][self::CONTROLLER], $result_from_parse['finalRouteValues'][self::ACTION]))
		{
			$classObj = new $result_from_parse['finalRouteValues'][self::CONTROLLER]($this->_Base);

			$result_from_parse['terms'] = Arrays::insertValueAtPos($result_from_parse['terms'], 1, array('controllerCalled'=> $result_from_parse['finalRouteValues'][self::CONTROLLER]));

			$result_from_parse['terms'] = Arrays::insertValueAtPos($result_from_parse['terms'], 1, array('actionCalled'=> $result_from_parse['finalRouteValues'][self::ACTION]));

			if(method_exists($classObj, 'before'))
			{
				call_user_func_array(
					array($classObj, 'before'),
					$result_from_parse['terms']
				);
			}

			if(isset($result_from_parse['terms']['lang']))
			{
				unset($result_from_parse['terms']['lang']);
			}

			if(isset($result_from_parse['terms']['actionCalled']))
			{
				unset($result_from_parse['terms']['actionCalled']);
			}

			if(isset($result_from_parse['terms']['controllerCalled']))
			{
				unset($result_from_parse['terms']['controllerCalled']);
			}

			$return = call_user_func_array(
				array($classObj, $result_from_parse['finalRouteValues'][self::ACTION]),
				$result_from_parse['terms']
			);

			//$result_from_parse['terms']['returnFromAction'] = $return;

			$result_from_parse['terms'] = Arrays::insertValueAtPos($result_from_parse['terms'], 1, array('actionCalled'=> $result_from_parse['finalRouteValues'][self::ACTION]));

			$result_from_parse['terms'] = Arrays::insertValueAtPos($result_from_parse['terms'], 1, array('controllerCalled'=> $result_from_parse['finalRouteValues'][self::CONTROLLER]));

			if(method_exists($classObj, 'after'))
			{
				call_user_func_array(
					array($classObj, 'after'),
					$result_from_parse['terms']
				);
			}

			return $return;
		}
		else
		{
			throw new \Exception('Requested class or action does not exist.', Codes::ROUTING_ERROR_NO_CLASS_OR_ACTION);
		}
	}

	/**
	 * Strip the base URI from the URL.
	 * @param string $uri
	 * @return string
	 */
	public function stripBaseUri(string $uri): string
	{
		$uri = $this->stripBothSlashes($uri);

		if($this->_base_uri !== '')
		{
			$base_uri_exploded = explode('/', $uri);

			$uri_exploded = explode('/', $uri);

			foreach($base_uri_exploded as $key => $value)
			{
				if(isset($uri_exploded[$key]) && $uri_exploded[$key] === $value)
				{
					unset($uri_exploded[$key]);
				}
			}

			$uri = implode('/', $uri_exploded);
		}

		return $uri.'/';
	}

	/**
	 * Add a new routing rule.
	 * @param string $identifier
	 * @param string $pattern
	 * @param string $controller
	 * @param string $action
	 * @param array $constraints
	 * @param bool $expose
	 * @return Routing
	 */
	public function add(string $identifier, string $pattern, string $controller, string $action, array $constraints = array(), bool $expose = false): Routing
	{
		$this->_routes[$identifier] = array(
			self::PATTERN => $this->stripBothSlashes($pattern),
			self::CONTROLLER => $controller,
			self::ACTION => $action,
			self::CONSTRAINTS => $constraints,
			self::RULES => $this->_generateRules($pattern, $constraints),
		);

		if($expose)
		{
			$this->_exposed[$identifier] = $pattern;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSiteUrl(): string
	{
		return ($this->_secure)?str_replace('http://','https://', $this->_site_url):$this->_site_url;
	}

	/**
	 * Get base uri.
	 * @return string
	 */
	public function getBaseUri(): string
	{
		return $this->_base_uri;
	}

	/**
	 * Get exposed routes.
	 * @return array
	 */
	public function getExposed(): array
	{
		return $this->_exposed;
	}

	/**
	 * Get action for specified route.
	 * @param string $identifier
	 * @return null|string
	 */
	public function getAction(string $identifier): ?string
	{
		if(isset($this->_routes[$identifier]))
		{
			return $this->_routes[$identifier][self::ACTION];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get controller for specified route.
	 * @param string $identifier
	 * @return null|string
	 */
	public function getController(string $identifier): ?string
	{
		if(isset($this->_routes[$identifier]))
		{
			return $this->_routes[$identifier][self::CONTROLLER];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Remove a routing rule.
	 * @param string $identifier
	 * @return Routing
	 */
	public function remove(string $identifier): Routing
	{
		unset($this->_routes[$identifier]);

		return $this;
	}

	/**
	 * Set the site url.
	 * @param string $base_url
	 * @return Routing
	 */
	public function setSiteUrl(string $base_url): Routing
	{
		$this->_site_url = $this->stripLeadingSlash($this->addTrailingSlash($base_url));

		return $this;
	}

	/**
	 * Set the site uri.
	 * @param string $base_uri
	 * @return Routing
	 */
	public function setBaseUri(string $base_uri): Routing
	{
		$this->_base_uri = $this->stripLeadingSlash($this->addTrailingSlash($base_uri));

		return $this;
	}

	/**
	 * Enable exceptions for required term misses.
	 * @return Routing
	 */
	public function enableExceptionsForReqTermMiss(): Routing
	{
		$this->_throw_exception_for_req_terms_miss = true;

		return $this;
	}

	/**
	 * Disable exceptions for required term misses.
	 * @return Routing
	 */
	public function disableExceptionsForReqTermMiss(): Routing
	{
		$this->_throw_exception_for_req_terms_miss = false;

		return $this;
	}

	/**
	 * Enable exceptions for constraint failures.
	 * @return Routing
	 */
	public function enableExceptionsForConstraintTermMiss(): Routing
	{
		$this->_throw_exception_for_constraint_term_miss = true;

		return $this;
	}

	/**
	 * Disable exceptions for constraint failures.
	 * @return Routing
	 */
	public function disableExceptionsForConstraintTermMiss(): Routing
	{
		$this->_throw_exception_for_constraint_term_miss = false;

		return $this;
	}

	/**
	 * Strip trailing slash from provided url/uri.
	 * @param string $string
	 * @return string
	 */
	public function stripTrailingSlash(string $string): string
	{
		return (
		substr($string, -1) === '/'?
			substr($string, 0, -1):
			$string
		);
	}

	/**
	 * Add trailing slash from provided url/uri.
	 * @param string $string
	 * @return string
	 */
	public function addTrailingSlash(string $string): string
	{
		return (
		substr($string, -1) !== '/'?
			$string.'/':
			$string
		);
	}

	/**
	 * Strip leading slash from provided url/uri.
	 * @param string $string
	 * @return string
	 */
	public function stripLeadingSlash(string $string): string
	{
		return (
		substr($string, 0, 1) === '/'?
			substr($string, 1):
			$string
		);
	}

	/**
	 * Add leading slash from provided url/uri.
	 * @param string $string
	 * @return string
	 */
	public function addLeadingSlash(string $string): string
	{
		return (
		substr($string, 0, 1) !== '/'?
			'/'.$string:
			$string
		);
	}

	/**
	 * Strip leading and trailing slash from provided url/uri.
	 * @param string $string
	 * @return string
	 */
	public function stripBothSlashes(string $string): string
	{
		return $this->stripTrailingSlash(
			$this->stripLeadingSlash($string)
		);
	}

	/**
	 * Get a fully qualified route with filled-in terms (usually for views).
	 * @param string $identifier
	 * @param array $terms
	 * @param string|null $override_site_url
	 * @param bool $secure
	 * @return string
	 * @throws \Exception
	 */
	public function get(string $identifier, array $terms = array(), string $override_site_url = null, $secure = false): string
	{
		if(isset($this->_routes[$identifier]))
		{
			if($override_site_url === null)
			{
				$siteUrl = $this->_site_url;
			}
			else
			{
				$siteUrl = $override_site_url;
			}

			$siteUrl = $this->stripBaseUri($siteUrl);

			if($this->_secure || $secure)
			{
				$siteUrl = str_replace('http://','https://', $siteUrl);
			}

			if(isset($terms['lang']))
			{
				$terms['lang'] = $this->_Base->locale()->getShortLocaleId($terms['lang']);
			}
			elseif(isset($this->_routes[$identifier][self::CONSTRAINTS]['lang']))
			{
				$terms['lang'] = $this->_Base->locale()->getCurrentLocale();
			}

			$merged_pattern = $this->_mergeTermsWithPattern($terms, $this->_routes[$identifier][self::PATTERN], $this->_routes[$identifier][self::CONSTRAINTS]);

			return $this->addTrailingSlash($this->addTrailingSlash($siteUrl).$merged_pattern);
		}
		else
		{
			throw new \Exception('Route not found.', Codes::ROUTING_ERROR_NO_ROUTE);
		}
	}

	/**
	 * Fetch a raw routing rule.
	 * @param string $identifier
	 * @return string
	 * @throws \Exception
	 */
	public function fetch(string $identifier): string
	{
		if(isset($this->_routes[$identifier]))
		{
			return $this->_routes[$identifier];
		}
		else
		{
			throw new \Exception('Route not found.', Codes::ROUTING_ERROR_NO_ROUTE);
		}
	}

	/**
	 * @param array $terms
	 * @param string $pattern
	 * @param array $constraints
	 * @return string
	 * @throws \Exception
	 */
	protected function _mergeTermsWithPattern(array $terms, string $pattern, array $constraints = array()): string
	{
		$final_uri = $pattern;

		## Optional terms
		while(preg_match(self::REGEX_TERM_OPT_PATTERN, $final_uri, $match))
		{
			$patternPart = $match[0] ?? null;
			$patternTerm = $match[1] ?? null;

			if(preg_match(self::REGEX_TERM_PATTERN, $patternTerm, $match) !== false)
			{
				$term = $match[0] ?? null;
				$term_name = $match[1] ?? null;

				if(isset($terms[$term_name]) && $terms[$term_name] !== '')
				{
					$merged_term = htmlentities(str_replace($term, $terms[$term_name], $patternTerm));
				}
				else
				{
					$merged_term = null;
				}

				$final_uri = str_replace($patternPart, $merged_term, $final_uri);
			}
		}

		## Mandatory terms
		while(preg_match(self::REGEX_TERM_PATTERN, $final_uri, $match))
		{
			$term = $match[0] ?? '';
			$term_name = $match[1] ?? '';

			## Required term
			if(!isset($terms[$term_name]))
			{
				if($this->_throw_exception_for_req_terms_miss)
				{
					throw new \Exception('Term requirement failed for "'.$term_name.'".', Codes::ROUTING_ERROR_MISSING_REQ_TERMS);
				}
				else
				{
					$final_uri	= str_replace($term, htmlentities($term), $final_uri);

					continue;
				}
			}

			## Constraint
			if(!$this->_constraintCheck($constraints, $term, $terms[$term_name]))
			{
				if($this->_throw_exception_for_constraint_term_miss)
				{
					throw new \Exception('Term constraint rule failed for "'.$term_name.'". Value was "'.$terms[$term_name].'".', Codes::ROUTING_ERROR_REGEX_MATCH_ERROR);
				}
				else
				{
					$final_uri = str_replace($term, htmlentities($term), $final_uri);

					continue;
				}
			}

			$final_uri = str_replace($term, htmlentities((string)$terms[$term_name]), $final_uri);
		}

		return $final_uri;
	}

	/**
	 * Validates part/term with its intended constraint (if any).
	 * @param array $constraints
	 * @param string $term
	 * @param mixed $value
	 * @return bool
	 */
	protected function _constraintCheck(array $constraints, string $term, $value): bool
	{
		$term = str_replace(array('<','>','(',')'),'',$term);

		if(isset($constraints[$term]) && preg_match("#^".$constraints[$term]."$#", (string)$value) !== 1)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Get request uri if possible.
	 * @return string
	 * @throws \Exception
	 */
	public function getUri(): string
	{
		if(isset($_SERVER['REDIRECT_URL']))
		{
			return Strings::trim($_SERVER['REDIRECT_URL']);
		}
		elseif($_SERVER['REQUEST_URI'])
		{
			return Strings::trim($_SERVER['REQUEST_URI']);
		}
		else
		{
			throw new \Exception('Cannot get URI.', Codes::ROUTING_ERROR_NO_URI);
		}
	}

	/**
	 * @param string $file_file_path
	 * @return bool
	 * @throws \Exception
	 */
	public function bulkAddFromFile(string $file_file_path): bool
	{
		if(IO::isValidFile($file_file_path))
		{
			require $file_file_path;

			if(isset($ROUTES_CONFIG_ARRAY) && is_array($ROUTES_CONFIG_ARRAY) && !empty($ROUTES_CONFIG_ARRAY))
			{
				foreach($ROUTES_CONFIG_ARRAY as $identifier => $route)
				{
					if(is_array($route) && !empty($route))
					{
						if(isset($route['pattern']) && isset($route['controller']) && isset($route['action']) && isset($route['constraints']) && is_array($route['constraints']) && isset($route['expose']))
						{
							$this->add($identifier, $route['pattern'], $route['controller'], $route['action'], $route['constraints'], $route['expose']);
						}
						else
						{
							throw new \Exception('Invalid route "'.$identifier.'"!', Codes::ROUTING_ERROR_INVALID_ROUTE);
						}
					}
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			throw new \Exception('Routes file "'.$file_file_path.'" does not exist!', Codes::ROUTING_ERROR_FILE_NOT_FOUND);
		}

		return true;
	}

	/**
	 * Get a fully qualified route and swap a term with a new value
	 * @param string $term
	 * @param string $value
	 * @return string
	 * @throws \Exception
	 */
	public function getChange(string $term, string $value): string
	{
		try
		{
			return $this->get($this->_current_route, array_replace($this->_current_terms, array($term => $value)));
		}
		catch(\Exception $e)
		{
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * @return string
	 */
	public function getCurrentUrl()
	{
		$siteUrl = $this->stripTrailingSlash($this->_site_url).$this->_base_uri;
		if($this->secured())
		{
			$siteUrl = str_replace('http://','https://', $siteUrl);
		}
		return $siteUrl;
	}
}