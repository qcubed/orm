<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\PostgreSql;

use QCubed\Database\RowBase;
use QCubed\Database\FieldType;
use QCubed\QDateTime;
use QCubed\Type;

/**
 * Class for handling a single row from PostgreSQL database result set
 *
 * @was QPostgreSqlDatabaseRow
 */
class Row extends RowBase
{
    /** @var string[] Column name value pairs for current result set */
    protected $strColumnArray;

    /**
     * QPostgreSqlDatabaseRow constructor.
     *
     * @param string $strColumnArray
     */
    public function __construct($strColumnArray)
    {
        $this->strColumnArray = $strColumnArray;
    }

    /**
     * Gets the value of a column from a result row returned by the database
     *
     * @param string $strColumnName Name of the column
     * @param null|string $strColumnType Data type
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
                // PostgreSQL returns 't' or 'f' for boolean fields
                if ($strColumnValue == 'f') {
                    return false;
                } else {
                    return ($strColumnValue) ? true : false;
                }

            case FieldType::BLOB:
            case FieldType::CHAR:
            case FieldType::VAR_CHAR:
            case FieldType::JSON: // JSON is basically String
                return Type::cast($strColumnValue, Type::STRING);
            case FieldType::DATE:
            case FieldType::DATE_TIME:
            case FieldType::TIME:
                return new QDateTime($strColumnValue);

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

    /**
     * @return string|string[]
     */
    public function getColumnNameArray()
    {
        return $this->strColumnArray;
    }

    /**
     * Returns the boolean value corresponding to whatever a bit column returns. Postgres
     * returns a 't' or 'f' (or null).
     * @param bool|null $mixValue Value of the BIT column
     * @return bool
     */
    public function resolveBooleanValue($mixValue)
    {
        if ($mixValue == 'f') {
            return false;
        } elseif ($mixValue == 't') {
            return true;
        } else {
            return null;
        }
    }
}


