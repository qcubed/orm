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

/**
 * Class Not
 * @package QCubed\Query\Condition
 * @was QQConditionNot
 */
class Not extends AbstractLogical {
	public function __construct(iCondition $objCondition) {
		parent::__construct([$objCondition]);
	}
	public function UpdateQueryBuilder(Builder $objBuilder) {
		$objBuilder->AddWhereItem('(NOT');
		try {
			$this->objConditionArray[0]->UpdateQueryBuilder($objBuilder);
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}
		$objBuilder->AddWhereItem(')');
	}
}
