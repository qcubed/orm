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
use QCubed\Type;

/**
 * Class LogicalBase
 * @package QCubed\Query\Condition
 * @was QQConditionLogical
 */
abstract class LogicalBase extends ConditionBase implements ConditionInterface
{
    /** @var iCondition[] */
    protected $objConditionArray;

    public function __construct($mixParameterArray)
    {
        $objConditionArray = $this->collapseConditions($mixParameterArray);
        try {
            $this->objConditionArray = Type::cast($objConditionArray, Type::ARRAY_TYPE);
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

    public function updateQueryBuilder(Builder $objBuilder)
    {
        $intLength = count($this->objConditionArray);
        if ($intLength) {
            $objBuilder->addWhereItem('(');
            for ($intIndex = 0; $intIndex < $intLength; $intIndex++) {
                if (!($this->objConditionArray[$intIndex] instanceof iCondition)) {
                    throw new Caller($this->strOperator . ' clause has elements that are not Conditions');
                }
                try {
                    $this->objConditionArray[$intIndex]->updateQueryBuilder($objBuilder);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
                if (($intIndex + 1) != $intLength) {
                    $objBuilder->addWhereItem($this->strOperator);
                }
            }
            $objBuilder->addWhereItem(')');
        }
    }

    protected function collapseConditions($mixParameterArray)
    {
        $objConditionArray = array();
        foreach ($mixParameterArray as $mixParameter) {
            if (is_array($mixParameter)) {
                $objConditionArray = array_merge($objConditionArray, $mixParameter);
            } else {
                array_push($objConditionArray, $mixParameter);
            }
        }

        foreach ($objConditionArray as $objCondition) {
            if (!($objCondition instanceof iCondition)) {
                throw new Caller('Logical Or/And clause parameters must all be iCondition objects', 3);
            }
        }

        if (count($objConditionArray)) {
            return $objConditionArray;
        } else {
            throw new Caller('No parameters passed in to logical Or/And clause', 3);
        }
    }

    public function equalTables($strTableName)
    {
        foreach ($this->objConditionArray as $objCondition) {
            if (!$objCondition->equalTables($strTableName)) {
                return false;
            }
        }
        return true;
    }
}