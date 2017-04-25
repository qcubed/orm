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
use QCubed\Query\Node\Virtual;

/**
 * Class ExpandVirtualNode
 * Node representing an expansion on a virtual node
 * @package QCubed\Query\Clause
 * @was QQExpandVirtualNode
 */
class ExpandVirtualNode extends AbstractBase implements ClauseInterface {
	protected $objNode;
	public function __construct(Virtual $objNode) {
		$this->objNode = $objNode;
	}
	public function UpdateQueryBuilder(Builder $objBuilder) {
		try {
			$objBuilder->AddSelectFunction(null, $this->objNode->GetColumnAlias($objBuilder), $this->objNode->GetAttributeName());
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}
	}
	public function __toString() {
		return 'QQExpandVirtualNode Clause';
	}
}
