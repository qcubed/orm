<?php

/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Mysqli5;


// New MySQL 5 constants not yet in PHP (as of PHP 5.1.2)
use QCubed\Exception\Caller;

if (!defined('MYSQLI_TYPE_NEWDECIMAL')) {
    define('MYSQLI_TYPE_NEWDECIMAL', 246);
}
if (!defined('MYSQLI_TYPE_BIT')) {
    define('MYSQLI_TYPE_BIT', 16);
}


/**
 * Class Database
 * @package QCubed\Database\Mysqli5
 * @was QMySqli5Database
 */
class Database extends MysqliDatabase
{
    const ADAPTER = 'MySql Improved Database Adapter for MySQL 5';

    public function getTables()
    {
        // Connect if Applicable
        if (!$this->blnConnectedFlag) {
            $this->connect();
        }

        // Use the MySQL5 Information Schema to get a list of all the tables in this database
        // (excluding views, etc.)
        $strDatabaseName = $this->Database;

        $objResult = $this->query("
			SELECT
				table_name
			FROM
				information_schema.tables
			WHERE
				table_type <> 'VIEW' AND
				table_schema = '$strDatabaseName';
		");

        $strToReturn = array();
        while ($strRowArray = $objResult->fetchRow()) {
            array_push($strToReturn, $strRowArray[0]);
        }
        return $strToReturn;
    }

    /**
     * @param string $strQuery
     * @return Result
     * @throws Caller
     * @throws MysqliException
     */
    protected function executeQuery($strQuery)
    {
        // Perform the Query
        $objResult = $this->objMySqli->query($strQuery);
        if ($this->objMySqli->error) {
            throw new MysqliException($this->objMySqli->error, $this->objMySqli->errno, $strQuery);
        }

        if (is_bool($objResult)) {
            throw new Caller ("Use ExecuteNonQuery when no results are expected from a query.");
        }

        // Return the Result
        $objMySqliDatabaseResult = new Result($objResult, $this);
        return $objMySqliDatabaseResult;
    }

    /**
     * @param string $strQuery
     * @return Result[] array of results
     * @throws MysqliException
     */
    public function multiQuery($strQuery)
    {
        // Connect if Applicable
        if (!$this->blnConnectedFlag) {
            $this->connect();
        }

        // Perform the Query
        $this->objMySqli->multi_query($strQuery);
        if ($this->objMySqli->error) {
            throw new MysqliException($this->objMySqli->error, $this->objMySqli->errno, $strQuery);
        }

        $objResultSets = array();
        do {
            if ($objResult = $this->objMySqli->store_result()) {
                array_push($objResultSets, new Result($objResult, $this));
            }
        } while ($this->objMySqli->more_results() && $this->objMySqli->next_result());

        return $objResultSets;
    }

    /**
     * Generic stored procedure executor. For Mysql 5, you can have your stored procedure return results by
     * "SELECT"ing the results. The results will be returned as an array.
     *
     * @param string $strProcName Name of stored procedure
     * @param array|null $params
     * @return Result[]
     * @throws MysqliException
     */
    public function executeProcedure($strProcName, $params = null)
    {
        $strParams = '';
        if ($params) {
            $a = array_map(function ($val) {
                return $this->sqlVariable($val);
            }, $params);
            $strParams = implode(',', $a);
        }
        $strSql = "call {$strProcName}({$strParams})";
        return $this->multiQuery($strSql);
    }

}


