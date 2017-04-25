<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\PostgreSql;

use QCubed\Database\AbstractRow;
use QCubed\Database\FieldType;
use QCubed\QDateTime;
use QCubed\Type;

/**
 * Class for handling a single row from PostgreSQL database result set
 *
 * @was QPostgreSqlDatabaseRow
 */
class Row extends AbstractRow {
	/** @var string[] Column name value pairs for current result set */
	protected $strColumnArray;

	/**
	 * QPostgreSqlDatabaseRow constructor.
	 *
	 * @param string $strColumnArray
	 */
	public function __construct($strColumnArray) {
		$this->strColumnArray = $strColumnArray;
	}

	/**
	 * Gets the value of a column from a result row returned by the database
	 *
	 * @param string        $strColumnName Name of the column
	 * @param null|string 	$strColumnType Data type
	 *
	 * @return mixed
	 */
	public function GetColumn($strColumnName, $strColumnType = null) {
		if (!isset($this->strColumnArray[$strColumnName])) {
			return null;
		}
		$strColumnValue = $this->strColumnArray[$strColumnName];
		switch ($strColumnType) {
			case FieldType::Bit:
				// PostgreSQL returns 't' or 'f' for boolean fields
				if ($strColumnValue == 'f') {
					return false;
				} else {
					return ($strColumnValue) ? true : false;
				}

			case FieldType::Blob:
			case FieldType::Char:
			case FieldType::VarChar:
			case FieldType::Json: // JSON is basically String
				return Type::Cast($strColumnValue, Type::String);
			case FieldType::Date:
			case FieldType::DateTime:
			case FieldType::Time:
				return new QDateTime($strColumnValue);

			case FieldType::Float:
				return Type::Cast($strColumnValue, Type::Float);

			case FieldType::Integer:
				return Type::Cast($strColumnValue, Type::Integer);

			default:
				return $strColumnValue;
		}
	}

	/**
	 * Tells whether a particular column exists in a returned database row
	 *
	 * @param string $strColumnName Name of te column
	 *
	 * @return bool
	 */
	public function ColumnExists($strColumnName) {
		return array_key_exists($strColumnName, $this->strColumnArray);
	}

	/**
	 * @return string|string[]
	 */
	public function GetColumnNameArray() {
		return $this->strColumnArray;
	}

	/**
	 * Returns the boolean value corresponding to whatever a bit column returns. Postgres
	 * returns a 't' or 'f' (or null).
	 * @param bool|null $mixValue Value of the BIT column
	 * @return bool
	 */
	public function ResolveBooleanValue ($mixValue) {
		if ($mixValue == 'f') {
			return false;
		} elseif ($mixValue == 't') {
			return true;
		}
		else
			return null;
	}
}


