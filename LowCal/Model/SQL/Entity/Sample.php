<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);
/************************************************************************************
 ************************************************************************************
 * This is class is meant to be an example, only! Using the base Data Class, you can create
 * as many entity classes as you see fit. They will all inherit the base methods that allow
 * you to manipulate data in your SQL database of choice.
 * This concept allows you to more easily control the structure of your data entities in a traditional SQL
 * environment.
 ************************************************************************************
 ************************************************************************************/
namespace LowCal\Model\SQL\Entity;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Format;
use LowCal\Model\SQL\Data;
use LowCal\Module\Db\Results;

/**
 * Class Sample
 * @package Model\SQL\Entity
 */
class Sample extends Data implements \LowCal\Interfaces\Model\SQL\Data
{
	/**
	 * @var string
	 */
	protected $_uid = '';

	/**
	 * @var string
	 */
	protected $_access_code = '';

	/**
	 * Request constructor.
	 * @param Base $LowCal
	 */
	function __construct(Base $LowCal)
	{
		parent::__construct($LowCal);
	}

	/**
	 * @param string $uid
	 * @return Sample
	 */
	public function setUID(string $uid): self
	{
		$this->_uid = $uid;

		if(!$this->_ignore_changes)
		{
			$this->_changes['string']['uid'] = 'uid';
		}

		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getUID(): ?string
	{
		return $this->_uid;
	}

	/**
	 * @param string $access_code
	 * @return Sample
	 */
	public function setAccessCode(string $access_code): self
	{
		$this->_access_code = $access_code;

		if(!$this->_ignore_changes)
		{
			$this->_changes['string']['access_code'] = 'access_code';
		}

		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getAccessCode(): ?string
	{
		return $this->_access_code;
	}

	/**
	 * This method ingests a row array containing data for one entity.
	 * @param array $full_row
	 */
	public function ingestRowFromDatabase(array $full_row): void
	{
		parent::ingestRowFromDatabase($full_row);

		$this->ignoreChanges();

		$this->_uid = $full_row['uid'] ?? '';
		$this->_access_code = $full_row['access_code'] ?? '';

		$this->dontIgnoreChanges();
	}

	/**
	 * @return Sample
	 * @throws \Exception
	 */
	public function populate(): Sample
	{
		if(!empty($this->_id))
		{
			$select = $this->_LowCal->db()->interact()->select("SELECT id, uid, access_code, date_added, date_modified FROM sample WHERE id = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($this->_id)." LIMIT 1");

			if($select->getReturnedRows() > 0 && !$select->getErrorDetected())
			{
				$this->ingestRowFromDatabase($select->getNextResult());
			}
			else
			{
				throw new \Exception('Could not populate Sample entity.', Codes::DB_DATA_NOT_FOUND);
			}
		}
		else
		{
			throw new \Exception('Cannot populate Sample object without an Id.', Codes::DB_IDENTIFIER_MISSING);
		}

		return $this;
	}

	/**
	 * @return Results
	 * @throws \Exception
	 */
	public function insert(): Results
	{
		if(!empty($this->_changes))
		{
			$query_beginning = "INSERT INTO sample ";

			$column_string = "";
			$values_string = "";

			foreach($this->_changes as $data_type => $changes)
			{
				$this->_baseChangeLoopInsert($data_type, $changes, $column_string, $values_string);
			}

			return $this->_baseChangeInsert($query_beginning, $column_string, $values_string, "");
		}
		else
		{
			throw new \Exception('Cannot insert Sample if no changes provided.', Codes::DB_DATA_NOT_FOUND);
		}
	}

	/**
	 * @return Results
	 * @throws \Exception
	 */
	public function update(): Results
	{
		if(!empty($this->_changes) && !empty($this->_id))
		{
			$query_beginning = "UPDATE sample SET ";

			$query_string = "";

			foreach($this->_changes as $data_type => $changes)
			{
				$this->_baseChangeLoopUpdate($data_type, $changes, $query_string);
			}

			$query_end = " WHERE id = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($this->_id)." LIMIT 1";

			return $this->_baseChangeUpdate($query_beginning, $query_string, $query_end);
		}
		else
		{
			throw new \Exception('Cannot update Sample if no Id provided.', Codes::DB_IDENTIFIER_MISSING);
		}
	}

	/**
	 * @return Results
	 * @throws \Exception
	 */
	public function delete(): Results
	{
		if(!empty($this->_id))
		{
			return $this->_baseDelete("DELETE FROM sample WHERE id = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($this->_id)." LIMIT 1");
		}
		else
		{
			throw new \Exception('Cannot delete Sample if no Id provided.', Codes::DB_IDENTIFIER_MISSING);
		}
	}

	/**
	 * @param bool $full_rows
	 * @param string $search_fields
	 * @param string $search_terms
	 * @param array $search_ids
	 * @param array $search_statuses
	 * @return Results
	 * @throws \Exception
	 */
	public function search(bool $full_rows = false, string $search_fields = '', string $search_terms = '', array $search_ids = array(), array $search_statuses = array()): Results
	{
		$query_beginning = "SELECT ".(!empty($full_rows)?"*":(!empty($search_fields)?$this->_LowCal->db()->sanitizeQueryValueNonNumeric($search_fields):"id"))." FROM sample WHERE ";
		$search_query_string = "";

		if(!empty($this->_changes) || !empty($search_terms) || !empty($search_ids))
		{
			if(!empty($search_ids))
			{
				$search_query_string .= "AND id IN (".Format::typeSafeImplodeForQuery($this->_LowCal->db()->sanitizeQueryValueTypeSafe($search_ids)).") ";
			}

			if(!empty($search_statuses))
			{
				$search_query_string .= "AND status_id IN (".Format::typeSafeImplodeForQuery($this->_LowCal->db()->sanitizeQueryValueTypeSafe($search_statuses)).") ";
			}

			foreach($this->_changes as $data_type => $changes)
			{
				$this->_baseChangeLoopSearch($data_type, $changes, $search_query_string);
			}
		}
		else
		{
			throw new \Exception('Cannot search for Sample if no instructions provided.', Codes::DB_IDENTIFIER_MISSING);
		}

		return $this->_baseChangeSearch($query_beginning, $search_query_string);
	}
}