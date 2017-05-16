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
use QCubed\QCubed;
use QCubed\QDateTime;
use QCubed\Timer;
use QCubed\Type;

/**
 * Every database adapter must implement the following 5 classes (all of which are abstract):
 * * DatabaseBase
 * * DatabaseFieldBase
 * * DatabaseResultBase
 * * DatabaseRowBase
 * * DatabaseExceptionBase
 * This Database library also has the following classes already defined, and
 * Database adapters are assumed to use them internally:
 * * DatabaseIndex
 * * DatabaseForeignKey
 * * DatabaseFieldType (which is an abstract class that solely contains constants)
 *
 * @property-read string $EscapeIdentifierBegin
 * @property-read string $EscapeIdentifierEnd
 * @property-read boolean $EnableProfiling
 * @property-read int $AffectedRows
 * @property-read string $Profile
 * @property-read int $DatabaseIndex
 * @property-read int $Adapter
 * @property-read string $Server
 * @property-read string $Port
 * @property-read string $Database
 * @property-read string $Service
 * @property-read string $Protocol
 * @property-read string $Host
 * @property-read string $Username
 * @property-read string $Password
 * @property boolean $Caching         if true objects loaded from this database will be kept in cache (assuming a cache provider is also configured)
 * @property-read string $DateFormat
 * @property-read boolean $OnlyFullGroupBy database adapter sub-classes can override and set this property to true
 *          to prevent the behavior of automatically adding all the columns to the select clause when the query has
 *          an aggregation clause.
 * @package DatabaseAdapters
 * @was QDatabaseBase
 */
abstract class DatabaseBase extends ObjectBase
{
    // Must be updated for all Adapters
    /** Adapter name */
    const ADAPTER = 'Generic Database Adapter (Abstract)';

    // Protected Member Variables for ALL Database Adapters
    /** @var int Database Index according to the configuration file */
    protected $intDatabaseIndex;
    /** @var bool Has the profiling been enabled? */
    protected $blnEnableProfiling;
    protected $strProfileArray;

    protected $objConfigArray;
    protected $blnConnectedFlag = false;

    /** @var string The beginning part of characters which can escape identifiers in a SQL query for the database */
    protected $strEscapeIdentifierBegin = '"';
    /** @var string The ending part of characters which can escape identifiers in a SQL query for the database */
    protected $strEscapeIdentifierEnd = '"';
    protected $blnOnlyFullGroupBy = false; // should be set in sub-classes as appropriate

    /**
     * @var int The transaction depth value.
     * It is incremented on a transaction begin,
     * decremented on a transaction commit, and reset to zero on a roll back.
     * It is used to implement the recursive transaction functionality.
     */
    protected $intTransactionDepth = 0;

    // Abstract Methods that ALL Database Adapters MUST implement

    /**
     * Connects to the database
     */
    abstract public function connect();
    // these are protected - externally, the "Query/NonQuery" wrappers are meant to be called

    /**
     * Sends a SQL query for execution to the database
     * In this regard, a query is a 'SELECT' statement
     *
     * @param string $strQuery The Query to be executed
     *
     * @return mixed Result that the database returns after running the query.
     */
    abstract protected function executeQuery($strQuery);

    /**
     * Sends a non-SELECT query (such as INSERT, UPDATE, DELETE, TRUNCATE) to DB server.
     * In most cases, the results of this function are not used and you should not send
     * 'SELECT' queries using this method because a result is not guaranteed to be returned
     *
     * If there was an error, it would most probably be caught as an exception.
     *
     * @param string $strNonQuery The Query to be executed
     *
     * @return mixed Result that the database returns after running the query
     */
    abstract protected function executeNonQuery($strNonQuery);

    /**
     * Returns the list of tables in the database (as string)
     *
     * @return mixed|string[] List of tables
     */
    abstract public function getTables();

    /**
     * Returns the ID to be inserted in a table column (normally it an autoincrement column)
     *
     * @param null|string $strTableName Table name where the ID has to be inserted
     * @param null|string $strColumnName Column name where the ID has to be inserted
     *
     * @return mixed
     */
    abstract public function insertId($strTableName = null, $strColumnName = null);

    /**
     * Get the list of columns/fields for a given table
     *
     * @param string $strTableName Name of table whose fields we have to get
     *
     * @return mixed
     */
    abstract public function getFieldsForTable($strTableName);

