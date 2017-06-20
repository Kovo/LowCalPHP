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

	/**
	 * @var null|int
	 */
	protected $_affected_rows = null;

	/**
	 * @var null|int
	 */
	protected $_insert_id = null;

	/**
	 * @var null|int
	 */
	protected $_returned_rows = null;

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
	 * @param int|null $rows
	 * @return Results
	 */
	public function setAffectedRows(?int $rows): Results
	{
		$this->_affected_rows = $rows;

		return $this;
	}

	/**
	 * @param int|null $insert_id
	 * @return Results
	 */
	public function setInsertId(?int $insert_id): Results
	{
		$this->_insert_id = $insert_id;

		return $this;
	}

	/**
	 * @param int|null $returned_rows
	 * @return Results
	 */
	public function setReturnedRows(?int $returned_rows): Results
	{
		$this->_returned_rows = $returned_rows;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getAffectedRows(): ?int
	{
		return $this->_affected_rows;
	}

	/**
	 * @return int|null
	 */
	public function getInsertId(): ?int
	{
		return $this->_insert_id;
	}

	/**
	 * @return int|null
	 */
	public function getReturnedRows(): ?int
	{
		return $this->_returned_rows;
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
			$this->_results = clone $results;

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