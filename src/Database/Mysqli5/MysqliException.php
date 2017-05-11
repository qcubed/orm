<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Mysqli5;

use QCubed\Database\Exception\ExceptionBase;


/**
 * Exception
 * @was QMySqliDatabaseException
 */
class MysqliException extends ExceptionBase
{
    public function __construct($strMessage, $intNumber, $strQuery)
    {
        parent::__construct(sprintf("MySqli Error: %s", $strMessage), 2);
        $this->intErrorNumber = $intNumber;
        $this->strQuery = $strQuery;
    }
}