<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Clause;

/**
 * Class Count
 * Count aggregate items
 * @package QCubed\Query\Clause
 * @was QQCount
 */
class Count extends AggregationBase
{
    protected $strFunctionName = 'COUNT';

    public function __toString()
    {
        return 'Count Clause';
    }
}

