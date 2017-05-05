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
 * Class Like
 * Represent a test for a SQL Like function.
 * @package QCubed\Query\Condition
 * @was QQConditionLike
 */
class Like extends ComparisonBase
{
    /**
     * @param Node\Column $objQueryNode
     * @param string $strValue
     * @throws Caller
     */
    public function __construct(Node\Column $objQueryNode, $strValue)
    {
        parent::__construct($objQueryNode);

        if ($strValue instanceof Node\NamedValue) {
            $this->mixOperand = $strValue;
        } else {
            try {
                $this->mixOperand = Type::cast($strValue, Type::STRING);
            } catch (Caller $objExc) {
                $objExc->incrementOffset();
                $objExc->incrementOffset();
                throw $objExc;
            }
        }
    }

    /**
     * @param Builder $objBuilder
     */
    public function updateQueryBuilder(Builder $objBuilder)
    {
        $mixOperand = $this->mixOperand;
        if ($mixOperand instanceof Node\NamedValue) {
            /** @var Node\NamedValue $mixOperand */
            $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' LIKE ' . $mixOperand->parameter());
        } else {
            $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' LIKE ' . $objBuilder->Database->sqlVariable($mixOperand));
        }
    }
}
