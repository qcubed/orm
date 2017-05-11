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
 * Class Sum
 * @package QCubed\Query\Clause
 * @was QQSum
 */
class Sum extends AggregationBase
{
    protected $strFunctionName = 'SUM';

    public function __toString()
    {
        return 'QQSum Clause';
    }
}
