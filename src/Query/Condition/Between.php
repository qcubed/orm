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
use QCubed\Query\Node;

/**
 * Class Between
 * Represent a test for an item being between two values.
 * Note that different SQLs treat this clause differently, and may produce different results. Its not transportable.
 * @package QCubed\Query\Condition
 * @was QQConditionBetween
 */
class Between extends ComparisonBase
{
    /** @var  mixed */
    protected $mixOperandTwo;

    /**
     * @param Node\Column $objQueryNode
     * @param mixed|null $mixMinValue
     * @param $mixMaxValue
     * @throws Caller
     */
    public function __construct(Node\Column $objQueryNode, $mixMinValue, $mixMaxValue)
    {
        parent::__construct($objQueryNode);
        try {
            $this->mixOperand = $mixMinValue;
            $this->mixOperandTwo = $mixMaxValue;
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            $objExc->incrementOffset();
            throw $objExc;
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
            $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' BETWEEN ' . $mixOperand->parameter() . ' AND ' . $mixOperandTwo->parameter());
        } else {
            $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' BETWEEN ' . $objBuilder->Database->sqlVariable($mixOperand) . ' AND ' . $objBuilder->Database->sqlVariable($mixOperandTwo));
        }
    }
}