    /**
     * Get list of indexes for a table
     *
     * @param string $strTableName Name of table whose column indexes we have to get
     *
     * @return mixed
     */
    abstract public function getIndexesForTable($strTableName);

    /**
     * Get list of foreign keys for a table
     *
     * @param string $strTableName Name of table whose foreign keys we are trying to get
     *
     * @return mixed
     */
    abstract public function getForeignKeysForTable($strTableName);

    /**
     * This function actually begins the database transaction.
     * Must be implemented in all subclasses.
     * The "TransactionBegin" wrapper are meant to be called by end-user code
     *
     * @return void Nothing
     */
    abstract protected function executeTransactionBegin();

    /**
     * This function actually commits the database transaction.
     * Must be implemented in all subclasses.
     * The "TransactionCommit" wrapper are meant to be called by end-user code
     * @return void Nothing
     */
    abstract protected function executeTransactionCommit();

    /**
     * This function actually rolls back the database transaction.
     * Must be implemented in all subclasses.
     * The "TransactionRollBack" wrapper are meant to be called by end-user code
     *
     * @return void Nothing
     */
    abstract protected function executeTransactionRollBack();

    /**
     * Template for executing stored procedures. Optional, for those database drivers that support it.
     * @param string $strProcName
     * @param array|null $params
     * @return mixed
     */
    public function executeProcedure($strProcName, $params = null)
    {
        return null;
    }

    /**
     * This function begins the database transaction.
     *
     * @return void Nothing
     */
    public final function transactionBegin()
    {
        if (0 == $this->intTransactionDepth) {
            $this->executeTransactionBegin();
        }
        $this->intTransactionDepth++;
    }

    /**
     * This function commits the database transaction.
     *
     * @throws Caller
     * @return void Nothing
     */
    public final function transactionCommit()
    {
        if (1 == $this->intTransactionDepth) {
            $this->executeTransactionCommit();
        }
        if ($this->intTransactionDepth <= 0) {
            throw new Caller("The transaction commit call is called before the transaction begin was called.");
        }
        $this->intTransactionDepth--;
    }

    /**
     * This function rolls back the database transaction.
     *
     * @return void Nothing
     */
    public final function transactionRollBack()
    {
        $this->executeTransactionRollBack();
        $this->intTransactionDepth = 0;
    }

    abstract public function sqlLimitVariablePrefix($strLimitInfo);

    abstract public function sqlLimitVariableSuffix($strLimitInfo);

    abstract public function sqlSortByVariable($strSortByInfo);

    /**
     * Closes the database connection
     *
     * @return mixed
     */
    abstract public function close();

    /**
     * Given an identifier for a SQL query, this method returns the escaped identifier
     *
     * @param string $strIdentifier Identifier to be escaped
     *
     * @return string Escaped identifier string
     */
    public function escapeIdentifier($strIdentifier)
    {
        return $this->strEscapeIdentifierBegin . $strIdentifier . $this->strEscapeIdentifierEnd;
    }

    /**
     * Given an array of identifiers, this method returns array of escaped identifiers
     * For corner case handling, if a single identifier is supplied, a single escaped identifier is returned
     *
     * @param array|string $mixIdentifiers Array of escaped identifiers (array) or one unescaped identifier (string)
     *
     * @return array|string Array of escaped identifiers (array) or one escaped identifier (string)
     */
    public function escapeIdentifiers($mixIdentifiers)
    {
        if (is_array($mixIdentifiers)) {
            return array_map(array($this, 'EscapeIdentifier'), $mixIdentifiers);
        } else {
            return $this->escapeIdentifier($mixIdentifiers);
        }
    }

    /**
     * Escapes values (or single value) which we can then send to the database
     *
     * @param array|mixed $mixValues Array of values (or a single value) to be escaped
     *
     * @return array|string Array of (or a single) escaped value(s)
     */
    public function escapeValues($mixValues)
    {
        if (is_array($mixValues)) {
            return array_map(array($this, 'SqlVariable'), $mixValues);
        } else {
            return $this->sqlVariable($mixValues);
        }
    }

    /**
     * Escapes both column and values when supplied as an array
     *
     * @param array $mixColumnsAndValuesArray Array with column=>value format with both (column and value) sides unescaped
     *
     * @return array Array with column=>value format data with both column and value escaped
     */
    public function escapeIdentifiersAndValues($mixColumnsAndValuesArray)
    {
        $result = array();
        foreach ($mixColumnsAndValuesArray as $strColumn => $mixValue) {
            $result[$this->escapeIdentifier($strColumn)] = $this->sqlVariable($mixValue);
        }
        return $result;
    }

