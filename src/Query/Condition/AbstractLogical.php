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
use QCubed\Type;

/**
 * Class AbstractLogical
 * @package QCubed\Query\Condition
 * @was QQConditionLogical
 */
abstract class AbstractLogical extends AbstractBase implements ConditionInterface {
	/** @var iCondition[] */
	protected $objConditionArray;

	public function __construct($mixParameterArray) {
		$objConditionArray = $this->CollapseConditions($mixParameterArray);
		try {
			$this->objConditionArray = Type::Cast($objConditionArray, Type::ArrayType);
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}
	}

	public function UpdateQueryBuilder(Builder $objBuilder) {
		$intLength = count($this->objConditionArray);
		if ($intLength) {
			$objBuilder->AddWhereItem('(');
			for ($intIndex = 0; $intIndex < $intLength; $intIndex++) {
				if (!($this->objConditionArray[$intIndex] instanceof iCondition))
					throw new Caller($this->strOperator . ' clause has elements that are not Conditions');
				try {
					$this->objConditionArray[$intIndex]->UpdateQueryBuilder($objBuilder);
				} catch (Caller $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
				if (($intIndex + 1) != $intLength)
					$objBuilder->AddWhereItem($this->strOperator);
			}
			$objBuilder->AddWhereItem(')');
		}
	}

	protected function CollapseConditions($mixParameterArray) {
		$objConditionArray = array();
		foreach ($mixParameterArray as $mixParameter) {
			if (is_array($mixParameter))
				$objConditionArray = array_merge($objConditionArray, $mixParameter);
			else
				array_push($objConditionArray, $mixParameter);
		}

		foreach ($objConditionArray as $objCondition)
			if (!($objCondition instanceof iCondition))
				throw new Caller('Logical Or/And clause parameters must all be iCondition objects', 3);

		if (count($objConditionArray))
			return $objConditionArray;
		else
			throw new Caller('No parameters passed in to logical Or/And clause', 3);
	}

	public function EqualTables($strTableName) {
		foreach ($this->objConditionArray as $objCondition) {
			if (!$objCondition->EqualTables($strTableName)) {
				return false;
			}
		}
		return true;
	}
}