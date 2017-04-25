<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Mysqli5;

use QCubed\Database\AbstractResult;

/**
 * Class to handle results sent by database upon querying
 * @was QMySqliDatabaseResult
 */
class MysqliResult extends AbstractResult {
	protected $objMySqliResult;
	protected $objDb;

	public function __construct(\mysqli_result $objResult, MysqliDatabase $objDb) {
		$this->objMySqliResult = $objResult;
		$this->objDb = $objDb;
	}

	public function FetchArray() {
		return $this->objMySqliResult->fetch_array();
	}

	public function FetchFields() {
		$objArrayToReturn = array();
		while ($objField = $this->objMySqliResult->fetch_field())
			array_push($objArrayToReturn, new MysqliField($objField, $this->objDb));
		return $objArrayToReturn;
	}

	public function FetchField() {
		if ($objField = $this->objMySqliResult->fetch_field()) {
			return new MysqliField($objField, $this->objDb);
		}
		return null;
	}

	public function FetchRow() {
		return $this->objMySqliResult->fetch_row();
	}

	public function MySqlFetchField() {
		return $this->objMySqliResult->fetch_field();
	}

	public function CountRows() {
		return $this->objMySqliResult->num_rows;
	}

	public function CountFields() {
		return $this->objMySqliResult->field_count;
	}

	public function Close() {
		$this->objMySqliResult->free();
	}

	public function GetNextRow() {
		$strColumnArray = $this->FetchArray();

		if ($strColumnArray)
			return new MysqliRow($strColumnArray);
		else
			return null;
	}

	public function GetRows() {
		$objDbRowArray = array();
		while ($objDbRow = $this->GetNextRow())
			array_push($objDbRowArray, $objDbRow);
		return $objDbRowArray;
	}
}

