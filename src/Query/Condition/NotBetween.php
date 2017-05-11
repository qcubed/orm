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
use QCubed\Type;
use QCubed\Query\Node;


/**
 * Class NotBetween
 * Represent a test for an item being between two values.
 * Note that different SQLs treat this clause differently, and may produce different results. Its not transportable.
 * @package QCubed\Query\Condition
 * @was QQConditionNotBetween
 */
class NotBetween extends ComparisonBase
{
    /** @var mixed */
    protected $mixOperandTwo;

    /**
     * @param Node\Column $objQueryNode
     * @param string $strMinValue
     * @param string $strMaxValue
     * @throws Caller
     */
    public function __construct(Node\Column $objQueryNode, $strMinValue, $strMaxValue)
    {
        parent::__construct($objQueryNode);
        try {
            $this->mixOperand = Type::cast($strMinValue, Type::STRING);
            $this->mixOperandTwo = Type::cast($strMaxValue, Type::STRING);
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            $objExc->incrementOffset();
            throw $objExc;
        }

        if ($strMinValue instanceof Node\NamedValue) {
            $this->mixOperand = $strMinValue;
        }
        if ($strMaxValue instanceof Node\NamedValue) {
            $this->mixOperandTwo = $strMaxValue;
        }

    }

    /**
     * @param Builder $objBuilder
     */
    public function updateQueryBuilder(Builder $objBuilder)
    {
        $mixOperand = $this->mixOperand;
        $mixOperandTwo = $this->mixOperandTwo;
        if ($mixOperand instanceof Node\NamedValue) {
            /** @var Node\NamedValue $mixOperand */
            /** @var Node\NamedValue $mixOperandTwo */
            $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' NOT BETWEEN ' . $mixOperand->parameter() . ' AND ' . $mixOperandTwo->parameter());
        } else {
            $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' NOT BETWEEN ' . $objBuilder->Database->sqlVariable($mixOperand) . ' AND ' . $objBuilder->Database->sqlVariable($mixOperandTwo));
        }
    }
}
