<?php
declare(strict_types=1);

namespace LowCal\Model\NoSQL;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Helper\Format;
use LowCal\Helper\Strings;
use LowCal\Model\Model;
use LowCal\Module\Db\Results;

/**
 * Class Data
 * A skeleton data model for NoSQL environments.
 * @package LowCal\Model\NoSQL
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
	 * Documents can have subtypes as well as super types. Subtypes are dynamic, while
	 * super types are hard-coded into the entities themselves.
	 * @var int
	 */
	protected $_subtype = 0;

	/**
	 * A document status flag.
	 * @var int
	 */
	protected $_status = 0;

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
	 * Set the document's subtype.
	 * @param int $subtype
	 * @return Data
	 */
	public function setSubType(int $subtype): self
	{
		if(!empty($subtype))
		{
			$this->_subtype = $subtype;

			if(!$this->_ignore_changes)
			{
				$this->_changes['int']['subtype'] = 'subtype';
			}
		}

		return $this;
	}

	/**
	 * Unset the document's subtype.
	 * @return Data
	 */
	public function unsetSubType(): self
	{
		$this->_subtype = 0;

		if(!$this->_ignore_changes)
		{
			unset($this->_changes['int']['subtype']);
		}

		return $this;
	}

	/**
	 * Get the document's subtype.
	 * @return int
	 */
	public function getSubType(): int
	{
		return $this->_subtype;
	}

	/**
	 * Set the document's status.
	 * @param int $status
	 * @return Data
	 */
	public function setStatus(int $status): self
	{
		if(!empty($status))
		{
			$this->_status = $status;

			if(!$this->_ignore_changes)
			{
				$this->_changes['int']['status'] = 'status';
			}
		}

		return $this;
	}

	/**
	 * Unset the document's status.
	 * @return Data
	 */
	public function unsetStatus(): self
	{
		$this->_status = 0;

		if(!$this->_ignore_changes)
		{
			unset($this->_changes['int']['status']);
		}

		return $this;
	}

	/**
	 * Get the document's status.
	 * @return int
	 */
	public function getStatus(): int
	{
		return $this->_status;
	}

	/**
	 * Get the document's prefixed id used as the document key in the NoSQL database.
	 * @return string
	 */
	public function getPrefixedId(): string
	{
		return Format::getPrefixedId(
			1,
			array(
				$this->_id
			)
		);
	}

	/**
	 * This method ingests a json array taken directly from the NoSQL database.
	 * @param array $full_json
	 */
	public function ingestJsonFromDatabase(array $full_json): void
	{
		$this->ignoreChanges();

		$this->_id = $full_json['id'] ?? 0;
		$this->_subtype = $full_json['subtype'] ?? null;
		$this->_status = $full_json['status'] ?? null;
		$this->_date_modified = $full_json['date_modified'] ?? '';
		$this->_date_created = $full_json['date_created'] ?? '';

		$this->dontIgnoreChanges();
	}

	/**
	 * This method expects a json represented as an array, from a request
	 * (used to populate the object for an update, delete or insert).
	 * @param array $full_json
	 * @param bool $for_delete
	 */
	public function ingestJsonFromRequest(array $full_json, bool $for_delete = false): void
	{
		if(isset($full_json['id']))
		{
			$this->setId((int)$full_json['id']);
		}

		if(isset($full_json['status']))
		{
			$this->setStatus((int)$full_json['status']);
		}

		if(isset($full_json['subtype']))
		{
			$this->setSubType((int)$full_json['subtype']);
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
			$query_string .= $change." = ".($this->$variable_var?"true":"false").",";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function updateArray(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;

			$query_string .= $change." = CASE WHEN ";
			$query_string .= $change." IS NOT VALUED THEN ".json_encode($this->_LowCal->db()->sanitizeQueryValueTypeSafe(array_values($this->$variable_var)));
			$query_string .= " ELSE ARRAY_PUT(".$change.", ".Format::typeSafeImplodeForQuery($this->$variable_var).") END,";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 * @param string $query_string_2
	 */
	protected function updateObject(array $changes, string &$query_string, string &$query_string_2): void
	{
		foreach($changes as $change)
		{
			$change = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($change);

			$variable_var = '_'.$change;

			$query_string .= $change." = CASE WHEN ".$change." IS NOT VALUED ";
			$query_string .= "THEN ".Format::typeSafeJSONValueForQuery($this->$variable_var, null, true, false)." ";
			$query_string .= "ELSE ".$change." END,";

			foreach($this->$variable_var as $key => $values)
			{
				$key = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($key);

				$query_string_2 .= $change.".`".$key."` = ".Format::typeSafeJSONValueForQuery($values, null, true, false).",";
			}
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 * @param string $query_string_2
	 */
	protected function updateObjectLocaleArray(array $changes, string &$query_string, string &$query_string_2): void
	{
		foreach($changes as $change)
		{
			$change = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($change);

			$variable_var = '_'.$change;

			$query_string .= $change." = CASE WHEN ".$change." IS NOT VALUED ";
			$query_string .= "THEN {} ";
			$query_string .= "ELSE ".$change." END,";

			foreach($this->$variable_var as $language_id => $values)
			{
				$language_id = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($language_id);
				$values = Format::typeSafeImplodeForQuery($values);

				$query_string_2 .= $change.".`".$language_id."` = CASE WHEN ".$change.".`".$language_id."` IS NOT VALUED ";
				$query_string_2 .= "THEN ARRAY_PUT([], ".$values.") ";
				$query_string_2 .= "ELSE ARRAY_PUT(".$change.".`".$language_id."`, ".$values.") END,";
			}
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 * @param string $query_string_2
	 */
	protected function updateObjectLocale(array $changes, string &$query_string, string &$query_string_2): void
	{
		foreach($changes as $change)
		{
			$change = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($change);

			$variable_var = '_'.$change;

			$query_string .= $change." = CASE WHEN ".$change." IS NOT VALUED ";
			$query_string .= "THEN {} ";
			$query_string .= "ELSE ".$change." END,";

			foreach($this->$variable_var as $language_id => $value)
			{
				$language_id = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($language_id);

				$query_string_2 .= $change.".`".$language_id."` = ".Format::typeSafeJSONValueForQuery($value).",";
			}
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $data_type
	 * @param string $query_string
	 * @param string $query_string_2
	 * @param string $query_string_3
	 */
	protected function updateArrayObject(array $changes, string $data_type, string &$query_string, string &$query_string_2, string &$query_string_3): void
	{
		foreach($changes as $change => $ids)
		{
			$change = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($change);

			$variable_var = '_'.$change;

			$query_string .= $change." = CASE WHEN ";
			$query_string .= $change." IS NOT VALUED THEN ".json_encode($this->_LowCal->db()->sanitizeQueryValueTypeSafe(array_values($this->$variable_var)));
			$query_string .= " ELSE ".$change." END,";

			$query_string_2 .= $change." = ARRAY_CONCAT(".$change.",";

			foreach($this->$variable_var as $id => $value)
			{
				$query_string_2 .= "CASE WHEN (";
				$query_string_2 .= "EVERY ".$change."_item IN ".$change;

				if($data_type === 'array_objects_id')
				{
					$query_string_2 .= " SATISFIES ".$change."_item.id <> ".$this->_LowCal->db()->sanitizeQueryValueNumeric($id)." END";
				}
				elseif($data_type === 'array_objects_type')
				{
					$query_string_2 .= " SATISFIES ".$change."_item.type <> ".$this->_LowCal->db()->sanitizeQueryValueNumeric($id)." END";
				}
				else
				{
					$query_string_2 .= " SATISFIES ".$change."_item.code <> \"".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($id)."\" END";
				}

				$query_string_2 .= ") ";
				$query_string_2 .= "THEN [".json_encode($this->_LowCal->db()->sanitizeQueryValueTypeSafe($value))."] ";
				$query_string_2 .= "ELSE [] END,";

				$query_string_3 .= $change."[ARRAY_POSITION(".$change.", ".$change."_set)] = ".json_encode($this->_LowCal->db()->sanitizeQueryValueTypeSafe($value));
				$query_string_3 .= " FOR ".$change."_set IN ".$change;

				if($data_type === 'array_objects_id')
				{
					$query_string_3 .= " WHEN ".$change."_set.id = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($id)." END,";
				}
				elseif($data_type === 'array_objects_type')
				{
					$query_string_3 .= " WHEN ".$change."_set.type = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($id)." END,";
				}
				else
				{
					$query_string_3 .= " WHEN ".$change."_set.code = \"".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($id)."\" END,";
				}
			}

			$query_string_2 = substr($query_string_2,0,-1)."),";
		}
	}

	/**
	 * Returns final document represented as a JSON string.
	 * @param bool $for_insert
	 * @return string
	 * @throws \Exception
	 */
	public function getFinalArrayString(bool $for_insert = false): string
	{
		if(($string = json_encode($this->getFinalArray($for_insert))) !== false)
		{
			return $string;
		}
		else
		{
			throw new \Exception('Unable to create proper json string for entity.', Codes::DB_FORMAT_ERROR_JSON);
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $unset_query_string
	 */
	protected function deleteRootField(array $changes, string &$unset_query_string): void
	{
		foreach($changes as $change)
		{
			$unset_query_string .= $change.",";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $set_query_string
	 */
	protected function deleteArray(array $changes, string &$set_query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;

			$set_query_string .= $change." = CASE WHEN ";
			$set_query_string .= $change." IS VALUED THEN ARRAY_REMOVE(".$change.", ".Format::typeSafeImplodeForQuery($this->$variable_var).") ";
			$set_query_string .= "ELSE [] END,";
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function deleteObject(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$change = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($change);

			$variable_var = '_'.$change;

			foreach($this->$variable_var as $key => $values)
			{
				$query_string .= $change.".`".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($key)."` = null,";
			}
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $set_query_string
	 */
	protected function deleteObjectLocaleArray(array $changes, string &$set_query_string): void
	{
		foreach($changes as $change)
		{
			$change = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($change);

			$variable_var = '_'.$change;

			foreach($this->$variable_var as $language_id => $values)
			{
				$language_id = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($language_id);

				$set_query_string .= $change.".`".$language_id."` = ARRAY ".$change."_item FOR ".$change."_item IN ".$change.".`".$language_id."` WHEN ";

				foreach($values as $value)
				{
					$set_query_string .= $change."_item <> ".Format::typeSafeJSONValueForQuery($value)." AND ";
				}

				$set_query_string = substr($set_query_string,0,-5)." END,";
			}
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $set_query_string
	 */
	protected function deleteObjectLocale(array $changes, string &$set_query_string): void
	{
		foreach($changes as $change)
		{
			$change = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($change);

			$variable_var = '_'.$change;

			foreach($this->$variable_var as $language_id => $value)
			{
				$set_query_string .= $change.".`".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($language_id)."` = null,";
			}
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param string $data_type
	 * @param array $changes
	 * @param string $set_query_string
	 */
	protected function deleteArrayObject(string $data_type, array $changes, string &$set_query_string): void
	{
		foreach($changes as $change => $ids)
		{
			$change = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($change);

			$variable_var = '_'.$change;

			foreach($this->$variable_var as $id => $value)
			{
				if($data_type === 'array_objects_id')
				{
					$set_query_string .= $change." = ARRAY ".$change."_item FOR ".$change."_item IN ".$change." WHEN ".$change."_item.id <> ".$this->_LowCal->db()->sanitizeQueryValueNumeric($id)." END,";
				}
				elseif($data_type === 'array_objects_type')
				{
					$set_query_string .= $change." = ARRAY ".$change."_item FOR ".$change."_item IN ".$change." WHEN ".$change."_item.type <> ".$this->_LowCal->db()->sanitizeQueryValueNumeric($id)." END,";
				}
				else
				{
					$set_query_string .= $change." = ARRAY ".$change."_item FOR ".$change."_item IN ".$change." WHEN ".$change."_item.code <> \"".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($id)."\" END,";
				}
			}
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
			$query_string .= " AND ".$change." = \"".$this->_LowCal->db()->sanitizeQueryValueNonNumeric(Format::getTimestamp(strtotime($this->$variable_var)))."\"";
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
			$query_string .= " AND ".$change." = \"".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($this->$variable_var)."\"";
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
			$query_string .= " AND ".$change." = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($this->$variable_var);
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
			$query_string .= " AND ".$change." = ".($this->$variable_var?"true":"false");
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function searchArray(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$variable_var = '_'.$change;

			foreach($this->$variable_var as $value)
			{
				$query_string .= " AND ANY item IN ".$change." SATISFIES item = ".Format::typeSafeJSONValueForQuery($value)." END";
			}
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function searchObject(array $changes, string &$query_string): void
	{
		foreach($changes as $change)
		{
			$change = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($change);

			$variable_var = '_'.$change;

			foreach($this->$variable_var as $key => $values)
			{
				$key = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($key);
				$values = $this->_LowCal->db()->sanitizeQueryValueTypeSafe($values);

				$query_string .= " AND ".$change.".`".$key."` = ".Format::typeSafeJSONValueForQuery($values, null, true, false)." ";
			}
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function searchObjectLocaleArray(array $changes, string &$query_string): void
	{
		foreach($changes as $language_id => $change)
		{
			foreach($change as $subchange)
			{
				$subchange = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($subchange);

				$variable_var = '_'.$subchange;

				$new_value = Format::typeSafeJSONValueForQuery($this->$variable_var, $language_id, false);

				$language_id = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($language_id);

				if(is_array($new_value))
				{
					$query_string .= " AND ANY item IN ".$subchange.".`".$language_id."` SATISFIES ";

					foreach($new_value as $value)
					{
						$query_string .= "item = ".Format::typeSafeJSONValueForQuery($value)." OR ";
					}

					$query_string = substr($query_string,0,-4)." END,";
				}
				else
				{
					$query_string .= " AND ANY item IN ".$subchange.".`".$language_id."` SATISFIES item = ".$new_value." END";
				}
			}
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $query_string
	 */
	protected function searchObjectLocale(array $changes, string &$query_string): void
	{
		foreach($changes as $language_id => $change)
		{
			foreach($change as $subchange)
			{
				$subchange = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($subchange);

				$variable_var = '_'.$subchange;

				$query_string .= " AND ".$subchange.".`".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($language_id)."` = ".$this->_LowCal->db()->sanitizeQueryValueTypeSafe($this->$variable_var[$language_id]);
			}
		}
	}

	/**
	 * Method made for constructing N1QL query fragments.
	 * @param array $changes
	 * @param string $data_type
	 * @param string $query_string
	 */
	protected function searchArrayObject(array $changes, string $data_type, string &$query_string): void
	{
		foreach($changes as $change => $ids)
		{
			$change = $this->_LowCal->db()->sanitizeQueryValueNonNumeric($change);

			$variable_var = '_'.$change;

			$query_string .= " AND ANY item IN ".$change." SATISFIES ";

			foreach($this->$variable_var as $id => $value)
			{
				if($data_type === 'array_objects_id')
				{
					$query_string .= " item.id = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($id)." OR ";
				}
				elseif($data_type === 'array_objects_type')
				{
					$query_string .= " item.type = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($id)." OR ";
				}
				else
				{
					$query_string .= " item.code = \"".$this->_LowCal->db()->sanitizeQueryValueNonNumeric($id)."\" OR ";
				}
			}

			$query_string = substr($query_string,0,-4)." END ";
		}
	}

	/**
	 * This is the base update method that brings all the fragments together.
	 * @param string $data_type
	 * @param array $changes
	 * @param string $query_string
	 * @param string $query_string_2
	 * @param string $query_string_3
	 * @throws \Exception
	 */
	protected function _baseChangeLoopUpdate(string $data_type, array $changes, string &$query_string, string &$query_string_2, string &$query_string_3): void
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
			case 'array':
				$this->updateArray($changes, $query_string);
				break;
			case 'object':
				$this->updateObject($changes, $query_string, $query_string_2);
				break;
			case 'object_locale':
				$this->updateObjectLocale($changes, $query_string, $query_string_2);
				break;
			case 'object_locale_array':
				$this->updateObjectLocaleArray($changes, $query_string, $query_string_2);
				break;
			case 'array_objects_id':
			case 'array_objects_code':
			case 'array_objects_type':
				$this->updateArrayObject($changes, $data_type, $query_string, $query_string_2, $query_string_3);
				break;
			case 'array_object_attributes':
				$this->updateArrayObjectAttributes($changes, $query_string, $query_string_2, $query_string_3);
				break;
		}
	}

	/**
	 * This is the base delete method that brings all the fragments together.
	 * @param string $data_type
	 * @param array $changes
	 * @param string $unset_query_string
	 * @param string $set_query_string
	 * @throws \Exception
	 */
	protected function _baseChangeLoopDelete(string $data_type, array $changes, string &$unset_query_string, string &$set_query_string): void
	{
		switch($data_type)
		{
			case 'date':
			case 'string':
			case 'int':
			case 'bool':
				$this->deleteRootField($changes, $unset_query_string);
				break;
			case 'array':
				$this->deleteArray($changes, $set_query_string);
				break;
			case 'object':
				$this->deleteObject($changes, $set_query_string);
				break;
			case 'object_locale':
				$this->deleteObjectLocale($changes, $set_query_string);
				break;
			case 'object_locale_array':
				$this->deleteObjectLocaleArray($changes, $set_query_string);
				break;
			case 'array_objects_id':
			case 'array_objects_code':
			case 'array_objects_type':
				$this->deleteArrayObject($data_type, $changes, $set_query_string);
				break;
			case 'array_object_attributes':
				$this->deleteArrayObjectAttributes($changes, $set_query_string);
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
			case 'array':
				$this->searchArray($changes, $search_query_string);
				break;
			case 'object':
				$this->searchObject($changes, $search_query_string);
				break;
			case 'object_locale':
				$this->searchObjectLocale($changes, $search_query_string);
				break;
			case 'object_locale_array':
				$this->searchObjectLocaleArray($changes, $search_query_string);
				break;
			case 'array_objects_id':
			case 'array_objects_code':
			case 'array_objects_type':
				$this->searchArrayObject($changes, $data_type, $search_query_string);
				break;
		}
	}

	/**
	 * Base insert method.
	 * @return Results
	 */
	protected function _baseInsert(): Results
	{
		$data = $this->getFinalArrayString(true);

		$Result = $this->_LowCal->db()->insert("INSERT INTO ".Config::get('APP_DB_NAME')." (KEY, VALUE) VALUES ('".$this->getPrefixedId()."', ".$data.") RETURNING id");

		$data = null;
		unset($data);

		return $Result;
	}

	/**
	 * Base update method.
	 * @param string $query_beginning
	 * @param string $query_string
	 * @param string $query_end
	 * @param string $query_string_2
	 * @param string $query_string_3
	 * @param 
	 * @return Results
	 */
	protected function _basePostChangeUpdate(string &$query_beginning, string &$query_string, string &$query_end, string &$query_string_2, string &$query_string_3): Results
	{
		$Result = $this->_LowCal->db()->update($query_beginning.substr($query_string,0, -1).$query_end);

		if(!$Result->getErrorDetected() && !empty($query_string_2))
		{
			$Result_2 = $this->_LowCal->db()->update($query_beginning.substr($query_string_2,0, -1).$query_end);
			$Result->setAffectedRows($Result->getAffectedRows()+$Result_2->getAffectedRows());
			$Result->setReturnedRows($Result->getReturnedRows()+$Result_2->getReturnedRows());

			if($Result_2->getErrorDetected())
			{
				$Result->setErrorDetected();
			}

			$Result_2->free();
			$Result_2 = null;
			unset($Result_2);
		}

		if(!$Result->getErrorDetected() && !empty($query_string_3))
		{
			$Result_3 = $this->_LowCal->db()->update($query_beginning.substr($query_string_3,0, -1).$query_end);
			$Result->setAffectedRows($Result->getAffectedRows()+$Result_3->getAffectedRows());
			$Result->setReturnedRows($Result->getReturnedRows()+$Result_3->getReturnedRows());

			if($Result_3->getErrorDetected())
			{
				$Result->setErrorDetected();
			}

			$Result_3->free();
			$Result_3 = null;
			unset($Result_3);
		}

		$this->_changes = array();

		return $Result;
	}

	/**
	 * Base full delete method.
	 * @param 
	 * @return bool
	 */
	protected function _baseFullDelete()
	{
		$result = $this->_LowCal->db()->interact()->deleteKV($this->getPrefixedId());

		return $result;
	}

	/**
	 * Base partial delete method.
	 * @param string $query_beginning
	 * @param string $set_query_string
	 * @param string $query_end
	 * @param string $unset_query_string
	 * @param string $set_query_string_2
	 * @param 
	 * @return Results
	 */
	protected function _basePostChangeDelete(string &$query_beginning, string &$set_query_string, string &$query_end, string &$unset_query_string, string &$set_query_string_2): Results
	{
		$Result = $this->_LowCal->db()->update($query_beginning." SET ".substr($set_query_string,0, -1)." ".(!empty($unset_query_string)?" UNSET ".substr($unset_query_string,0,-1):"").$query_end);

		if(!$Result->getErrorDetected() && !empty($set_query_string_2))
		{
			$Result_2 = $this->_LowCal->db()->update($query_beginning." SET date_modified = '".Format::getTimestamp()."',".substr($set_query_string_2,0, -1).$query_end);

			$Result->setAffectedRows($Result->getAffectedRows()+$Result_2->getAffectedRows());
			$Result->setReturnedRows($Result->getReturnedRows()+$Result_2->getReturnedRows());

			if($Result_2->getErrorDetected())
			{
				$Result->setErrorDetected();
			}

			$Result_2->free();
			$Result_2 = null;
			unset($Result_2);
		}

		return $Result;
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
	protected function _basePostChangeSearch(string &$query_beginning, string &$search_query_string, array $search_order = array(), ?int $search_limit = null, ?int $search_offset = null): Results
	{
		$this->_LowCal->db()->interact()->setQueryConsistencyRequestPlus();

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

			if(!empty($query_end))
			{
				$query_end = substr($query_end,0,-2);
			}
		}

		if(!empty($search_limit))
		{
			$query_end .= " LIMIT ".$this->_LowCal->db()->sanitizeQueryValueNumeric($search_limit);
		}

		if(!empty($search_offset))
		{
			$query_end .= " OFFSET ".$this->_LowCal->db()->sanitizeQueryValueNumeric($search_offset);
		}

		$Result =  $this->_LowCal->db()->select($query_beginning.$search_query_string.$query_end);

		$this->_LowCal->db()->interact()->setQueryConsistencyNotBound();

		return $Result;
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
	 * Get document represented as an array.
	 * @param bool $for_insert
	 * @return array
	 */
	public function getFinalArray(bool $for_insert = false): array
	{
		$final_array = array(
			"id" => $this->_id,
		);

		if($for_insert)
		{
			$final_array['date_created'] = Format::getTimestamp((!empty($this->_date_created)?strtotime($this->_date_created):null));
			$final_array['date_modified'] = Format::getTimestamp((!empty($this->_date_modified)?strtotime($this->_date_modified):null));
		}
		else
		{
			if(!empty($this->_date_created))
			{
				$final_array['date_created'] = Format::getTimestamp(strtotime($this->_date_created));
			}

			if(!empty($this->_date_modified))
			{
				$final_array['date_modified'] = Format::getTimestamp(strtotime($this->_date_modified));
			}
		}

		if(!empty($this->_status))
		{
			$final_array['status'] = $this->_status;
		}

		if(!empty($this->_subtype))
		{
			$final_array['subtype'] = $this->_subtype;
		}

		return $final_array;
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
}