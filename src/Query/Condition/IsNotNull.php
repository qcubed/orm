<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Condition;

use QCubed\Query\Builder;
use QCubed\Query\Node;

/**
 * Class IsNotNull
 * Represent a test for a not null item in the database.
 * @package QCubed\Query\Condition
 * @was QQConditionIsNotNull
 */
class IsNotNull extends ComparisonBase
{
    public function __construct(Node\Column $objQueryNode)
    {
        parent::__construct($objQueryNode);
    }

    /**
     * @param Builder $objBuilder
     */
    public function updateQueryBuilder(Builder $objBuilder)
    {
        $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' IS NOT NULL');
    }
}
