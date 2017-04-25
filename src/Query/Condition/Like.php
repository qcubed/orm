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
class Like extends AbstractComparison {
	/**
	 * @param Node\Column $objQueryNode
	 * @param string $strValue
	 * @throws Caller
	 */
	public function __construct(Node\Column $objQueryNode, $strValue) {
		parent::__construct($objQueryNode);

		if ($strValue instanceof Node\NamedValue)
			$this->mixOperand = $strValue;
		else {
			try {
				$this->mixOperand = Type::Cast($strValue, Type::String);
			} catch (Caller $objExc) {
				$objExc->IncrementOffset();
				$objExc->IncrementOffset();
				throw $objExc;
			}
		}
	}

	/**
	 * @param Builder $objBuilder
	 */
	public function UpdateQueryBuilder(Builder $objBuilder) {
		$mixOperand = $this->mixOperand;
		if ($mixOperand instanceof Node\NamedValue) {
			/** @var Node\NamedValue $mixOperand */
			$objBuilder->AddWhereItem($this->objQueryNode->GetColumnAlias($objBuilder) . ' LIKE ' . $mixOperand->Parameter());
		} else {
			$objBuilder->AddWhereItem($this->objQueryNode->GetColumnAlias($objBuilder) . ' LIKE ' . $objBuilder->Database->SqlVariable($mixOperand));
		}
	}
}
