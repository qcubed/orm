<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Clause;

use QCubed\AbstractBase;
use QCubed\Exception\Caller;
use QCubed\Query\Builder;
use QCubed\Query\Condition\ConditionInterface as iCondition;
use QCubed\Query\Node;

/**
 * Class ExpandAsArray
 * @package QCubed\Query\Clause
 * @was QQExpandAsArray
 */
class ExpandAsArray extends AbstractBase implements ClauseInterface {
	/** @var Node\AbstractBase */
	protected $objNode;
	protected $objCondition;
	protected $objSelect;

	/**
	 * ExpandAsArray constructor.
	 * @param Node\AbstractBase $objNode
	 * @param null $objCondition
	 * @param Select|null $objSelect
	 * @throws Caller
	 */
	public function __construct(Node\AbstractBase $objNode, $objCondition = null, Select $objSelect = null) {
		// For backwards compatibility with v2, which did not have a condition parameter, we will detect what the 2nd param is.
		// Ensure that this is an Association
		if ((!($objNode instanceof Node\Association)) && (!($objNode instanceof Node\ReverseReference)))
			throw new Caller('ExpandAsArray clause parameter must be an Association or ReverseReference node', 2);

		if ($objCondition instanceof Select) {
			$this->objNode = $objNode;
			$this->objSelect = $objCondition;
		} else {
			if (!is_null($objCondition)) {
				/*
				if ($objNode instanceof Association) {
					throw new Caller('Join conditions can only be applied to reverse reference nodes here. Try putting a condition on the next level down.', 2);
				}*/
				if (!($objCondition instanceof iCondition)) {
					throw new Caller('Condition clause parameter must be a iCondition dervied class.', 2);
				}
			}
			$this->objNode = $objNode;
			$this->objSelect = $objSelect;
			$this->objCondition = $objCondition;
		}

	}
	public function UpdateQueryBuilder(Builder $objBuilder) {
		if ($this->objNode instanceof Node\Association) {
			// The below works because all code generated association nodes will have a _ChildTableNode parameter.
			// TODO: Make this an interface
			$this->objNode->_ChildTableNode->Join($objBuilder, true, $this->objCondition, $this->objSelect);
		}
		else {
			$this->objNode->Join($objBuilder, true, $this->objCondition, $this->objSelect);
		}
		$objBuilder->AddExpandAsArrayNode($this->objNode);
	}
	public function __toString() {
		return 'ExpandAsArray Clause';
	}
}
