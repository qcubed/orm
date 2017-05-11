<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Condition;

use QCubed\Exception\InvalidCast;
use QCubed\Query\Builder;
use QCubed\Query\Condition\ConditionInterface as iCondition;
use QCubed\Query\Node;
use QCubed\Query\Clause;

/**
 * Class ComparisonBase
 * @package QCubed\Query\Condition
 * @was QQConditionComparison
 */
abstract class ComparisonBase extends ConditionBase implements ConditionInterface
{
    /** @var Node\Column */
    public $objQueryNode;
    public $mixOperand;

    /**
     * @param Node\Column $objQueryNode
     * @param mixed $mixOperand
     * @throws InvalidCast
     */
    public function __construct(Node\Column $objQueryNode, $mixOperand = null)
    {
        $this->objQueryNode = $objQueryNode;
        if ($mixOperand instanceof Node\NamedValue || $mixOperand === null) {
            $this->mixOperand = $mixOperand;
        } elseif ($mixOperand instanceof Node\Association) {
            throw new InvalidCast('Comparison operand cannot be an Association-based Node', 3);
        } elseif ($mixOperand instanceof iCondition) {
            throw new InvalidCast('Comparison operand cannot be a Condition', 3);
        } elseif ($mixOperand instanceof Clause\ClauseInterface) {
            throw new InvalidCast('Comparison operand cannot be a Clause', 3);
        } elseif (!($mixOperand instanceof Node\NodeBase)) {
            $this->mixOperand = $mixOperand;
        } elseif (!($mixOperand instanceof Node\Column)) {
            throw new InvalidCast('Unable to cast "' . $mixOperand->_Name . '" table to Column-based QQNode',
                3);
        } else {
            $this->mixOperand = $mixOperand;
        }
    }

    public function updateQueryBuilder(Builder $objBuilder)
    {
        $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . $this->strOperator . Node\NodeBase::getValue($this->mixOperand,
                $objBuilder));
    }

    /**
     * Used by conditional joins to make sure the join conditions only apply to given table.
     * @param $strTableName
     * @returns bool
     */
    public function equalTables($strTableName)
    {
        return $this->objQueryNode->getTable() == $strTableName;
    }
}
