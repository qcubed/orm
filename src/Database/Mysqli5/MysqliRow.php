<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Mysqli5;

use QCubed\Database\RowBase;
use QCubed\Database\FieldType;
use QCubed\QDateTime;
use QCubed\Type;

/**
 *
 * @package DatabaseAdapters
 * @was QMySqliDatabaseRow
 */
class MysqliRow extends RowBase
{
    protected $strColumnArray;

    public function __construct($strColumnArray)
    {
        $this->strColumnArray = $strColumnArray;
    }

    /**
     * Gets the value of a column from a result row returned by the database
     *
     * @param string $strColumnName Name of the column
     * @param null|string $strColumnType A FieldType string
     *
     * @return mixed
     */
    public function getColumn($strColumnName, $strColumnType = null)
    {
        if (!isset($this->strColumnArray[$strColumnName])) {
            return null;
        }
        $strColumnValue = $this->strColumnArray[$strColumnName];

        switch ($strColumnType) {
            case FieldType::BIT:
                // Account for single bit value
                $chrBit = $strColumnValue;
                if ((strlen($chrBit) == 1) && (ord($chrBit) == 0)) {
                    return false;
                }

                // Otherwise, use PHP conditional to determine true or false
                return ($strColumnValue) ? true : false;

            case FieldType::BLOB:
            case FieldType::CHAR:
            case FieldType::VAR_CHAR:
                return Type::cast($strColumnValue, Type::STRING);

            case FieldType::DATE:
                return new QDateTime($strColumnValue, null, QDateTime::DATE_ONLY_TYPE);
            case FieldType::DATE_TIME:
                return new QDateTime($strColumnValue, null, QDateTime::DATE_AND_TIME_TYPE);
            case FieldType::TIME:
                return new QDateTime($strColumnValue, null, QDateTime::TIME_ONLY_TYPE);

            case FieldType::FLOAT:
                return Type::cast($strColumnValue, Type::FLOAT);

            case FieldType::INTEGER:
                return Type::cast($strColumnValue, Type::INTEGER);

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
    public function columnExists($strColumnName)
    {
        return array_key_exists($strColumnName, $this->strColumnArray);
    }

    public function getColumnNameArray()
    {
        return $this->strColumnArray;
    }
}

