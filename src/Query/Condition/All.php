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

/**
 * Class All
 * @package QCubed\Query\Condition
 * @was QQConditionAll
 */
class All extends ConditionBase implements ConditionInterface
{
    /**
     * @param $mixParameterArray
     * @throws Caller
     */
    public function __construct($mixParameterArray)
    {
        if (count($mixParameterArray)) {
            throw new Caller('All clause takes in no parameters', 3);
        }
    }

    /**
     * @param Builder $objBuilder
     */
    public function updateQueryBuilder(Builder $objBuilder)
    {
        $objBuilder->addWhereItem('1=1');
    }
}