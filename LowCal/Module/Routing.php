<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Helper\Codes;

/**
 * Class Routing
 * @package LowCal\Module
 */
class Routing extends Module
{
	const PATTERN = 0;
	const CONTROLLER = 1;
	const ACTION = 2;
	const CONSTRAINTS = 3;
	const REGEX_TERM_PATTERN = "#(\\()?<[^>]++>(\\))?#";
	const REGEX_TERM_OPT_PATTERN = "#\\(<[^>]++>\\)#";

	/**
	 * @var array
	 */
	protected $_routes = array();

	/**
	 * @var string
	 */
	protected $_site_url = '';

	/**
	 * @var string
	 */
	protected $_base_uri = '';

	/**
	 * @var bool
	 */
	protected $_throw_exception_for_req_terms_miss = true;

	/**
	 * @var bool
	 */
	protected $_throw_exception_for_constraint_term_miss = true;

	/**
	 * @var string
	 */
	protected $_current_route = '';

	/**
	 * @var array
	 */
	protected $_current_terms = array();

	/**
	 * @var array
	 */
	protected $_exposed = array();

	/**
	 * @var bool
	 */
	protected $_secure = false;

	/**
	 * @return Routing
	 */
	public function secure(): Routing
	{
		$this->_secure = true;

		return $this;
	}

	/**
	 * @return Routing
	 */
	public function unsecure(): Routing
	{
		$this->_secure = false;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function secured(): bool
	{
		return $this->_secure;
	}

	/**
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
	 * @return string
	 */
	public function getCurrentRoute(): string
	{
		return $this->_current_route;
	}

	/**
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

				## Save current values
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
		if(!isset($this->_routes[$identifier]))
		{
			$this->set($identifier, $pattern, $controller, $action, $constraints);
		}

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
		return $this->_site_url;
	}

	/**
	 * @return string
	 */
	public function getBaseUri(): string
	{
		return $this->_base_uri;
	}

	/**
	 * @return array
	 */
	public function getExposed(): array
	{
		return $this->_exposed;
	}

	/**
	 * @param string $identifier
	 * @param string $pattern
	 * @param string $controller
	 * @param string $action
	 * @param array $constraints
	 * @return Routing
	 */
	public function set(string $identifier, string $pattern, string $controller, string $action, array $constraints): Routing
	{
		$this->_routes[$identifier] = array(
			self::PATTERN => $this->stripBothSlashes($pattern),
			self::CONTROLLER => $controller,
			self::ACTION => $action,
			self::CONSTRAINTS => $constraints,
		);

		return $this;
	}

	/**
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
	 * @param string $identifier
	 * @return Routing
	 */
	public function remove(string $identifier): Routing
	{
		unset($this->_routes[$identifier]);

		return $this;
	}

	/**
	 * @param string $base_url
	 * @return Routing
	 */
	public function setSiteUrl(string $base_url): Routing
	{
		$this->_site_url = $this->stripLeadingSlash($this->addTrailingSlash($base_url));

		return $this;
	}

	/**
	 * @param string $base_uri
	 * @return Routing
	 */
	public function setBaseUri(string $base_uri): Routing
	{
		$this->_base_uri = $this->stripLeadingSlash($this->addTrailingSlash($base_uri));

		return $this;
	}

	/**
	 * @return Routing
	 */
	public function enableExceptionsForReqTermMiss(): Routing
	{
		$this->_throw_exception_for_req_terms_miss = true;

		return $this;
	}

	/**
	 * @return Routing
	 */
	public function disableExceptionsForReqTermMiss(): Routing
	{
		$this->_throw_exception_for_req_terms_miss = false;

		return $this;
	}

	/**
	 * @return Routing
	 */
	public function enableExceptionsForConstraintTermMiss(): Routing
	{
		$this->_throw_exception_for_constraint_term_miss = true;

		return $this;
	}

	/**
	 * @return Routing
	 */
	public function disableExceptionsForConstraintTermMiss(): Routing
	{
		$this->_throw_exception_for_constraint_term_miss = false;

		return $this;
	}

	/**
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
	 * @param string $identifier
	 * @param array $terms
	 * @param string|null $override_site_url
	 * @param bool $secure
	 * @return string
	 * @throws \Exception
	 */
	public function get(string $identifier, array $terms = array(), string $override_site_url = null, $secure = false): string
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

		if(isset($this->_routes[$identifier]))
		{
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
	 * @param string $part_string
	 * @param string $term
	 * @return bool
	 */
	protected function _termMatchesPart(string $part_string, string $term): bool
	{
		return (strpos($part_string, '<'.$term.'>') !== false || strpos($part_string, '(<'.$term.'>)') !== false);
	}

	/**
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
	 * @return string
	 * @throws \Exception
	 */
	public function getUri(): string
	{
		if(isset($_SERVER['REDIRECT_URL']))
		{
			return trim($_SERVER['REDIRECT_URL']);
		}
		elseif($_SERVER['REQUEST_URI'])
		{
			return trim($_SERVER['REQUEST_URI']);
		}
		else
		{
			throw new \Exception('Cannot get URI.', Codes::ROUTING_ERROR_NO_URI);
		}
	}
}