<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Mysqli5;

use QCubed\Database\DatabaseBase;
use QCubed\Database\ForeignKey;
use QCubed\Database\Index;
use QCubed\Exception\Caller;
use QCubed\QString;
use QCubed\Type;

if (!defined('MYSQLI_ON_UPDATE_NOW_FLAG')) {
    define('MYSQLI_ON_UPDATE_NOW_FLAG', 8192);
}

/**
 * Class QMySqliDatabase
 * @was QMySqliDatabase
 */
class MysqliDatabase extends DatabaseBase
{
    const ADAPTER = 'MySql Improved Database Adapter for MySQL 4';

    /** @var  \MySqli */
    protected $objMySqli;

    protected $strEscapeIdentifierBegin = '`';
    protected $strEscapeIdentifierEnd = '`';

    public function sqlLimitVariablePrefix($strLimitInfo)
    {
        // MySQL uses Limit by Suffixes (via a LIMIT clause)

        // If requested, use SQL_CALC_FOUND_ROWS directive to utilize GetFoundRows() method
        if (array_key_exists('usefoundrows', $this->objConfigArray) && $this->objConfigArray['usefoundrows']) {
            return 'SQL_CALC_FOUND_ROWS';
        }

        return null;
    }

    public function sqlLimitVariableSuffix($strLimitInfo)
    {
        // Setup limit suffix (if applicable) via a LIMIT clause
        if (strlen($strLimitInfo)) {
            if (strpos($strLimitInfo, ';') !== false) {
                throw new \Exception('Invalid Semicolon in LIMIT Info');
            }
            if (strpos($strLimitInfo, '`') !== false) {
                throw new \Exception('Invalid Backtick in LIMIT Info');
            }
            return "LIMIT $strLimitInfo";
        }

        return null;
    }

    public function sqlSortByVariable($strSortByInfo)
    {
        // Setup sorting information (if applicable) via a ORDER BY clause
        if (strlen($strSortByInfo)) {
            if (strpos($strSortByInfo, ';') !== false) {
                throw new \Exception('Invalid Semicolon in ORDER BY Info');
            }
            if (strpos($strSortByInfo, '`') !== false) {
                throw new \Exception('Invalid Backtick in ORDER BY Info');
            }

            return "ORDER BY $strSortByInfo";
        }

        return null;
    }

    public function insertOrUpdate($strTable, $mixColumnsAndValuesArray, $strPKNames = null)
    {
        $strEscapedArray = $this->escapeIdentifiersAndValues($mixColumnsAndValuesArray);
        $strUpdateStatement = '';
        foreach ($strEscapedArray as $strColumn => $strValue) {
            if ($strUpdateStatement) {
                $strUpdateStatement .= ', ';
            }
            $strUpdateStatement .= $strColumn . ' = ' . $strValue;
        }
        $strSql = sprintf('INSERT INTO %s%s%s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $this->EscapeIdentifierBegin, $strTable, $this->EscapeIdentifierEnd,
            implode(', ', array_keys($strEscapedArray)),
            implode(', ', array_values($strEscapedArray)),
            $strUpdateStatement
        );
        $this->executeNonQuery($strSql);
    }

    public function connect()
    {
        // Connect to the Database Server
        $this->objMySqli = new \MySqli($this->Server, $this->Username, $this->Password, $this->Database, $this->Port);

        if (!$this->objMySqli) {
            throw new MysqliException("Unable to connect to Database", -1, null);
        }

        if ($this->objMySqli->error) {
            throw new MysqliException($this->objMySqli->error, $this->objMySqli->errno, null);
        }

        // Update "Connected" Flag
        $this->blnConnectedFlag = true;

        // Set to AutoCommit
        $this->nonQuery('SET AUTOCOMMIT=1;');

        // Set NAMES (if applicable)
        if (array_key_exists('encoding', $this->objConfigArray)) {
            $this->nonQuery('SET NAMES ' . $this->objConfigArray['encoding'] . ';');
        }
    }

