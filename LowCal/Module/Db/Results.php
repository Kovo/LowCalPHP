<?php
declare(strict_types=1);
namespace LowCal\Module\Db;
use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Module\Module;

/**
 * Class Results
 * This class result holds all the information pertinent to a database query result.
 * @package LowCal\Module\Db
 */
class Results extends Module
{
	/**
	 * An array or mysqli_result object holding results.
	 * @var array|\mysqli_result
	 */
	protected $_results = null;

	/**
	 * Result type used for faster result iteration.
	 * @var null|string
	 */
	protected $_result_type = null;

	/**
	 * For results stored in an array, a flag used during iteration of the first value.
	 * @var bool
	 */
	protected $_result_array_traversed = false;

	/**
	 * If possible, show how many rows were affected.
	 * @var null|int
	 */
	protected $_affected_rows = null;

	/**
	 * If possible, show the insert id relevant to the result.
	 * @var null|int
	 */
	protected $_insert_id = null;

	/**
	 * If possible, show the number of returned rows.
	 * @var null|int
	 */
	protected $_returned_rows = null;

	/**
	 * @var bool
	 */
	protected $_error_detected = false;

	/**
	 * Results constructor.
	 * @param Base $Base
	 */
	function __construct(Base $Base)
	{
		parent::__construct($Base);
	}

	/**
	 * Results destructor.
	 */
	function __destruct()
	{
		$this->free();
	}

	/**
	 * Will free memory of result data.
	 */
	public function free(): void
	{
		if($this->_result_type === '\mysqli_result')
		{
			$this->_results->free();
		}

		$this->_results = null;
	}

	/**
	 * Set the number of affected rows.
	 * @param int|null $rows
	 * @return Results
	 */
	public function setAffectedRows(?int $rows): Results
	{
		$this->_affected_rows = $rows;

		return $this;
	}

	/**
	 * Set the last insert id.
	 * @param int|null $insert_id
	 * @return Results
	 */
	public function setInsertId(?int $insert_id): Results
	{
		$this->_insert_id = $insert_id;

		return $this;
	}

	/**
	 * Set the number of returned rows.
	 * @param int|null $returned_rows
	 * @return Results
	 */
	public function setReturnedRows(?int $returned_rows): Results
	{
		$this->_returned_rows = $returned_rows;

		return $this;
	}

	/**
	 * @return Results
	 */
	public function setErrorDetected(): Results
	{
		$this->_error_detected = true;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getErrorDetected(): bool
	{
		return $this->_error_detected;
	}

	/**
	 * Get the number of affected rows.
	 * @return int|null
	 */
	public function getAffectedRows(): ?int
	{
		return $this->_affected_rows;
	}

	/**
	 * Get the last insert id.
	 * @return int|null
	 */
	public function getInsertId(): ?int
	{
		return $this->_insert_id;
	}

	/**
	 * Get the number of returned rows.
	 * @return int|null
	 */
	public function getReturnedRows(): ?int
	{
		return $this->_returned_rows;
	}

	/**
	 * Set the results. Should be either an array, or a mysqli_result object.
	 * Generic objects will be converted into associative arrays.
	 * @param $results
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
			$this->_results = clone $results;

			$this->_result_type = '\mysqli_result';
		}
		elseif(is_object($results))
		{
			$this->_results = array(json_decode(json_encode($results), true));

			$this->_result_type = 'array';
		}
		else
		{
			throw new \Exception('Invalid result type provided. Expected array, object, or mysqli_result.', Codes::DB_INVALID_RESULT_TYPE);
		}

		return $this;
	}

	/**
	 * Return all results or the mysqli_result object.
	 * @return array|\mysqli_result|null
	 */
	public function getResults()
	{
		return $this->_results;
	}

	/**
	 * Get the next result from the result set. Results coming from arrays will always be wrapped in an array.
	 * Null will be returned when no results are left.
	 * @return array|null
	 * @throws \Exception
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

					$result = current($this->_results);

					return (is_array($result)?$result:array($result));
				}
				else
				{
					$result = next($this->_results);

					if($result === false)
					{
						return null;
					}

					return (is_array($result)?$result:array($result));
				}
			}
		}
		elseif($this->_result_type === '\mysqli_result')
		{
			return $this->_results->fetch_assoc();
		}
		else
		{
			throw new \Exception('Invalid result type provided. Expected array, object, or mysqli_result.', Codes::DB_INVALID_RESULT_TYPE);
		}

		return null;
	}
}