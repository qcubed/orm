<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database;

use QCubed\ObjectBase;


/**
 * Base class for all Database rows. Implemented by Database adapters
 * @package DatabaseAdapters
 * @was QDatabaseRowBase
 */
abstract class RowBase extends ObjectBase
{
    /**
     * Gets the value of a column from a result row returned by the database
     *
     * @param string $strColumnName Name of the column
     * @param null|string $strColumnType Data type
     *
     * @return mixed
     */
    abstract public function getColumn($strColumnName, $strColumnType = null);

    /**
     * Tells whether a particular column exists in a returned database row
     *
     * @param string $strColumnName Name of te column
     *
     * @return bool
     */
    abstract public function columnExists($strColumnName);

    abstract public function getColumnNameArray();

    /**
     * Returns the boolean value corresponding to whatever a boolean column returns. Some database types
     * return strings that represent the boolean values. Default is to use a PHP cast.
     * @param mixed $mixValue the value of the BIT column
     * @return bool
     */
    public function resolveBooleanValue($mixValue)
    {
        if ($mixValue === null) {
            return null;
        }
        return ((bool)$mixValue);
    }
}

