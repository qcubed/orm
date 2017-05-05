<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Node;

/**
 * Class SubQueryCount
 * @package QCubed\Query\Node
 * @was QQSubQueryCountNode
 */
class SubQueryCount extends SubQueryBase
{
    protected $strFunctionName = 'COUNT';
}
