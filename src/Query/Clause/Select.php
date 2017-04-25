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
use QCubed\Query\Node;

/**
 * Class Select
 * @package QCubed\Query\Clause
 * @was Select
 */
class Select extends AbstractBase implements ClauseInterface {
	/** @var Node\AbstractBase[] */
	protected $arrNodeObj = array();
	protected $blnSkipPrimaryKey = false;

	/**
	 * @param Node\AbstractBase[] $arrNodeObj
	 * @throws Caller
	 */
	public function __construct($arrNodeObj) {
		$this->arrNodeObj = $arrNodeObj;
		foreach ($this->arrNodeObj as $objNode) {
			if (!($objNode instanceof Node\Column)) {
				throw new Caller('Select nodes must be column nodes.', 3);
			}
		}
	}

	public function UpdateQueryBuilder(Builder $objBuilder) {
	}

	public function AddSelectItems(Builder $objBuilder, $strTableName, $strAliasPrefix) {
		foreach ($this->arrNodeObj as $objNode) {
			$strNodeTable = $objNode->GetTable();
			if ($strNodeTable == $strTableName) {
				$objBuilder->AddSelectItem($strTableName, $objNode->_Name, $strAliasPrefix . $objNode->_Name);
			}
		}
	}

	public function Merge(Select $objSelect = null) {
		if ($objSelect) {
			foreach ($objSelect->arrNodeObj as $objNode) {
				array_push($this->arrNodeObj, $objNode);
			}
			if ($objSelect->blnSkipPrimaryKey) {
				$this->blnSkipPrimaryKey = true;
			}
		}
	}

	/**
	 * @return boolean
	 */
	public function SkipPrimaryKey() {
		return $this->blnSkipPrimaryKey;
	}

	/**
	 * @param boolean $blnSkipPrimaryKey
	 */
	public function SetSkipPrimaryKey($blnSkipPrimaryKey) {
		$this->blnSkipPrimaryKey = $blnSkipPrimaryKey;
	}

	public function __toString() {
		return 'QQSelectColumn Clause';
	}
}
