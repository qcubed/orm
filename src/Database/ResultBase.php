<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database;

use QCubed\Exception\Caller;
use QCubed\Exception\InvalidCast;
use QCubed\ObjectBase;
use QCubed\Type;

/**
 */

/**
 * Class ResultBase
 *
 * Class to handle results sent by database upon querying
 *
 * @property string[] ColumnAliasArray;
 * @package QCubed\Database
 * @was QDatabaseResultBase
 */
abstract class ResultBase extends ObjectBase
{
    /** @var array The column alias array. This is needed for instantiating cursors. */
    protected $strColumnAliasArray;

    /**
     * Fetches one row as indexed (column=>value style) array from the result set
     * @abstract
     * @return mixed
     */
    abstract public function fetchArray();

    /**
     * Fetches one row as enumerated (with numerical indexes) array from the result set
     * @abstract
     * @return mixed
     */
    abstract public function fetchRow();

    abstract public function fetchField();

    abstract public function fetchFields();

    abstract public function countRows();

    abstract public function countFields();

    /**
     * @return RowBase
     */
    abstract public function getNextRow();

    abstract public function getRows();

    abstract public function close();

    /**
     * PHP magic method
     *
     * @param string $strName Property name
     *
     * @return mixed
     * @throws \Exception|Caller
     */
    public function __get($strName)
    {
        switch ($strName) {
            case 'ColumnAliasArray':
                return $this->strColumnAliasArray;
            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }

    public function __set($strName, $mixValue)
    {
        switch ($strName) {
            case 'ColumnAliasArray':
                try {
                    return ($this->strColumnAliasArray = Type::cast($mixValue, Type::ARRAY_TYPE));
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            default:
                try {
                    return parent::__set($strName, $mixValue);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }
}

