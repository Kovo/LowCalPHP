<?php
/**
 * Copyright (c) 2017, Consultation Kevork Aghazarian
 * All rights reserved.
 */
declare(strict_types=1);

namespace LowCal\Model\SQL;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Format;
use LowCal\Helper\Strings;
use LowCal\Model\Model;
use LowCal\Module\Db\Results;

/**
 * Class Data
 * @package LowCal\Model\SQL
 */
class Data extends Model
{
	/**
	 * The atomic id of the document.
	 * @var null|int
	 */
	protected $_id = 0;

	/**
	 * A creation timestamp controlled by the class, only.
	 * @var string
	 */
	protected $_date_created = '';

	/**
	 * A modification timestamp controlled by the class, only.
	 * @var string
	 */
	protected $_date_modified = '';

	/**
	 * A document status flag.
	 * @var int
	 */
	protected $_status_id = 0;

	/**
	 * All changes done to this object are stored here. Once the update/delete methods are called,
	 * they read all the changes, construct the correct queries, and execute them.
	 * @var array
	 */
	protected $_changes = array();

	/**
	 * A flag to ignore incoming changes (or not).
	 * @var bool
	 */
	protected $_ignore_changes = false;

	/**
	 * Request constructor.
	 * @param Base $LowCal
	 */
	protected function __construct(Base $LowCal)
	{
		parent::__construct($LowCal);
	}

	/**
	 * Start to ignore changes.
	 * @return Data
	 */
	public function ignoreChanges(): self
	{
		$this->_ignore_changes = true;

		return $this;
	}

	/**
	 * Stop ignoring changes.
	 * @return Data
	 */
	public function dontIgnoreChanges(): self
	{
		$this->_ignore_changes = false;

		return $this;
	}

	/**
	 *  Clear any changes currently logged (will not remove values from the object itself).
	 * @return Data
	 */
	public function clearChanges(): self
	{
		$this->_changes = array();

		return $this;
	}

	/**
	 * Set the changes array (override it).
	 * @param array $changes
	 * @return Data
	 */
	public function setChanges(array $changes): self
	{
		$this->_changes = $changes;

		return $this;
	}

	/**
	 * Get the changes array.
	 * @return array
	 */
	public function getChanges(): array
	{
		return $this->_changes;
	}

	/**
	 * Sert the document id.
	 * @param int $id
	 * @return Data
	 */
	public function setId(int $id): self
	{
		$this->_id = $id;

		return $this;
	}

	/**
	 * Get the document id.
	 * @return int
	 */
	public function getId(): ?int
	{
		return $this->_id;
	}

	/**
	 * Set the date created (only useful when populating the object from the database).
	 * @param string $date_created
	 * @return Data
	 */
	public function setDateCreated(string $date_created): self
	{
		$this->_date_created = $date_created;

		return $this;
	}

	/**
	 * Get the date created.
	 * @return string
	 */
	public function getDateCreated(): string
	{
		return $this->_date_created;
	}

	/**
	 * Set the date modified (only useful when populating the object from the database).
	 * @param string $date_modified
	 * @return Data
	 */
	public function setDateModified(string $date_modified): self
	{
		$this->_date_modified = $date_modified;

		return $this;
	}

	/**
	 * Get the date modified.
	 * @return string
	 */
	public function getDateModified(): string
	{
		return $this->_date_modified;
	}

	/**
	 * Set the document's status.
	 * @param int $status
	 * @return Data
	 */
	public function setStatusId(int $status): self
	{
		if(!empty($status))
		{
			$this->_status_id = $status;

			if(!$this->_ignore_changes)
			{
				$this->_changes['int']['status_id'] = 'status_id';
			}
		}

		return $this;
	}

	/**
	 * Get the document's status.
	 * @return int
	 */
	public function getStatusId(): int
	{
		return $this->_status_id;
	}

	/**
	 * This method ingests a row array containing data for one entity.
	 * @param array $full_row
	 */
	public function ingestRowFromDatabase(array $full_row): void
	{
		$this->ignoreChanges();

		$this->_id = (isset($full_row['id'])?(int)$full_row['id']:0);
		$this->_status_id = (isset($full_row['status_id'])?(int)$full_row['status_id']:0);
		$this->_date_modified = $full_row['date_modified'] ?? '';
		$this->_date_created = $full_row['date_created'] ?? '';

		$this->dontIgnoreChanges();
	}

