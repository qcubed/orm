<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Condition;

use QCubed\Query\Node;
use QCubed\Query\Builder;

/**
 * Class Equal
 * Represent a test for an item being equal to a value.
 * @package QCubed\Query\Condition
 * @was QQConditionEqual
 */
class Equal extends AbstractComparison {
	protected $strOperator = ' = ';

	/**
	 * @param Builder $objBuilder
	 */
	public function UpdateQueryBuilder(Builder $objBuilder) {
		$objBuilder->AddWhereItem($this->objQueryNode->GetColumnAlias($objBuilder) . ' ' . Node\AbstractBase::GetValue($this->mixOperand, $objBuilder, true));
	}
}
