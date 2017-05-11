<?php
/**
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Exception;

use QCubed\Exception\Caller;

/**
 * Class UndefinedPrimaryKey
 * Used when trying to access a table object which does not have a primary key defined on it
 * @package QCubed\Exception
 * @was QUndefinedPrimaryKeyException
 */
class UndefinedPrimaryKey extends Caller
{
    /**
     * Constructor method
     * @param string $strMessage
     */
    public function __construct($strMessage)
    {
        parent::__construct($strMessage, 2);
    }
}