	/**
	 * @param array $changes
	 * @param string $columns_string
	 * @param string $values_string
	 */
	protected function insertDate(array $changes, string &$columns_string, string &$values_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$columns_string .= $change.',';
			$values_string .= (empty($this->$variable_var)?"null":"'".$this->_LowCal->db()->sanitizeQueryValueNonNumeric(Format::getTimestamp(strtotime($this->$variable_var)))."'").",";
		}
	}

	/**
	 * @param array $changes
	 * @param string $columns_string
	 * @param string $values_string
	 */
	protected function insertString(array $changes, string &$columns_string, string &$values_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$columns_string .= $change.',';
			$values_string .= "'".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($this->$variable_var)."',";
		}
	}

	/**
	 * @param array $changes
	 * @param string $columns_string
	 * @param string $values_string
	 */
	protected function insertInt(array $changes, string &$columns_string, string &$values_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$columns_string .= $change.',';
			$values_string .= $this->_LowCal->db()->sanitizeQueryValueNumeric($this->$variable_var).",";
		}
	}

	/**
	 * @param array $changes
	 * @param string $columns_string
	 * @param string $values_string
	 */
	protected function insertBool(array $changes, string &$columns_string, string &$values_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$columns_string .= $change.',';
			$values_string .= ($this->$variable_var?"1":"0").",";
		}
	}

	/**
	 * @param array $changes
	 * @param string $columns_string
	 * @param string $values_string
	 */
	protected function insertNull(array $changes, string &$columns_string, string &$values_string): void
	{
		foreach($changes as $change)
		{
			$columns_string .= $change.',';
			$values_string .= "NULL,";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function updateDate(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$query_string .= $change." = ".(empty($this->$variable_var)?"null":"'".$this->_LowCal->db()->sanitizeQueryValueNonNumeric(Format::getTimestamp(strtotime($this->$variable_var)))."'").",";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function updateString(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$query_string .= $change." = '".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($this->$variable_var)."',";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function updateInt(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$query_string .= $change." = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($this->$variable_var).",";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function updateBool(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$query_string .= $change." = ".($this->$variable_var?"1":"0").",";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function updateNull(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$query_string .= $change." = NULL,";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function searchDate(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$query_string .= " ".$change." = \"".$this->_LowCal->db()->sanitizeQueryValueNonNumeric(Format::getTimestamp(strtotime($this->$variable_var)))."\" AND ";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function searchString(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$query_string .= " ".$change." = \"".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($this->$variable_var)."\" AND ";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function searchInt(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$query_string .= " ".$change." = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($this->$variable_var)." AND ";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function searchBool(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;
			$query_string .= " ".$change." = ".($this->$variable_var?"1":"0")." AND ";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function searchNull(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$query_string .= " ".$change." IS NULL AND ";
		}
	}

	/**
	 * @param string $data_type
	 * @param array $changes
	 * @param string $column_string
	 * @param string $values_string
	 */
	protected function _baseChangeLoopInsert(string $data_type, array $changes, string &$column_string, string &$values_string): void
	{
		switch($data_type)
		{
			case 'date':
				$this->insertDate($changes, $column_string, $values_string);
				break;
			case 'string':
				$this->insertString($changes, $column_string, $values_string);
				break;
			case 'int':
				$this->insertInt($changes, $column_string, $values_string);
				break;
			case 'bool':
				$this->insertBool($changes, $column_string, $values_string);
				break;
			case 'null':
				$this->insertNull($changes, $column_string, $values_string);
				break;
		}
	}

	/**
	 * @param string $data_type
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function _baseChangeLoopUpdate(string $data_type, array $changes, string &$query_string): void
	{
		switch($data_type)
		{
			case 'date':
				$this->updateDate($changes, $query_string);
				break;
			case 'string':
				$this->updateString($changes, $query_string);
				break;
			case 'int':
				$this->updateInt($changes, $query_string);
				break;
			case 'bool':
				$this->updateBool($changes, $query_string);
				break;
			case 'null':
				$this->updateNull($changes, $query_string);
				break;
		}
	}

	/**
	 * This is the base search method that brings all the fragments together.
	 * @param string $data_type
	 * @param array $changes
	 * @param string $search_query_string
	 * @throws \Exception
	 */
	protected function _baseChangeLoopSearch(string $data_type, array $changes, string &$search_query_string): void
	{
		switch($data_type)
		{
			case 'date':
				$this->searchDate($changes, $search_query_string);
				break;
			case 'string':
				$this->searchString($changes, $search_query_string);
				break;
			case 'int':
				$this->searchInt($changes, $search_query_string);
				break;
			case 'bool':
				$this->searchBool($changes, $search_query_string);
				break;
			case 'null':
				$this->searchNull($changes, $search_query_string);
				break;
		}
	}

	/**
	 * @param array $search_terms
	 * @param string $search_query_string
	 */
	protected function _baseSearchTermLoopSearch(array $search_terms, string &$search_query_string): void
	{
		$ands = '';
		$ors = '';

		foreach($search_terms as $term_info)
		{
			switch($term_info[Codes::SEARCH_TERM_TYPE])
			{
				case Codes::SEARCH_TERM_TYPE_BETWEEN:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." BETWEEN '".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_VALUES][0])."' AND '".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_VALUES][1])."' ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_EQUAL:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." = '".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_VALUES])."' ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_NOTEQUAL:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." <> '".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_VALUES])."' ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_LIKE:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." LIKE '%".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_VALUES])."%' ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_LLIKE:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." LIKE '".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_VALUES])."%' ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_RLIKE:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." LIKE '%".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_VALUES])."' ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_LESSTHAN:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." < ".$this->_LowCal->db()->sanitizeQueryValueNumeric($term_info[Codes::SEARCH_TERM_VALUES])." ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_LESSTHANE:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." <= ".$this->_LowCal->db()->sanitizeQueryValueNumeric($term_info[Codes::SEARCH_TERM_VALUES])." ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_GREATERTHAN:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." > ".$this->_LowCal->db()->sanitizeQueryValueNumeric($term_info[Codes::SEARCH_TERM_VALUES])." ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_GREATERTHANE:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." >= ".$this->_LowCal->db()->sanitizeQueryValueNumeric($term_info[Codes::SEARCH_TERM_VALUES])." ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_IN:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." IN ( ";

					foreach($term_info[Codes::SEARCH_TERM_VALUES] as $value)
					{
						$query .= "'".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($value)."', ";
					}

					$query = substr($query,0,-2)." ";

					$query .= " ) ".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
				case Codes::SEARCH_TERM_TYPE_NOTIN:
					$query = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_FIELD])." NOT IN ( ";

					foreach($term_info[Codes::SEARCH_TERM_VALUES] as $value)
					{
						$query .= "'".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($value)."', ";
					}

					$query = substr($query,0,-2)." ";

					$query .= " ) ".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($term_info[Codes::SEARCH_TERM_ANDOR])." ";
					break;
			}

			if(isset($query))
			{
				if($term_info[Codes::SEARCH_TERM_ANDOR] == Codes::SEARCH_TERM_ANDOR_AND)
				{
					$ands .= $query;
				}
				else
				{
					$ors .= $query;
				}
			}
		}

		if(strlen($ands) > 0)
		{
			$search_query_string .= $ands;
		}

		if(strlen($ors) > 0)
		{
			$search_query_string .= "(".substr($ors,0,-3).") AND ";
		}
	}

	/**
	 * @param string $query_beginning
	 * @param string $column_string
	 * @param string $values_string
	 * @param string $query_end
	 * @return Results
	 */
	protected function _baseChangeInsert(string $query_beginning, string $column_string, string $values_string, string $query_end): Results
	{
		$Result = $this->_LowCal->db()->insert($query_beginning." (".substr($column_string,0, -1).") VALUES (".substr($values_string,0, -1).") ".$query_end);

		$this->_changes = array();

		return $Result;
	}

	/**
	 * @param string $query_beginning
	 * @param string $query_string
	 * @param string $query_end
	 * @return Results
	 */
	protected function _baseChangeUpdate(string $query_beginning, string $query_string, string $query_end): Results
	{
		$Result = $this->_LowCal->db()->update($query_beginning.substr($query_string,0, -1).$query_end);

		$this->_changes = array();

		return $Result;
	}

	/**
	 * @param string $query_string
	 * @return Results
	 */
	protected function _baseDelete(string $query_string): Results
	{
		return $this->_LowCal->db()->interact()->delete($query_string);
	}

	/**
	 * Base search method.
	 * @param string $query_beginning
	 * @param string $search_query_string
	 * @param array $search_order
	 * @param int|null $search_limit
	 * @param int|null $search_offset
	 * @return Results
	 */
	protected function _baseChangeSearch(string $query_beginning, string $search_query_string, array $search_order = array(), ?int $search_limit = null, ?int $search_offset = null): Results
	{
		$query_end = '';

		if(!empty($search_order))
		{
			$query_end .= " ORDER BY ";

			foreach($search_order as $instructions)
			{
				if(!isset($instructions['field']) || !isset($instructions['direction']) || empty(Strings::trim((string)$instructions['field'])) || empty(Strings::trim((string)$instructions['direction'])))
				{
					continue;
				}

				$query_end .= $this->_LowCal->db()->sanitizeQueryValueNonNumeric($instructions['field'])." ".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($instructions['direction']).", ";
			}

			if($query_end === " ORDER BY ")
			{
				$query_end = substr($query_end,0,-9);
			}
			elseif(!empty($query_end))
			{
				$query_end = substr($query_end,0,-2);
			}
		}

		if(!empty($search_limit) && !empty($search_offset))
		{
			$query_end .= " LIMIT ".$this->_LowCal->db()->sanitizeQueryValueNumeric($search_offset).", ".$this->_LowCal->db()->sanitizeQueryValueNumeric($search_limit);
		}
		elseif(!empty($search_limit))
		{
			$query_end .= " LIMIT ".$this->_LowCal->db()->sanitizeQueryValueNumeric($search_limit);
		}

		if(!empty($search_query_string))
		{
			$search_query_string = substr($search_query_string, 0, -4)." ";
		}
		else
		{
			$query_beginning = substr($query_beginning, 0, -6)." ";
		}

		$Result =  $this->_LowCal->db()->select($query_beginning.$search_query_string.$query_end);

		return $Result;
	}
}