<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Mysqli5;

use QCubed\Database\ResultBase;

/**
 * Class to handle results sent by database upon querying
 * @was QMySqliDatabaseResult
 */
class MysqliResult extends ResultBase
{
    protected $objMySqliResult;
    protected $objDb;

    public function __construct(\mysqli_result $objResult, MysqliDatabase $objDb)
    {
        $this->objMySqliResult = $objResult;
        $this->objDb = $objDb;
    }

    public function fetchArray()
    {
        return $this->objMySqliResult->fetch_array();
    }

    public function fetchFields()
    {
        $objArrayToReturn = array();
        while ($objField = $this->objMySqliResult->fetch_field()) {
            array_push($objArrayToReturn, new MysqliField($objField, $this->objDb));
        }
        return $objArrayToReturn;
    }

    public function fetchField()
    {
        if ($objField = $this->objMySqliResult->fetch_field()) {
            return new MysqliField($objField, $this->objDb);
        }
        return null;
    }

    public function fetchRow()
    {
        return $this->objMySqliResult->fetch_row();
    }

    public function mySqlFetchField()
    {
        return $this->objMySqliResult->fetch_field();
    }

    public function countRows()
    {
        return $this->objMySqliResult->num_rows;
    }

    public function countFields()
    {
        return $this->objMySqliResult->field_count;
    }

    public function close()
    {
        $this->objMySqliResult->free();
    }

    public function getNextRow()
    {
        $strColumnArray = $this->fetchArray();

        if ($strColumnArray) {
            return new MysqliRow($strColumnArray);
        } else {
            return null;
        }
    }

    public function getRows()
    {
        $objDbRowArray = array();
        while ($objDbRow = $this->getNextRow()) {
            array_push($objDbRowArray, $objDbRow);
        }
        return $objDbRowArray;
    }
}

