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
 * Class AbstractComparison
 * @package QCubed\Query\Condition
 * @was QQConditionComparison
 */
abstract class AbstractComparison extends AbstractBase implements ConditionInterface {
	/** @var Node\Column */
	public $objQueryNode;
	public $mixOperand;

	/**
	 * @param Node\Column $objQueryNode
	 * @param mixed $mixOperand
	 * @throws InvalidCast
	 */
	public function __construct(Node\Column $objQueryNode, $mixOperand = null) {
		$this->objQueryNode = $objQueryNode;

		if ($mixOperand instanceof Node\NamedValue || $mixOperand === null)
			$this->mixOperand = $mixOperand;
		else if ($mixOperand instanceof Node\Association)
			throw new InvalidCast('Comparison operand cannot be an Association-based Node', 3);
		else if ($mixOperand instanceof iCondition)
			throw new InvalidCast('Comparison operand cannot be a Condition', 3);
		else if ($mixOperand instanceof Clause\ClauseInterface)
			throw new InvalidCast('Comparison operand cannot be a Clause', 3);
		else if (!($mixOperand instanceof Node\AbstractBase)) {
			$this->mixOperand = $mixOperand;
		} else {
			if (!($mixOperand instanceof Node\Column))
				throw new InvalidCast('Unable to cast "' . $mixOperand->_Name . '" table to Column-based QQNode', 3);
			$this->mixOperand = $mixOperand;
		}
	}
	public function UpdateQueryBuilder(Builder $objBuilder) {
		$objBuilder->AddWhereItem($this->objQueryNode->GetColumnAlias($objBuilder) . $this->strOperator . Node\AbstractBase::GetValue($this->mixOperand, $objBuilder));
	}

	/**
	 * Used by conditional joins to make sure the join conditions only apply to given table.
	 * @param $strTableName
	 * @returns bool
	 */
	public function EqualTables($strTableName) {
		return $this->objQueryNode->GetTable() == $strTableName;
	}
}