    /**
     * INSERTs or UPDATEs a table
     *
     * @param string $strTable Table name
     * @param array $mixColumnsAndValuesArray column=>value array
     *                                                    (they are given to 'EscapeIdentifiersAndValues' method)
     * @param null|string|array $strPKNames Name(s) of primary key column(s) (expressed as string or array)
     */
    public function insertOrUpdate($strTable, $mixColumnsAndValuesArray, $strPKNames = null)
    {
        $strEscapedArray = $this->escapeIdentifiersAndValues($mixColumnsAndValuesArray);
        $strColumns = array_keys($strEscapedArray);
        $strUpdateStatement = '';
        foreach ($strEscapedArray as $strColumn => $strValue) {
            if ($strUpdateStatement) {
                $strUpdateStatement .= ', ';
            }
            $strUpdateStatement .= $strColumn . ' = ' . $strValue;
        }
        if (is_null($strPKNames)) {
            $strMatchCondition = 'target_.' . $strColumns[0] . ' = source_.' . $strColumns[0];
        } else {
            if (is_array($strPKNames)) {
                $strMatchCondition = '';
                foreach ($strPKNames as $strPKName) {
                    if ($strMatchCondition) {
                        $strMatchCondition .= ' AND ';
                    }
                    $strMatchCondition .= 'target_.' . $this->escapeIdentifier($strPKName) . ' = source_.' . $this->escapeIdentifier($strPKName);
                }
            } else {
                $strMatchCondition = 'target_.' . $this->escapeIdentifier($strPKNames) . ' = source_.' . $this->escapeIdentifier($strPKNames);
            }
        }
        $strTable = $this->EscapeIdentifierBegin . $strTable . $this->EscapeIdentifierEnd;
        $strSql = sprintf('MERGE INTO %s AS target_ USING %s AS source_ ON %s WHEN MATCHED THEN UPDATE SET %s WHEN NOT MATCHED THEN INSERT (%s) VALUES (%s)',
            $strTable, $strTable,
            $strMatchCondition, $strUpdateStatement,
            implode(', ', $strColumns),
            implode(', ', array_values($strEscapedArray))
        );
        $this->executeNonQuery($strSql);
    }

    /**
     * Sends the 'SELECT' query to the database and returns the result
     *
     * @param string $strQuery query string
     *
     * @return ResultBase
     */
    public final function query($strQuery)
    {
        $timerName = null;
        if (!$this->blnConnectedFlag) {
            $this->connect();
        }


        if ($this->blnEnableProfiling) {
            $timerName = 'queryExec' . mt_rand();
            Timer::start($timerName);
        }

        $result = $this->executeQuery($strQuery);

        if ($this->blnEnableProfiling) {
            $dblQueryTime = Timer::stop($timerName);
            Timer::reset($timerName);

            // Log Query (for Profiling, if applicable)
            $this->logQuery($strQuery, $dblQueryTime);
        }

        return $result;
    }

    /**
     * This is basically the same as 'Query' but is used when SQL statements other than 'SELECT'
     * @param string $strNonQuery The SQL to be sent
     *
     * @return mixed
     * @throws Caller
     */
    public final function nonQuery($strNonQuery)
    {
        if (!$this->blnConnectedFlag) {
            $this->connect();
        }
        $timerName = '';
        if ($this->blnEnableProfiling) {
            $timerName = 'queryExec' . mt_rand();
            Timer::start($timerName);
        }

        $result = $this->executeNonQuery($strNonQuery);

        if ($this->blnEnableProfiling) {
            $dblQueryTime = Timer::stop($timerName);
            Timer::reset($timerName);

            // Log Query (for Profiling, if applicable)
            $this->logQuery($strNonQuery, $dblQueryTime);
        }

        return $result;
    }

