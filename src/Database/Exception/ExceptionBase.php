<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Exception;

use QCubed\Exception\Caller;

/**
 * Class to handle exceptions related to database querying
 * @property-read int $ErrorNumber The number of error provided by the SQL server
 * @property-read string $Query The query caused the error
 * @package DatabaseAdapters
 * @was QDatabaseException
 */
abstract class ExceptionBase extends Caller
{
    /** @var int Error number */
    protected $intErrorNumber;
    /** @var string Query which produced the error */
    protected $strQuery;

    /**
     * PHP magic function to get property values
     * @param string $strName
     *
     * @return array|int|mixed
     */
    public function __get($strName)
    {
        switch ($strName) {
            case "ErrorNumber":
                return $this->intErrorNumber;
            case "Query":
                return $this->strQuery;
            default:
                return parent::__get($strName);
        }
    }
}
