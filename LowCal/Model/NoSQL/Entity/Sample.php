<?php
declare(strict_types=1);
/************************************************************************************
 ************************************************************************************
 * This is class is meant to be an example, only! Using the base Data Class, you can create
 * as many entity classes as you see fit. They will all inherit the base methods that allow
 * you to create JSON documents in your NoSQL database of choice.
 * This concept allows you to more easily control the structure of your data entities in a NoSQL
 * environment.
 ************************************************************************************
 ************************************************************************************/
namespace LowCal\Model\NoSQL\Entity;

use LowCal\Base;
use LowCal\Helper\Codes;
use LowCal\Helper\Config;
use LowCal\Helper\Format;
use LowCal\Model\NoSQL\Data;
use LowCal\Module\Db\Results;

/**
 * Class Sample
 * @package LowCal\Model\NoSQL\Entity
 */
class Sample extends Data implements \LowCal\Interfaces\Model\Data
{
	/**
	 * Request constructor.
	 * @param Base $LowCal
	 */
	function __construct(Base $LowCal)
	{
		parent::__construct($LowCal);
	}

	/**
	 * @return Sample
	 * @throws \Exception
	 */
	public function populate(): Sample
	{
		if(!empty($this->_id))
		{
			$select = $this->_LowCal->db()->interact()->getKV($this->getPrefixedId());

			if($select->getReturnedRows() > 0 && !$select->getErrorDetected())
			{
				$this->ingestJsonFromDatabase($select->getNextResult());
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
		if(empty($this->_id))
		{
			$this->setId($this->_LowCal->db()->interact()->getNextId(2));
		}

		if(!empty($this->_id))
		{
			return $this->_baseInsert();
		}
		else
		{
			throw new \Exception('Missing id.', Codes::DB_IDENTIFIER_MISSING);
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
			$query_beginning = "UPDATE ".Config::get('APP_DB_NAME')." USE KEYS '".$this->getPrefixedId()."' SET date_modified = '".Format::getTimestamp()."',";

			$query_string = "";
			$query_string_2 = "";
			$query_string_3 = "";

			foreach($this->_changes as $data_type => $changes)
			{
				$this->_baseChangeLoopUpdate($data_type, $changes, $query_string, $query_string_2, $query_string_3);
			}

			$query_end = " WHERE id = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($this->_id)." LIMIT 1";

			return $this->_basePostChangeUpdate($query_beginning, $query_string, $query_end, $query_string_2, $query_string_3);
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
		$Result = new Results($this->_LowCal);

		if(!empty($this->_id))
		{
			if(empty($this->_changes))
			{
				if($this->_baseFullDelete())
				{
					$Result->setAffectedRows(1);
				}
				else
				{
					$Result->setErrorDetected();
				}
			}
			else
			{
				$query_beginning = "UPDATE ".Config::get('APP_DB_NAME')." USE KEYS '".$this->getPrefixedId()."' ";

				$set_query_string = "date_modified = '".Format::getTimestamp()."',";
				$set_query_string_2 = "";
				$unset_query_string = "";

				foreach($this->_changes as $data_type => $changes)
				{
					$this->_baseChangeLoopDelete($data_type, $changes, $unset_query_string, $set_query_string);
				}

				$query_end = " WHERE id = ".$this->_LowCal->db()->sanitizeQueryValueNumeric($this->_id)." LIMIT 1";

				$Result = $this->_basePostChangeDelete($query_beginning, $set_query_string, $query_end, $unset_query_string, $set_query_string_2);
			}

			$this->_changes = array();
		}
		else
		{
			throw new \Exception('Cannot delete product if no Id provided.', Codes::DB_IDENTIFIER_MISSING);
		}

		return $Result;
	}

	/**
	 * @param bool $full_documents
	 * @param string $search_fields
	 * @param string $search_terms
	 * @param array $search_ids
	 * @param array $search_statuses
	 * @return Results
	 * @throws \Exception
	 */
	public function search(bool $full_documents = false, string $search_fields = '', string $search_terms = '', array $search_ids = array(), array $search_statuses = array()): Results
	{
		$query_beginning = "SELECT ".(!empty($full_documents)?Config::get('APP_DB_NAME').".*":(!empty($search_fields)?$this->_LowCal->db()->sanitizeQueryValueNonNumeric($search_fields):"id"))." FROM ".Config::get('APP_DB_NAME')." WHERE type = ".(2)." AND id >= 100000 ";
		$search_query_string = "";

		if(!empty($this->_changes) || !empty($search_terms) || !empty($search_ids))
		{
			if(!empty($search_ids))
			{
				$search_query_string .= "AND id IN [".Format::typeSafeImplodeForQuery($this->_LowCal->db()->sanitizeQueryValueTypeSafe($search_ids))."] ";
			}

			if(!empty($search_statuses))
			{
				$search_query_string .= "AND status IN [".Format::typeSafeImplodeForQuery($this->_LowCal->db()->sanitizeQueryValueTypeSafe($search_statuses))."] ";
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

		return $this->_basePostChangeSearch($query_beginning, $search_query_string);
	}
}