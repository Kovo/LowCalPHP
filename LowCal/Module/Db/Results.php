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
	 * @var array
	 */
	protected $_resultsArray = array();

	/**
	 * @var null|\mysqli_result|object
	 */
	protected $_resultsObject = null;

	/**
	 * @var null|int|string
	 */
	protected $_resultArrayPointer = null;

	/**
	 * @return array
	 */
	public function getResultsA(): array
	{
		return $this->_resultsArray;
	}

	/**
	 * @return object|\mysqli_result
	 */
	public function getResultsO()
	{
		return $this->_resultsObject;
	}

	/**
	 * @param string $key
	 * @return bool|mixed
	 */
	public function getSpecificResult(string $key)
	{
		if(array_key_exists($key, $this->_resultsArray))
		{
			return $this->_resultsArray[$key];
		}

		return false;
	}

	public function getNextResult()
	{

	}

	/**
	 * @param array $results
	 * @return Results
	 */
	public function setResultsA(array $results): Results
	{
		$this->_resultsArray = $results;

		return $this;
	}

	/**
	 * @param object $results
	 * @return Results
	 */
	public function setResultsO(object $results): Results
	{
		$this->_resultsObject = $results;

		return $this;
	}
}