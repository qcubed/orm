<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Node;

use QCubed\Exception\Caller;
use QCubed\Query\Builder;
use QCubed\Query\QQ;

/**
 * Class Virtual
 * Class to represent a computed value or sub sql expression with an alias that can be used to query and sort
 *
 * @package QCubed\Query\Node
 * @was QQVirtualNode
 */
class Virtual extends AbstractNoParent {
	protected $objSubQueryDefinition;

	/**
	 * @param $strName
	 * @param AbstractSubQuery|null $objSubQueryDefinition
	 */
	public function __construct($strName, AbstractSubQuery $objSubQueryDefinition = null) {
		parent::__construct('', '', '');
		$this->objParentNode = true;
		$this->strName = QQ::GetVirtualAlias($strName);
		$this->strAlias = $this->strName;
		$this->objSubQueryDefinition = $objSubQueryDefinition;
	}

	/**
	 * @param Builder $objBuilder
	 * @return string
	 * @throws Caller
	 */
	public function GetColumnAlias(Builder $objBuilder) {
		if ($this->objSubQueryDefinition) {
			$objBuilder->SetVirtualNode($this->strName, $this->objSubQueryDefinition);
			return $this->objSubQueryDefinition->GetColumnAlias($objBuilder);
		} else {
			try {
				$objNode = $objBuilder->GetVirtualNode($this->strName);
				return $objNode->GetColumnAlias($objBuilder);
			} catch (Caller $objExc) {
				$objExc->IncrementOffset();
				$objExc->IncrementOffset();
				throw $objExc;
			}
		}
	}
	public function GetAttributeName() {
		return $this->strName;
	}

	public function HasSubquery() {
		return $this->objSubQueryDefinition != null;
	}
}