    public function __get($strName)
    {
        switch ($strName) {
            case 'AffectedRows':
                return $this->objMySqli->affected_rows;
            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }

    /**
     * @param string $strQuery
     * @return MysqliResult
     * @throws MysqliException
     */
    protected function executeQuery($strQuery)
    {
        // Perform the Query
        $objResult = $this->objMySqli->query($strQuery);
        if ($this->objMySqli->error) {
            throw new MysqliException($this->objMySqli->error, $this->objMySqli->errno, $strQuery);
        }

        // Return the Result
        $objMySqliDatabaseResult = new MysqliResult($objResult, $this);
        return $objMySqliDatabaseResult;
    }

    protected function executeNonQuery($strNonQuery)
    {
        // Perform the Query
        $this->objMySqli->query($strNonQuery);
        if ($this->objMySqli->error) {
            throw new MysqliException($this->objMySqli->error, $this->objMySqli->errno, $strNonQuery);
        }
    }

    public function getTables()
    {
        // Use the MySQL "SHOW TABLES" functionality to get a list of all the tables in this database
        $objResult = $this->query("SHOW TABLES");
        $strToReturn = array();
        while ($strRowArray = $objResult->fetchRow()) {
            array_push($strToReturn, $strRowArray[0]);
        }
        return $strToReturn;
    }

    public function getFieldsForTable($strTableName)
    {
        $objResult = $this->query(sprintf('SELECT * FROM %s%s%s LIMIT 1', $this->strEscapeIdentifierBegin,
            $strTableName, $this->strEscapeIdentifierEnd));
        return $objResult->fetchFields();
    }

    public function insertId($strTableName = null, $strColumnName = null)
    {
        return $this->objMySqli->insert_id;
    }

    public function close()
    {
        $this->objMySqli->close();

        // Update Connected Flag
        $this->blnConnectedFlag = false;
    }

    protected function executeTransactionBegin()
    {
        // Set to AutoCommit
        $this->nonQuery('SET AUTOCOMMIT=0;');
    }

    protected function executeTransactionCommit()
    {
        $this->nonQuery('COMMIT;');
        // Set to AutoCommit
        $this->nonQuery('SET AUTOCOMMIT=1;');
    }

    protected function executeTransactionRollBack()
    {
        $this->nonQuery('ROLLBACK;');
        // Set to AutoCommit
        $this->nonQuery('SET AUTOCOMMIT=1;');
    }

    public function getFoundRows()
    {
        if (array_key_exists('usefoundrows', $this->objConfigArray) && $this->objConfigArray['usefoundrows']) {
            $objResult = $this->query('SELECT FOUND_ROWS();');
            $strRow = $objResult->fetchArray();
            return $strRow[0];
        } else {
            throw new Caller('Cannot call GetFoundRows() on the database when "usefoundrows" configuration was not set to true.');
        }
    }

    public function getIndexesForTable($strTableName)
    {
        // Figure out the Table Type (InnoDB, MyISAM, etc.) by parsing the Create Table description
        $strCreateStatement = $this->getCreateStatementForTable($strTableName);
        $strTableType = $this->getTableTypeForCreateStatement($strCreateStatement);

        switch (true) {
            case substr($strTableType, 0, 6) == 'MYISAM':
                return $this->parseForIndexes($strCreateStatement);

            case substr($strTableType, 0, 6) == 'INNODB':
                return $this->parseForIndexes($strCreateStatement);

            case substr($strTableType, 0, 6) == 'MEMORY':
            case substr($strTableType, 0, 4) == 'HEAP':
                return $this->parseForIndexes($strCreateStatement);

            default:
                throw new \Exception("Table Type is not supported: $strTableType");
        }
    }

    public function getForeignKeysForTable($strTableName)
    {
        // Figure out the Table Type (InnoDB, MyISAM, etc.) by parsing the Create Table description
        $strCreateStatement = $this->getCreateStatementForTable($strTableName);
        $strTableType = $this->getTableTypeForCreateStatement($strCreateStatement);

        switch (true) {
            case substr($strTableType, 0, 6) == 'MYISAM':
                $objForeignKeyArray = array();
                break;

            case substr($strTableType, 0, 6) == 'MEMORY':
            case substr($strTableType, 0, 4) == 'HEAP':
                $objForeignKeyArray = array();
                break;

            case substr($strTableType, 0, 6) == 'INNODB':
                $objForeignKeyArray = $this->parseForInnoDbForeignKeys($strCreateStatement);
                break;

            default:
                throw new \Exception("Table Type is not supported: $strTableType");
        }

        return $objForeignKeyArray;
    }

    // MySql defines KeyDefinition to be [OPTIONAL_NAME] ([COL], ...)
    // If the key name exists, this will parse it out and return it
    private function parseNameFromKeyDefinition($strKeyDefinition)
    {
        $strKeyDefinition = trim($strKeyDefinition);

        $intPosition = strpos($strKeyDefinition, '(');

        if ($intPosition === false) {
            throw new \Exception("Invalid Key Definition: $strKeyDefinition");
        } else {
            if ($intPosition == 0) // No Key Name Defined
            {
                return null;
            }
        }

        // If we're here, then we have a key name defined
        $strName = trim(substr($strKeyDefinition, 0, $intPosition));

        // Rip Out leading and trailing "`" character (if applicable)
        if (substr($strName, 0, 1) == '`') {
            return substr($strName, 1, strlen($strName) - 2);
        } else {
            return $strName;
        }
    }

    // MySql defines KeyDefinition to be [OPTIONAL_NAME] ([COL], ...)
    // This will return an array of strings that are the names [COL], etc.
    private function parseColumnNameArrayFromKeyDefinition($strKeyDefinition)
    {
        $strKeyDefinition = trim($strKeyDefinition);

        // Get rid of the opening "(" and the closing ")"
        $intPosition = strpos($strKeyDefinition, '(');
        if ($intPosition === false) {
            throw new \Exception("Invalid Key Definition: $strKeyDefinition");
        }
        $strKeyDefinition = trim(substr($strKeyDefinition, $intPosition + 1));

        $intPosition = strpos($strKeyDefinition, ')');
        if ($intPosition === false) {
            throw new \Exception("Invalid Key Definition: $strKeyDefinition");
        }
        $strKeyDefinition = trim(substr($strKeyDefinition, 0, $intPosition));

        // Create the Array
        // TODO: Current method doesn't support key names with commas or parenthesis in them!
        $strToReturn = explode(',', $strKeyDefinition);

        // Take out trailing and leading "`" character in each name (if applicable)
        for ($intIndex = 0; $intIndex < count($strToReturn); $intIndex++) {
            $strColumn = $strToReturn[$intIndex];

            if (substr($strColumn, 0, 1) == '`') {
                $strColumn = substr($strColumn, 1, strpos($strColumn, '`', 1) - 1);
            }

            $strToReturn[$intIndex] = $strColumn;
        }

        return $strToReturn;
    }

    private function parseForIndexes($strCreateStatement)
    {
        // MySql nicely splits each object in a table into it's own line
        // Split the create statement into lines, and then pull out anything
        // that says "PRIMARY KEY", "UNIQUE KEY", or just plain ol' "KEY"
        $strLineArray = explode("\n", $strCreateStatement);

        $objIndexArray = array();

        // We don't care about the first line or the last line
        for ($intIndex = 1; $intIndex < (count($strLineArray) - 1); $intIndex++) {
            $strLine = $strLineArray[$intIndex];

            // Each object has a two-space indent
            // So this is a key object if any of those key-related words exist at position 2
            switch (2) {
                case (strpos($strLine, 'PRIMARY KEY')):
                    $strKeyDefinition = substr($strLine, strlen('  PRIMARY KEY '));

                    $strKeyName = $this->parseNameFromKeyDefinition($strKeyDefinition);
                    $strColumnNameArray = $this->parseColumnNameArrayFromKeyDefinition($strKeyDefinition);

                    $objIndex = new Index($strKeyName, $blnPrimaryKey = true, $blnUnique = true, $strColumnNameArray);
                    array_push($objIndexArray, $objIndex);
                    break;

                case (strpos($strLine, 'UNIQUE KEY')):
                    $strKeyDefinition = substr($strLine, strlen('  UNIQUE KEY '));

                    $strKeyName = $this->parseNameFromKeyDefinition($strKeyDefinition);
                    $strColumnNameArray = $this->parseColumnNameArrayFromKeyDefinition($strKeyDefinition);

                    $objIndex = new Index($strKeyName, $blnPrimaryKey = false, $blnUnique = true, $strColumnNameArray);
                    array_push($objIndexArray, $objIndex);
                    break;

                case (strpos($strLine, 'KEY')):
                    $strKeyDefinition = substr($strLine, strlen('  KEY '));

                    $strKeyName = $this->parseNameFromKeyDefinition($strKeyDefinition);
                    $strColumnNameArray = $this->parseColumnNameArrayFromKeyDefinition($strKeyDefinition);

                    $objIndex = new Index($strKeyName, $blnPrimaryKey = false, $blnUnique = false, $strColumnNameArray);
                    array_push($objIndexArray, $objIndex);
                    break;
            }
        }

        return $objIndexArray;
    }

    private function parseForInnoDbForeignKeys($strCreateStatement)
    {
        // MySql nicely splits each object in a table into it's own line
        // Split the create statement into lines, and then pull out anything
        // that starts with "CONSTRAINT" and contains "FOREIGN KEY"
        $strLineArray = explode("\n", $strCreateStatement);

        $objForeignKeyArray = array();

        // We don't care about the first line or the last line
        for ($intIndex = 1; $intIndex < (count($strLineArray) - 1); $intIndex++) {
            $strLine = $strLineArray[$intIndex];

            // Check to see if the line:
            // * Starts with "CONSTRAINT" at position 2 AND
            // * contains "FOREIGN KEY"
            if ((strpos($strLine, "CONSTRAINT") == 2) &&
                (strpos($strLine, "FOREIGN KEY") !== false)
            ) {
                $strLine = substr($strLine, strlen('  CONSTRAINT '));

                // By the end of the following lines, we will end up with a strTokenArray
                // Index 0: the FK name
                // Index 1: the list of columns that are the foreign key
                // Index 2: the table which this FK references
                // Index 3: the list of columns which this FK references
                $strTokenArray = explode(' FOREIGN KEY ', $strLine);
                $strTokenArray[1] = explode(' REFERENCES ', $strTokenArray[1]);
                $strTokenArray[2] = $strTokenArray[1][1];
                $strTokenArray[1] = $strTokenArray[1][0];
                $strTokenArray[2] = explode(' ', $strTokenArray[2]);
                $strTokenArray[3] = $strTokenArray[2][1];
                $strTokenArray[2] = $strTokenArray[2][0];

                // Cleanup, and change Index 1 and Index 3 to be an array based on the
                // parsed column name list
                if (substr($strTokenArray[0], 0, 1) == '`') {
                    $strTokenArray[0] = substr($strTokenArray[0], 1, strlen($strTokenArray[0]) - 2);
                }
                $strTokenArray[1] = $this->parseColumnNameArrayFromKeyDefinition($strTokenArray[1]);
                if (substr($strTokenArray[2], 0, 1) == '`') {
                    $strTokenArray[2] = substr($strTokenArray[2], 1, strlen($strTokenArray[2]) - 2);
                }
                $strTokenArray[3] = $this->parseColumnNameArrayFromKeyDefinition($strTokenArray[3]);

                // Create the FK object and add it to the return array
                $objForeignKey = new ForeignKey($strTokenArray[0], $strTokenArray[1], $strTokenArray[2],
                    $strTokenArray[3]);
                array_push($objForeignKeyArray, $objForeignKey);

                // Ensure the FK object has matching column numbers (or else, throw)
                if ((count($objForeignKey->ColumnNameArray) == 0) ||
                    (count($objForeignKey->ColumnNameArray) != count($objForeignKey->ReferenceColumnNameArray))
                ) {
                    throw new \Exception("Invalid Foreign Key definition: $strLine");
                }
            }
        }
        return $objForeignKeyArray;
    }

    private function getCreateStatementForTable($strTableName)
    {
        // Use the MySQL "SHOW CREATE TABLE" functionality to get the table's Create statement
        $objResult = $this->query(sprintf('SHOW CREATE TABLE `%s`', $strTableName));
        $objRow = $objResult->fetchRow();
        $strCreateTable = $objRow[1];
        $strCreateTable = str_replace("\r", "", $strCreateTable);
        return $strCreateTable;
    }

    private function getTableTypeForCreateStatement($strCreateStatement)
    {
        // Table Type is in the last line of the Create Statement, "TYPE=DbTableType"
        $strLineArray = explode("\n", $strCreateStatement);
        $strFinalLine = strtoupper($strLineArray[count($strLineArray) - 1]);

        if (substr($strFinalLine, 0, 7) == ') TYPE=') {
            return trim(substr($strFinalLine, 7));
        } else {
            if (substr($strFinalLine, 0, 9) == ') ENGINE=') {
                return trim(substr($strFinalLine, 9));
            } else {
                throw new \Exception("Invalid Table Description");
            }
        }
    }

    /**
     *
     * @param string $sql
     * @return MysqliResult
     */
    public function explainStatement($sql)
    {
        // As of MySQL 5.6.3, EXPLAIN provides information about
        // SELECT, DELETE, INSERT, REPLACE, and UPDATE statements.
        // Before MySQL 5.6.3, EXPLAIN provides information only about SELECT statements.

        $objDbResult = $this->query("select version()");
        $strDbRow = $objDbResult->fetchRow();
        $strVersion = Type::cast($strDbRow[0], Type::STRING);
        $strVersionArray = explode('.', $strVersion);
        $strMajorVersion = null;
        if (count($strVersionArray) > 0) {
            $strMajorVersion = $strVersionArray[0];
        }
        if (null === $strMajorVersion) {
            return null;
        }
        if (intval($strMajorVersion) > 5) {
            return $this->query("EXPLAIN " . $sql);
        } else {
            if (5 == intval($strMajorVersion)) {
                $strMinorVersion = null;
                if (count($strVersionArray) > 1) {
                    $strMinorVersion = $strVersionArray[1];
                }
                if (null === $strMinorVersion) {
                    return null;
                }
                if (intval($strMinorVersion) > 6) {
                    return $this->query("EXPLAIN " . $sql);
                } else {
                    if (6 == intval($strMinorVersion)) {
                        $strSubMinorVersion = null;
                        if (count($strVersionArray) > 2) {
                            $strSubMinorVersion = $strVersionArray[2];
                        }
                        if (null === $strSubMinorVersion) {
                            return null;
                        }
                        if (!QString::isInteger($strSubMinorVersion)) {
                            $strSubMinorVersionArray = explode("-", $strSubMinorVersion);
                            if (count($strSubMinorVersionArray) > 1) {
                                $strSubMinorVersion = $strSubMinorVersionArray[0];
                                if (!is_integer($strSubMinorVersion)) {
                                    // Failed to determine the sub-minor version.
                                    return null;
                                }
                            } else {
                                // Failed to determine the sub-minor version.
                                return null;
                            }
                        }
                        if (intval($strSubMinorVersion) > 2) {
                            return $this->query("EXPLAIN " . $sql);
                        } else {
                            // We have the version before 5.6.3
                            // let's check if it is SELECT-only request
                            if (0 == substr_count($sql, "DELETE") &&
                                0 == substr_count($sql, "INSERT") &&
                                0 == substr_count($sql, "REPLACE") &&
                                0 == substr_count($sql, "UPDATE")
                            ) {
                                return $this->query("EXPLAIN " . $sql);
                            }
                        }
                    }
                }
            }
        }
        // Return null by default
        return null;
    }
}



