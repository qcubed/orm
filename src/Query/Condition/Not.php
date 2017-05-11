<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Condition;

use QCubed\Exception\Caller;
use QCubed\Query\Builder;
use QCubed\Query\Condition\ConditionInterface as iCondition;

/**
 * Class Not
 * @package QCubed\Query\Condition
 * @was QQConditionNot
 */
class Not extends LogicalBase
{
    public function __construct(iCondition $objCondition)
    {
        parent::__construct([$objCondition]);
    }

    public function updateQueryBuilder(Builder $objBuilder)
    {
        $objBuilder->addWhereItem('(NOT');
        try {
            $this->objConditionArray[0]->updateQueryBuilder($objBuilder);
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
        $objBuilder->addWhereItem(')');
    }
}
