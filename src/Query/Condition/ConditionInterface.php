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

/**
 * Interface ConditionInterface
 * @package QCubed\Query\Condition
 *
 * This interface is here simply to let parts of the framework refer to a general condition as a ConditionInterface,
 * instead of a Condition\Base class, which is just ugly.
 */
interface ConditionInterface
{
    public function updateQueryBuilder(Builder $objBuilder);

    public function __toString();

    public function getWhereClause(Builder $objBuilder, $blnProcessOnce = false);

    public function equalTables($strTableName);
}