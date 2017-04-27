<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Mysqli5;

use QCubed\Database\AbstractRow;
use QCubed\Database\FieldType;
use QCubed\QDateTime;
use QCubed\Type;

/**
 *
 * @package DatabaseAdapters
 * @was QMySqliDatabaseRow
 */
class MysqliRow extends AbstractRow {
	protected $strColumnArray;

	public function __construct($strColumnArray) {
		$this->strColumnArray = $strColumnArray;
	}

	/**
	 * Gets the value of a column from a result row returned by the database
	 *
	 * @param string      	$strColumnName Name of the column
	 * @param null|string 	$strColumnType A FieldType string
	 *
	 * @return mixed
	 */
	public function getColumn($strColumnName, $strColumnType = null) {
		if (!isset($this->strColumnArray[$strColumnName])) {
			return null;
		}
		$strColumnValue = $this->strColumnArray[$strColumnName];

		switch ($strColumnType) {
			case FieldType::Bit:
				// Account for single bit value
				$chrBit = $strColumnValue;
				if ((strlen($chrBit) == 1) && (ord($chrBit) == 0))
					return false;

				// Otherwise, use PHP conditional to determine true or false
				return ($strColumnValue) ? true : false;

			case FieldType::Blob:
			case FieldType::Char:
			case FieldType::VarChar:
				return Type::cast($strColumnValue, Type::String);

			case FieldType::Date:
				return new QDateTime($strColumnValue, null, QDateTime::DateOnlyType);
			case FieldType::DateTime:
				return new QDateTime($strColumnValue, null, QDateTime::DateAndTimeType);
			case FieldType::Time:
				return new QDateTime($strColumnValue, null, QDateTime::TimeOnlyType);

			case FieldType::Float:
				return Type::cast($strColumnValue, Type::Float);

			case FieldType::Integer:
				return Type::cast($strColumnValue, Type::Integer);

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
	public function columnExists($strColumnName) {
		return array_key_exists($strColumnName, $this->strColumnArray);
	}

	public function getColumnNameArray() {
		return $this->strColumnArray;
	}
}