    /**
     * PHP magic method
     * @param string $strName Property name
     *
     * @return mixed
     * @throws \Exception|Caller
     */
    public function __get($strName)
    {
        switch ($strName) {
            case 'EscapeIdentifierBegin':
                return $this->strEscapeIdentifierBegin;
            case 'EscapeIdentifierEnd':
                return $this->strEscapeIdentifierEnd;
            case 'EnableProfiling':
                return $this->blnEnableProfiling;
            case 'AffectedRows':
                return -1;
            case 'Profile':
                return $this->strProfileArray;
            case 'DatabaseIndex':
                return $this->intDatabaseIndex;
            case 'Adapter':
                $strConstantName = get_class($this) . '::ADAPTER';
                return constant($strConstantName) . ' (' . $this->objConfigArray['adapter'] . ')';
            case 'Server':
            case 'Port':
            case 'Database':
                // Informix naming
            case 'Service':
            case 'Protocol':
            case 'Host':

            case 'Username':
            case 'Password':
            case 'Caching':
                return $this->objConfigArray[strtolower($strName)];
            case 'DateFormat':
                return (is_null($this->objConfigArray[strtolower($strName)])) ? (QDateTime::FORMAT_ISO) : ($this->objConfigArray[strtolower($strName)]);
            case 'OnlyFullGroupBy':
                return (!isset($this->objConfigArray[strtolower($strName)])) ? $this->blnOnlyFullGroupBy : $this->objConfigArray[strtolower($strName)];

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
     * PHP magic method to set class properties
     * @param string $strName Property name
     * @param string $mixValue Property value
     *
     * @return mixed|void
     * @throws \Exception|Caller
     */
    public function __set($strName, $mixValue)
    {
        switch ($strName) {
            case 'Caching':
                $this->objConfigArray[strtolower($strName)] = $mixValue;
                break;

            default:
                try {
                    parent::__set($strName, $mixValue);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }

    /**
     * Constructs a Database Adapter based on the database index and the configuration array of properties
     * for this particular adapter. Sets up the base-level configuration properties for this database,
     * namely DB Profiling and Database Index
     *
     * @param integer $intDatabaseIndex
     * @param string[] $objConfigArray configuration array as passed in to the constructor
     *                                 by QApplicationBase::initializeDatabaseConnections();
     *
     * @throws \Exception|Caller|InvalidCast
     */
    public function __construct($intDatabaseIndex, $objConfigArray)
    {
        // Setup DatabaseIndex
        $this->intDatabaseIndex = $intDatabaseIndex;

        // Save the ConfigArray
        $this->objConfigArray = $objConfigArray;

        // Setup Profiling Array (if applicable)
        $this->blnEnableProfiling = Type::cast($objConfigArray['profiling'], Type::BOOLEAN);
        if ($this->blnEnableProfiling) {
            $this->strProfileArray = array();
        }
    }

    /**
     * Allows for the enabling of DB profiling while in middle of the script
     *
     * @return void
     */
    public function enableProfiling()
    {
        // Only perform profiling initialization if profiling is not yet enabled
        if (!$this->blnEnableProfiling) {
            $this->blnEnableProfiling = true;
            $this->strProfileArray = array();
        }
    }

    /**
     * If EnableProfiling is on, then log the query to the profile array
     *
     * @param string $strQuery
     * @param double $dblQueryTime query execution time in milliseconds
     * @return void
     */
    private function logQuery($strQuery, $dblQueryTime)
    {
        if ($this->blnEnableProfiling) {
            // Dereference-ize Backtrace Information
            $objDebugBacktrace = debug_backtrace();

            // get rid of unnecessary backtrace info in case of:
            // query
            if ((count($objDebugBacktrace) > 3) &&
                (array_key_exists('function', $objDebugBacktrace[2])) &&
                (($objDebugBacktrace[2]['function'] == 'QueryArray') ||
                    ($objDebugBacktrace[2]['function'] == 'QuerySingle') ||
                    ($objDebugBacktrace[2]['function'] == 'QueryCount'))
            ) {
                $objBacktrace = $objDebugBacktrace[3];
            } else {
                if (isset($objDebugBacktrace[2])) // non query
                {
                    $objBacktrace = $objDebugBacktrace[2];
                } else // ad hoc query
                {
                    $objBacktrace = $objDebugBacktrace[1];
                }
            }

            // get rid of reference to current object in backtrace array
            if (isset($objBacktrace['object'])) {
                $objBacktrace['object'] = null;
            }

            for ($intIndex = 0, $intMax = count($objBacktrace['args']); $intIndex < $intMax; $intIndex++) {
                $obj = $objBacktrace['args'][$intIndex];

                if (is_null($obj)) {
                    $obj = 'null';
                } else {
                    if (gettype($obj) == 'integer') {
                    } else {
                        if (gettype($obj) == 'object') {
                            $obj = 'Object: ' . get_class($obj);
                            if (method_exists($obj, '__toString')) {
                                $obj .= '- ' . $obj;
                            }
                        } else {
                            if (is_array($obj)) {
                                $obj = 'Array';
                            } else {
                                $obj = sprintf("'%s'", $obj);
                            }
                        }
                    }
                }
                $objBacktrace['args'][$intIndex] = $obj;
            }

            // Push it onto the profiling information array
            $arrProfile = array(
                'objBacktrace' => $objBacktrace,
                'strQuery' => $strQuery,
                'dblTimeInfo' => $dblQueryTime
            );

            array_push($this->strProfileArray, $arrProfile);
        }
    }

    /**
     * Properly escapes $mixData to be used as a SQL query parameter.
     * If IncludeEquality is set (usually not), then include an equality operator.
     * So for most data, it would just be "=".  But, for example,
     * if $mixData is NULL, then most RDBMS's require the use of "IS".
     *
     * @param mixed $mixData
     * @param boolean $blnIncludeEquality whether or not to include an equality operator
     * @param boolean $blnReverseEquality whether the included equality operator should be a "NOT EQUAL", e.g. "!="
     * @return string the properly formatted SQL variable
     */
    public function sqlVariable($mixData, $blnIncludeEquality = false, $blnReverseEquality = false)
    {
        // Are we SqlVariabling a BOOLEAN value?
        if (is_bool($mixData)) {
            // Yes
            if ($blnIncludeEquality) {
                // We must include the inequality

                if ($blnReverseEquality) {
                    // Do a "Reverse Equality"

                    // Check against NULL, True then False
                    if (is_null($mixData)) {
                        return 'IS NOT NULL';
                    } else {
                        if ($mixData) {
                            return '= 0';
                        } else {
                            return '!= 0';
                        }
                    }
                } else {
                    // Check against NULL, True then False
                    if (is_null($mixData)) {
                        return 'IS NULL';
                    } else {
                        if ($mixData) {
                            return '!= 0';
                        } else {
                            return '= 0';
                        }
                    }
                }
            } else {
                // Check against NULL, True then False
                if (is_null($mixData)) {
                    return 'NULL';
                } else {
                    if ($mixData) {
                        return '1';
                    } else {
                        return '0';
                    }
                }
            }
        }

        // Check for Equality Inclusion
        if ($blnIncludeEquality) {
            if ($blnReverseEquality) {
                if (is_null($mixData)) {
                    $strToReturn = 'IS NOT ';
                } else {
                    $strToReturn = '!= ';
                }
            } else {
                if (is_null($mixData)) {
                    $strToReturn = 'IS ';
                } else {
                    $strToReturn = '= ';
                }
            }
        } else {
            $strToReturn = '';
        }

        // Check for NULL Value
        if (is_null($mixData)) {
            return $strToReturn . 'NULL';
        }

        // Check for NUMERIC Value
        if (is_integer($mixData) || is_float($mixData)) {
            return $strToReturn . sprintf('%s', $mixData);
        }

        // Check for DATE Value
        if ($mixData instanceof QDateTime) {
            /** @var QDateTime $mixData */
            if ($mixData->isTimeNull()) {
                if ($mixData->isDateNull()) {
                    return $strToReturn . 'NULL'; // null date and time is a null value
                }
                return $strToReturn . sprintf("'%s'", $mixData->qFormat('YYYY-MM-DD'));
            } elseif ($mixData->isDateNull()) {
                return $strToReturn . sprintf("'%s'", $mixData->qFormat('hhhh:mm:ss'));
            }
            return $strToReturn . sprintf("'%s'", $mixData->qFormat(QDateTime::FORMAT_ISO));
        }

        // an array. Assume we are using it in an array context, like an IN clause
        if (is_array($mixData)) {
            $items = [];
            foreach ($mixData as $item) {
                $items[] = $this->sqlVariable($item);    // recurse
            }
            return '(' . implode(',', $items) . ')';
        }

        // Assume it's some kind of string value
        return $strToReturn . sprintf("'%s'", addslashes($mixData));
    }

    public function prepareStatement($strQuery, $mixParameterArray)
    {
        foreach ($mixParameterArray as $strKey => $mixValue) {
            if (is_array($mixValue)) {
                $strParameters = array();
                foreach ($mixValue as $mixParameter) {
                    array_push($strParameters, $this->sqlVariable($mixParameter));
                }
                $strQuery = str_replace(chr(QCubed::NAMED_VALUE_DELIMITER) . '{' . $strKey . '}',
                    implode(',', $strParameters) . ')', $strQuery);
            } else {
                $strQuery = str_replace(chr(QCubed::NAMED_VALUE_DELIMITER) . '{=' . $strKey . '=}',
                    $this->sqlVariable($mixValue, true, false), $strQuery);
                $strQuery = str_replace(chr(QCubed::NAMED_VALUE_DELIMITER) . '{!' . $strKey . '!}',
                    $this->sqlVariable($mixValue, true, true), $strQuery);
                $strQuery = str_replace(chr(QCubed::NAMED_VALUE_DELIMITER) . '{' . $strKey . '}',
                    $this->sqlVariable($mixValue), $strQuery);
            }
        }

        return $strQuery;
    }

    /**
     * Displays the OutputProfiling results, plus a link which will popup the details of the profiling.
     *
     * @param bool $blnPrintOutput
     * @return null|string
     */
    public function outputProfiling($blnPrintOutput = true)
    {
        $strPath = isset($_SERVER['REQUEST_URI']) ?
            $_SERVER['REQUEST_URI'] :
            $_SERVER['PHP_SELF'];

        $strOut = '<div class="qDbProfile">';
        if ($this->blnEnableProfiling) {
            $strOut .= sprintf('<form method="post" id="frmDbProfile%s" action="%s/profile.php"><div>',
                $this->intDatabaseIndex, QCUBED_PHP_URL);
            $strOut .= sprintf('<input type="hidden" name="strProfileData" value="%s" />',
                base64_encode(serialize($this->strProfileArray)));
            $strOut .= sprintf('<input type="hidden" name="intDatabaseIndex" value="%s" />', $this->intDatabaseIndex);
            $strOut .= sprintf('<input type="hidden" name="strReferrer" value="%s" /></div></form>',
                htmlentities($strPath));

            $intCount = round(count($this->strProfileArray));
            if ($intCount == 0) {
                $strQueryString = 'No queries';
            } else {
                if ($intCount == 1) {
                    $strQueryString = '1 query';
                } else {
                    $strQueryString = $intCount . ' queries';
                }
            }

            $strOut .= sprintf('<b>PROFILING INFORMATION FOR DATABASE CONNECTION #%s</b>: %s performed.  Please <a href="#" onclick="var frmDbProfile = document.getElementById(\'frmDbProfile%s\'); frmDbProfile.target = \'_blank\'; frmDbProfile.submit(); return false;">click here to view profiling detail</a><br />',
                $this->intDatabaseIndex, $strQueryString, $this->intDatabaseIndex);
        } else {
            $strOut .= '<form></form><b>Profiling was not enabled for this database connection (#' . $this->intDatabaseIndex . ').</b>  To enable, ensure that ENABLE_PROFILING is set to TRUE.';
        }
        $strOut .= '</div>';

        $strOut .= '<script>$j(function() {$j(".qDbProfile").draggable();});</script>';    // make it draggable so you can move it out of the way if needed.

        if ($blnPrintOutput) {
            print ($strOut);
            return null;
        } else {
            return $strOut;
        }
    }

    /**
     * Executes the explain statement for a given query and returns the output without any transformation.
     * If the database adapter does not support EXPLAIN statements, returns null.
     *
     * @param $strSql
     *
     * @return null
     */
    public function explainStatement($strSql)
    {
        return null;
    }


    /**
     * Utility function to extract the json embedded options structure from the comments.
     *
     * Usage:
     * <code>
     *    list($strComment, $options) = Base::extractCommentOptions($strComment);
     * </code>
     *
     * @param string $strComment The comment to analyze
     * @return array A two item array, with first item the comment with the options removed, and 2nd item the options array.
     *
     */
    public static function extractCommentOptions($strComment)
    {
        $ret[0] = null; // comment string without options
        $ret[1] = null; // the options array
        if (($strComment) &&
            ($pos1 = strpos($strComment, '{')) !== false &&
            ($pos2 = strrpos($strComment, '}', $pos1))
        ) {

            $strJson = substr($strComment, $pos1, $pos2 - $pos1 + 1);
            $a = json_decode($strJson, true);

            if ($a) {
                $ret[0] = substr($strComment, 0, $pos1) . substr($strComment,
                        $pos2 + 1); // return comment without options
                $ret[1] = $a;
            } else {
                $ret[0] = $strComment;
            }
        }

        return $ret;
    }

}

