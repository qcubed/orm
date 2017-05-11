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
use QCubed\ObjectBase;
use QCubed\Query\Builder;
use QCubed\Query\PartialBuilder;

/**
 * Class Base
 * @package QCubed\Query\Condition
 * @abstract
 * @was QQCondition
 */
abstract class ConditionBase extends ObjectBase
{
    protected $strOperator;
    protected $blnProcessed;

    abstract public function updateQueryBuilder(Builder $objBuilder);

    public function __toString()
    {
        return 'QQCondition Object';
    }


    /**
     * Used internally by QCubed Query to get an individual where clause for a given condition
     * Mostly used for conditional joins.
     *
     * @param Builder $objBuilder
     * @param bool|false $blnProcessOnce
     * @return null|string
     * @throws \Exception
     * @throws Caller
     */
    public function getWhereClause(Builder $objBuilder, $blnProcessOnce = false)
    {
        if ($blnProcessOnce && $this->blnProcessed) {
            return null;
        }

        $this->blnProcessed = true;

        try {
            $objConditionBuilder = new PartialBuilder($objBuilder);
            $this->updateQueryBuilder($objConditionBuilder);
            return $objConditionBuilder->getWhereStatement();
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

    /**
     * @abstract
     * @param string $strTableName
     * @return bool
     */
    public function equalTables($strTableName)
    {
        return true;
    }
}

