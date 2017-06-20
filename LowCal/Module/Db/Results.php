<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use LowCal\Module\Module;

/**
 * Class Results
 * @package LowCal\Module\Db
 */
class Results extends Module
{
	/**
	 * @var array|\mysqli_result
	 */
	protected $_results = null;

	/**
	 * @var null|string
	 */
	protected $_result_type = null;

	/**
	 * @var bool
	 */
	protected $_result_array_traversed = false;

	function __destruct()
	{
		$this->free();
	}

	public function free(): void
	{
		if($this->_result_type === '\mysqli_result')
		{
			$this->_results->free();
		}

		$this->_results = null;
	}

	/**
	 * @param \mysqli_result|array $results
	 * @return Results
	 * @throws \Exception
	 */
	public function setResults($results): Results
	{
		if(is_array($results))
		{
			$this->_results = $results;
			reset($this->_results);

			$this->_result_type = 'array';
		}
		elseif(is_object($results) && get_class($results) === '\mysqli_result')
		{
			$this->_results = $results;

			$this->_result_type = '\mysqli_result';
		}
		elseif(is_object($results))
		{
			$this->_results = json_decode(json_encode($results), true);

			$this->_result_type = 'array';
		}

		return $this;
	}

	/**
	 * @return array|\mysqli_result|null
	 */
	public function getResults()
	{
		return $this->_results;
	}

	/**
	 * @return array|null
	 */
	public function getNextResult(): ?array
	{
		if($this->_result_type === 'array')
		{
			if(key($this->_results) !== null)
			{
				if(!$this->_result_array_traversed)
				{
					$this->_result_array_traversed = true;

					return array(current($this->_results));
				}
				else
				{
					return array(next($this->_results));
				}
			}
		}
		elseif($this->_result_type === '\mysqli_result')
		{
			return $this->_results->fetch_assoc();
		}

		return null;
	}
}