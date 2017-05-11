<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Condition;

use QCubed\Query\Node;
use QCubed\Query\Builder;

/**
 * Class Equal
 * Represent a test for an item being equal to a value.
 * @package QCubed\Query\Condition
 * @was QQConditionEqual
 */
class Equal extends ComparisonBase
{
    protected $strOperator = ' = ';

    /**
     * @param Builder $objBuilder
     */
    public function updateQueryBuilder(Builder $objBuilder)
    {
        $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' ' . Node\NodeBase::getValue($this->mixOperand,
                $objBuilder, true));
    }
}
