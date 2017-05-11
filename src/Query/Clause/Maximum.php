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
 * Class Maximum
 * @package QCubed\Query\Clause
 * @was QQMaximum
 */
class Maximum extends AggregationBase
{
    protected $strFunctionName = 'MAX';

    public function __toString()
    {
        return 'Maximum Clause';
    }
}
