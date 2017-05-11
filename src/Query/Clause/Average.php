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
 * Class Average
 * @package QCubed\Query\Clause
 * @was QQAverage
 */
class Average extends AggregationBase
{
    protected $strFunctionName = 'AVG';

    public function __toString()
    {
        return 'Average Clause';
    }
}
