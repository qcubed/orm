<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\PostgreSql;

use QCubed\Database\AbstractResult;

/**
 * Class to handle results sent by database upon querying
 * @package DatabaseAdapters
 * @was QPostgreSqlDatabaseResult
 */
class Result extends AbstractResult {
	protected $objPgSqlResult;
	protected $objDb;

	/**
	 * QPostgreSqlDatabaseResult constructor.
	 *
	 * @param                     $objResult
	 * @param Database $objDb
	 */
	public function __construct($objResult, Database $objDb) {
		$this->objPgSqlResult = $objResult;
		$this->objDb = $objDb;
	}

	/**
	 * Fetch result (single result) as array
	 *
	 * @return array
	 */
	public function FetchArray() {
		return pg_fetch_array($this->objPgSqlResult);
	}

	/**
	 * Fetch fields (currently just null)
	 *
	 * @return null
	 */
	public function FetchFields() {
		return null;  // Not implemented
	}

	/**
	 * Fetch field (currently just null)
	 *
	 * @return null
	 */
	public function FetchField() {
		return null;  // Not implemented
	}

	/**
	 * Fetch row
	 *
	 * @return array
	 */
	public function FetchRow() {
		return pg_fetch_row($this->objPgSqlResult);
	}

	/**
	 * Return number of rows in result
	 *
	 * @return int
	 */
	public function CountRows() {
		return pg_num_rows($this->objPgSqlResult);
	}

	/**
	 * Return number of fields in a result
	 *
	 * @return int
	 */
	public function CountFields() {
		return pg_num_fields($this->objPgSqlResult);
	}

	/**
	 * Free the memory. Connection closes when script ends
	 */
	public function Close() {
		pg_free_result($this->objPgSqlResult);
	}

	/**
	 * @return null|Row
	 */
	public function GetNextRow() {
		$strColumnArray = $this->FetchArray();

		if ($strColumnArray)
			return new Row($strColumnArray);
		else
			return null;
	}

	/**
	 * Returns all results in the result set as array
	 *
	 * @return array
	 */
	public function GetRows() {
		$objDbRowArray = array();
		while ($objDbRow = $this->GetNextRow())
			array_push($objDbRowArray, $objDbRow);
		return $objDbRowArray;
	}
}

