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
 * Class Service
 *
 * This service initializes and provides the singleton instances of the databases in use. This was
 * provided by QApplication static variables in older versions.
 *
 * The order of the databases is take from the config file, and is very important to not change after
 * codegen. You must codegen again if they change.
 *
 * @package QCubed\Database
 */
class Service extends ObjectBase
{

    /**
     * An array of Database objects, as initialized by Service::initializeDatabaseConnections()
     *
     * @var Base[]
     */
    protected static $Database = [];

    /**
     * This call will initialize the database connection(s) as defined by
     * the constants DB_CONNECTION_X, where "X" is the index number of a
     * particular database connection.
     *
     * @throws \Exception
     * @return void
     */
    public static function initializeDatabaseConnections()
    {
        // for backward compatibility, don't use MAX_DB_CONNECTION_INDEX directly,
        // but check if MAX_DB_CONNECTION_INDEX is defined
        $intMaxIndex = defined('MAX_DB_CONNECTION_INDEX') ? constant('MAX_DB_CONNECTION_INDEX') : 9;

        if (defined('DB_CONNECTION_0')) {
            // This causes a conflict with how DbBackedSessionHandler works.
            throw new \Exception('Do not define DB_CONNECTION_0. Start at DB_CONNECTION_1');
        }

        for ($intIndex = 1; $intIndex <= $intMaxIndex; $intIndex++) {
            $strConstantName = sprintf('DB_CONNECTION_%s', $intIndex);

            if (defined($strConstantName)) {
                // Expected Keys to be Set
                $strExpectedKeys = array(
                    'adapter',
                    'server',
                    'port',
                    'database',
                    'username',
                    'password',
                    'profiling',
                    'dateformat'
                );

                // Lookup the Serialized Array from the DB_CONFIG constants and unserialize it
                $strSerialArray = constant($strConstantName);
                $objConfigArray = unserialize($strSerialArray);

                // Set All Expected Keys
                foreach ($strExpectedKeys as $strExpectedKey) {
                    if (!array_key_exists($strExpectedKey, $objConfigArray)) {
                        $objConfigArray[$strExpectedKey] = null;
                    }
                }

                if (!$objConfigArray['adapter']) {
                    throw new \Exception('No Adapter Defined for ' . $strConstantName . ': ' . var_export($objConfigArray,
                            true));
                }

                if (!$objConfigArray['server']) {
                    throw new \Exception('No Server Defined for ' . $strConstantName . ': ' . constant($strConstantName));
                }

                $strDatabaseType = 'QCubed\\Database\\' . $objConfigArray['adapter'] . '\\Database';

                //if (!class_exists($strDatabaseType)) {
                //	throw new \Exception('Database adapter was not found: ' . $objConfigArray['adapter']);
                //}

                self::$Database[$intIndex] = new $strDatabaseType($intIndex, $objConfigArray);
            }
        }
    }

    /**
     * @param $intIndex
     * @return DatabaseBase|null
     */
    public static function getDatabase($intIndex)
    {
        if (isset(self::$Database[$intIndex])) {
            return self::$Database[$intIndex];
        } else {
            return null;
        }
    }

    public static function isInitialized()
    {
        return !empty(self::$Database);
    }

    public static function count()
    {
        return count(self::$Database);
    }
}