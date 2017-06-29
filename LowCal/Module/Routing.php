<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Helper\Codes;
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
	 * Regex pattern used for detecting terms in route rules.
	 * @var string
	 */
	const REGEX_TERM_PATTERN = "#(\\()?<[^>]++>(\\))?#";

	/**
	 * Rehex pattern used for detecting optional terms in route rules.
	 * @var string
	 */
	const REGEX_TERM_OPT_PATTERN = "#\\(<[^>]++>\\)#";

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

				$arguments = Arrays::insertValueAtPos($arguments, 1, array('actionCalled'=> $method));

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
		$result_from_parse = array();
		$uri_parts = explode('/', $this->stripBaseUri($this->getUri()));
		$uri_parts_count = count($uri_parts);
		$result_from_parse['foundKey'] = null;
		$result_from_parse['terms'] = array();

		foreach($this->_routes as $route_key => $route_values)
		{
			$pattern_parts = explode('/', $route_values[self::PATTERN]);
			$broken = false;
			$uri_hits = 0;

			foreach($pattern_parts as $order => $part_string)
			{
				if(!$this->_isPartATerm($part_string))
				{
					if(!isset($uri_parts[$order]) || $uri_parts[$order] !== $part_string || ($uri_parts_count > 1 && $uri_parts[$order] == ''))
					{
						$broken = true;
						break;
					}

					$uri_hits++;
				}
				else
				{
					if(!$this->_isPartAnOptionalTerm($part_string))
					{
						if(!isset($uri_parts[$order]))
						{
							$broken = true;
							break;
						}
						else
						{
							if(!$this->_constraintCheck($route_values[self::CONSTRAINTS],$part_string,$uri_parts[$order]) || $uri_parts[$order] == '')
							{
								$broken = true;
								break;
							}
							else
							{
								$result_from_parse['terms'][str_replace(array('(',')','<','>'), '', $part_string)] = $uri_parts[$order];
							}

							$uri_hits++;
						}
					}
					else
					{
						if(isset($uri_parts[$order]))
						{
							if(!empty($uri_parts[$order]) && !$this->_constraintCheck($route_values[self::CONSTRAINTS],$part_string,$uri_parts[$order]))
							{
								$broken = true;
								break;
							}
							else
							{
								$result_from_parse['terms'][str_replace(array('(',')','<','>'), '', $part_string)] = $uri_parts[$order];
							}

							$uri_hits++;
						}
					}
				}
			}

			if(!$broken && $uri_hits == $uri_parts_count)
			{
				$result_from_parse['foundKey'] = $route_key;
				$result_from_parse['finalRouteValues'] = $route_values;
				$this->_current_route = $route_key;

				break;
			}
			else
			{
				$result_from_parse['terms'] = array();
			}
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
				$this->_current_terms = (isset($final_route_values['terms'])?$final_route_values['terms']:array());

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

			$result_from_parse['terms']['returnFromAction'] = $return;
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
		$base_uri = $this->stripTrailingSlash($this->_base_uri);

		if($this->_base_uri !== '')
		{
			$base_uri_exploded = explode('/', $base_uri);

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

		return $uri;
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
		);

		if($expose)
		{
			$this->_exposed[$identifier] = $pattern;
		}

		return $this;
	}

	/**
	 * Get site url.
	 * @return string
	 */
	public function getSiteUrl(): string
	{
		return $this->_site_url;
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
		$string = $this->stripTrailingSlash($string);
		$string = $this->stripLeadingSlash($string);

		return $string;
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

			if(($this->_secure || $secure) && $secure !== false)
			{
				$siteUrl = str_replace('http://','https://', $siteUrl);
			}

			if(isset($this->_routes[$identifier][self::CONSTRAINTS]['lang']))
			{
				$terms['lang'] = $this->_Base->locale()->getCurrentLocale();
			}

			$merged_pattern = $this->_mergeTermsWithPattern($terms, $this->_routes[$identifier][self::PATTERN], $this->_routes[$identifier][self::CONSTRAINTS]);

			return $this->addTrailingSlash($siteUrl.'/'.$merged_pattern);
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
	 * Checks to see if detected part is a term or not.
	 * @param string $part_string
	 * @return bool
	 */
	protected function _isPartATerm(string $part_string): bool
	{
		if(preg_match(self::REGEX_TERM_PATTERN, $part_string) !== 1)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Checks to see if detected part is an optional term or not.
	 * @param string $part_string
	 * @return bool
	 */
	protected function _isPartAnOptionalTerm(string $part_string): bool
	{
		if(preg_match(self::REGEX_TERM_OPT_PATTERN, $part_string) !== 1)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Merges terms into route pattern if they are valid.
	 * @param array $terms
	 * @param string $pattern
	 * @param array $constraints
	 * @return string
	 * @throws \Exception
	 */
	protected function _mergeTermsWithPattern(array $terms, string $pattern, array $constraints = array()): string
	{
		if(count($terms) === 0)
		{
			return $pattern;
		}
		else
		{
			$pattern_parts = explode('/', $pattern);
			$final_uri = '';

			foreach($pattern_parts as $part_string)
			{
				if(!$this->_isPartATerm($part_string))
				{
					$final_uri .= $part_string.'/';
				}
				else
				{
					$found = false;

					foreach($terms as $term => $value)
					{
						if($this->_termMatchesPart($part_string, $term))
						{
							if(!$this->_constraintCheck($constraints, $term, $value))
							{
								if($this->_throw_exception_for_constraint_term_miss)
								{
									throw new \Exception('Term constraint rule failed for "'.$term.'". Value was "'.$value.'".', Codes::ROUTING_ERROR_REGEX_MATCH_ERROR);
								}
								else
								{
									$final_uri .= htmlentities($part_string).'/';

									$found = true;

									break;
								}
							}

							$final_uri .= $value.'/';

							$found = true;

							break;
						}
					}

					if(!$found && !$this->_isPartAnOptionalTerm($part_string))
					{
						if($this->_throw_exception_for_req_terms_miss)
						{
							throw new \Exception('Could not fulfill required terms.', Codes::ROUTING_ERROR_MISSING_REQ_TERMS);
						}
						else
						{
							$final_uri .= htmlentities($part_string).'/';
						}
					}
				}
			}

			return $final_uri;
		}
	}

	/**
	 * Checks to see if provided term matches part of url/uri.
	 * @param string $part_string
	 * @param string $term
	 * @return bool
	 */
	protected function _termMatchesPart(string $part_string, string $term): bool
	{
		return (strpos($part_string, '<'.$term.'>') !== false || strpos($part_string, '(<'.$term.'>)') !== false);
	}

	/**
	 * Validates part/term with its intended constraint (if any).
	 * @param array $constraints
	 * @param string $term
	 * @param string $value
	 * @return bool
	 */
	protected function _constraintCheck(array $constraints, string $term, string $value): bool
	{
		$term = str_replace(array('<','>','(',')'),'',$term);

		if(isset($constraints[$term]) && preg_match("#^".$constraints[$term]."$#", $value) !== 1)
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
}