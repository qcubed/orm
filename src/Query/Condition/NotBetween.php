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
class NotBetween extends AbstractComparison {
	/** @var mixed  */
	protected $mixOperandTwo;

	/**
	 * @param Node\Column $objQueryNode
	 * @param string $strMinValue
	 * @param string $strMaxValue
	 * @throws Caller
	 */
	public function __construct(Node\Column $objQueryNode, $strMinValue, $strMaxValue) {
		parent::__construct($objQueryNode);
		try {
			$this->mixOperand = Type::Cast($strMinValue, Type::String);
			$this->mixOperandTwo = Type::Cast($strMaxValue, Type::String);
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			$objExc->IncrementOffset();
			throw $objExc;
		}

		if ($strMinValue instanceof Node\NamedValue)
			$this->mixOperand = $strMinValue;
		if ($strMaxValue instanceof Node\NamedValue)
			$this->mixOperandTwo = $strMaxValue;

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
			$objBuilder->AddWhereItem($this->objQueryNode->GetColumnAlias($objBuilder) . ' NOT BETWEEN ' . $mixOperand->Parameter() . ' AND ' . $mixOperandTwo->Parameter());
		} else {
			$objBuilder->AddWhereItem($this->objQueryNode->GetColumnAlias($objBuilder) . ' NOT BETWEEN ' . $objBuilder->Database->SqlVariable($mixOperand) . ' AND ' . $objBuilder->Database->SqlVariable($mixOperandTwo));
		}
	}
}
