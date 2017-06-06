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
 * Class OptimisticLocking
 * Thrown when optimistic locking (in ORM Save() method) detects that DB data was updated
 * @package QCubed\Database\Exception
 * @was QOptimisticLockingException
 */
class OptimisticLocking extends Caller
{
    /**
     * Constructor method
     * @param string $strClass
     */
    public function __construct($strClass)
    {
        parent::__construct(sprintf('Optimistic Locking constraint when trying to update %s object.  To update anyway, call ->save() with $blnForceUpdate set to true', $strClass));
    }
}
