<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Node;

use QCubed\Exception\Caller;
use QCubed\Query\Builder;
use QCubed\Query\Condition\ConditionInterface as iCondition;
use QCubed\Type;

/**
 * Class AbstractSubQuery
 * This empty class serves as a superclass organizing all the sub query classes into a group.
 *
 * @package QCubed\Query\Node
 * @was QQSubQueryNode
 */
abstract class AbstractSubQuery extends Column {
}
