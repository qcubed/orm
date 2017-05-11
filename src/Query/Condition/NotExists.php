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
 * Class NotExists
 * Represent a test for an item being in a set of values.
 * @package QCubed\Query\Condition
 * @was QQConditionNotExists
 */
class NotExists extends ConditionBase
{
    /** @var Node\SubQuerySql */
    protected $objNode;

    /**
     * @param Node\SubQuerySql $objNode
     */
    public function __construct(Node\SubQuerySql $objNode)
    {
        $this->objNode = $objNode;
    }

    /**
     * @param Builder $objBuilder
     */
    public function updateQueryBuilder(Builder $objBuilder)
    {
        $objBuilder->addWhereItem('NOT EXISTS ' . $this->objNode->getColumnAlias($objBuilder));
    }
}
