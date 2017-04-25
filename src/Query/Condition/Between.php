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
class Between extends AbstractComparison {
	/** @var  mixed */
	protected $mixOperandTwo;

	/**
	 * @param Node\Column $objQueryNode
	 * @param mixed|null $mixMinValue
	 * @param $mixMaxValue
	 * @throws Caller
	 */
	public function __construct(Node\Column $objQueryNode, $mixMinValue, $mixMaxValue) {
		parent::__construct($objQueryNode);
		try {
			$this->mixOperand = $mixMinValue;
			$this->mixOperandTwo = $mixMaxValue;
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			$objExc->IncrementOffset();
			throw $objExc;
		}
	}

	/**
	 * @param Builder $objBuilder
	 */
	public function UpdateQueryBuilder(Builder $objBuilder) {
		$mixOperand = $this->mixOperand;
		$mixOperandTwo = $this->mixOperandTwo;
		if ($mixOperand instanceof Node\NamedValue) {
			/** @var Node\NamedValue $mixOperand */
			/** @var Node\NamedValue $mixOperandTwo */
			$objBuilder->AddWhereItem($this->objQueryNode->GetColumnAlias($objBuilder) . ' BETWEEN ' . $mixOperand->Parameter() . ' AND ' . $mixOperandTwo->Parameter());
		} else {
			$objBuilder->AddWhereItem($this->objQueryNode->GetColumnAlias($objBuilder) . ' BETWEEN ' . $objBuilder->Database->SqlVariable($mixOperand) . ' AND ' . $objBuilder->Database->SqlVariable($mixOperandTwo));
		}
	}
}
