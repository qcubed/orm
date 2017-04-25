<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */


namespace QCubed\Query;

use QCubed\Exception\Caller;
use QCubed\Query\Node;
use QCubed\Query\Clause;
use QCubed\Query\Condition\ConditionInterface as iCondition;
use QCubed\Database;

/**
 * Builder class
 * @property \QCubed\Database\AbstractBase $Database
 * @property string $RootTableName
 * @property string[] $ColumnAliasArray
 * @property Node\AbstractBase $ExpandAsArrayNode
 * @was QQueryBuilder
 */
class Builder extends \QCubed\AbstractBase {
	/** @var string[]  */
	protected $strSelectArray;
	/** @var string[]  */
	protected $strColumnAliasArray;
	/** @var int  */
	protected $intColumnAliasCount = 0;
	/** @var string[]  */
	protected $strTableAliasArray;
	/** @var int  */
	protected $intTableAliasCount = 0;
	/** @var string[] */
	protected $strFromArray;
	/** @var string[] */
	protected $strJoinArray;
	/** @var string[] */
	protected $strJoinConditionArray;
	/** @var string[] */
	protected $strWhereArray;
	/** @var string[] */
	protected $strOrderByArray;
	/** @var string[] */
	protected $strGroupByArray;
	/** @var string[] */
	protected $strHavingArray;
	/** @var Node\Virtual[] */
	protected $objVirtualNodeArray;
	/** @var  string */
	protected $strLimitInfo;
	/** @var  bool */
	protected $blnDistinctFlag;
	/** @var  Node\AbstractBase */
	protected $objExpandAsArrayNode;
	/** @var  bool */
	protected $blnCountOnlyFlag;

	/** @var \QCubed\Database\AbstractBase  */
	protected $objDatabase;
	/** @var string  */
	protected $strRootTableName;
	/** @var string  */
	protected $strEscapeIdentifierBegin;
	/** @var string  */
	protected $strEscapeIdentifierEnd;
	/** @var  Clause\OrderBy */
	protected $objOrderByClause;

	/**
	 * @param \QCubed\Database\AbstractBase $objDatabase
	 * @param string $strRootTableName
	 */
	public function __construct(Database\AbstractBase $objDatabase, $strRootTableName) {
		$this->objDatabase = $objDatabase;
		$this->strEscapeIdentifierBegin = $objDatabase->EscapeIdentifierBegin;
		$this->strEscapeIdentifierEnd = $objDatabase->EscapeIdentifierEnd;
		$this->strRootTableName = $strRootTableName;

		$this->strSelectArray = array();
		$this->strColumnAliasArray = array();
		$this->strTableAliasArray = array();
		$this->strFromArray = array();
		$this->strJoinArray = array();
		$this->strJoinConditionArray = array();
		$this->strWhereArray = array();
		$this->strOrderByArray = array();
		$this->strGroupByArray = array();
		$this->strHavingArray = array();
		$this->objVirtualNodeArray = array();
	}

	/**
	 * @param string $strTableName
	 * @param string $strColumnName
	 * @param string $strFullAlias
	 */
	public function AddSelectItem($strTableName, $strColumnName, $strFullAlias) {
		$strTableAlias = $this->GetTableAlias($strTableName);

		if (!array_key_exists($strFullAlias, $this->strColumnAliasArray)) {
			$strColumnAlias = 'a' . $this->intColumnAliasCount++;
			$this->strColumnAliasArray[$strFullAlias] = $strColumnAlias;
		} else {
			$strColumnAlias = $this->strColumnAliasArray[$strFullAlias];
		}

		$this->strSelectArray[$strFullAlias] = sprintf('%s%s%s.%s%s%s AS %s%s%s',
			$this->strEscapeIdentifierBegin, $strTableAlias, $this->strEscapeIdentifierEnd,
			$this->strEscapeIdentifierBegin, $strColumnName, $this->strEscapeIdentifierEnd,
			$this->strEscapeIdentifierBegin, $strColumnAlias, $this->strEscapeIdentifierEnd);
	}

	/**
	 * @param string $strFunctionName
	 * @param string $strColumnName
	 * @param string $strFullAlias
	 */
	public function AddSelectFunction($strFunctionName, $strColumnName, $strFullAlias) {
		$this->strSelectArray[$strFullAlias] = sprintf('%s(%s) AS %s__%s%s',
			$strFunctionName, $strColumnName,
			$this->strEscapeIdentifierBegin, $strFullAlias, $this->strEscapeIdentifierEnd);
	}

	/**
	 * @param string $strTableName
	 */
	public function AddFromItem($strTableName) {
		$strTableAlias = $this->GetTableAlias($strTableName);

		$this->strFromArray[$strTableName] = sprintf('%s%s%s AS %s%s%s',
			$this->strEscapeIdentifierBegin, $strTableName, $this->strEscapeIdentifierEnd,
			$this->strEscapeIdentifierBegin, $strTableAlias, $this->strEscapeIdentifierEnd);
	}

	/**
	 * @param string $strTableName
	 * @return string
	 */
	public function GetTableAlias($strTableName) {
		if (!array_key_exists($strTableName, $this->strTableAliasArray)) {
			$strTableAlias = 't' . $this->intTableAliasCount++;
			$this->strTableAliasArray[$strTableName] = $strTableAlias;
			return $strTableAlias;
		} else {
			return $this->strTableAliasArray[$strTableName];
		}
	}

	/**
	 * @param string $strJoinTableName
	 * @param  string $strJoinTableAlias
	 * @param  string $strTableName
	 * @param  string $strColumnName
	 * @param  string $strLinkedColumnName
	 * @param iCondition|null $objJoinCondition
	 * @throws Caller
	 */
	public function AddJoinItem($strJoinTableName, $strJoinTableAlias, $strTableName, $strColumnName, $strLinkedColumnName, iCondition $objJoinCondition = null) {
		$strJoinItem = sprintf('LEFT JOIN %s%s%s AS %s%s%s ON %s%s%s.%s%s%s = %s%s%s.%s%s%s',
			$this->strEscapeIdentifierBegin, $strJoinTableName, $this->strEscapeIdentifierEnd,
			$this->strEscapeIdentifierBegin, $this->GetTableAlias($strJoinTableAlias), $this->strEscapeIdentifierEnd,

			$this->strEscapeIdentifierBegin, $this->GetTableAlias($strTableName), $this->strEscapeIdentifierEnd,
			$this->strEscapeIdentifierBegin, $strColumnName, $this->strEscapeIdentifierEnd,

			$this->strEscapeIdentifierBegin, $this->GetTableAlias($strJoinTableAlias), $this->strEscapeIdentifierEnd,
			$this->strEscapeIdentifierBegin, $strLinkedColumnName, $this->strEscapeIdentifierEnd);

		$strJoinIndex = $strJoinItem;
		try {
			$strConditionClause = null;
			if ($objJoinCondition &&
				($strConditionClause = $objJoinCondition->GetWhereClause($this, false)))
				$strJoinItem .= ' AND ' . $strConditionClause;
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}

		/* If this table has already been joined, then we need to check for the following:
			1. Condition wasn't specified before and we aren't specifying one now
				Do Nothing --b/c nothing was changed or updated
			2. Condition wasn't specified before but we ARE specifying one now
				Update the indexed item in the joinArray with the new JoinItem WITH Condition
			3. Condition WAS specified before but we aren't specifying one now
				Do Nothing -- we need to keep the old condition intact
			4. Condition WAS specified before and we are specifying the SAME one now
				Do Nothing --b/c nothing was changed or updated
			5. Condition WAS specified before and we are specifying a DIFFERENT one now
				Throw exception
		*/
		if (array_key_exists($strJoinIndex, $this->strJoinArray)) {
			// Case 1 and 2
			if (!array_key_exists($strJoinIndex, $this->strJoinConditionArray)) {

				// Case 1
				if (!$strConditionClause) {
					return;

					// Case 2
				} else {
					$this->strJoinArray[$strJoinIndex] = $strJoinItem;
					$this->strJoinConditionArray[$strJoinIndex] = $strConditionClause;
					return;
				}
			}

			// Case 3
			if (!$strConditionClause)
				return;

			// Case 4
			if ($strConditionClause == $this->strJoinConditionArray[$strJoinIndex])
				return;

			// Case 5
			throw new Caller('You have two different Join Conditions on the same Expanded Table: ' . $strJoinIndex . "\r\n[" . $this->strJoinConditionArray[$strJoinIndex] . ']   vs.   [' . $strConditionClause . ']');
		}

		// Create the new JoinItem in the JoinArray
		$this->strJoinArray[$strJoinIndex] = $strJoinItem;

		// If there is a condition, record that condition against this JoinIndex
		if ($strConditionClause)
			$this->strJoinConditionArray[$strJoinIndex] = $strConditionClause;
	}

	/**
	 * @param  string $strJoinTableName
	 * @param  string $strJoinTableAlias
	 * @param iCondition $objJoinCondition
	 * @throws Caller
	 */
	public function AddJoinCustomItem($strJoinTableName, $strJoinTableAlias, iCondition $objJoinCondition) {
		$strJoinItem = sprintf('LEFT JOIN %s%s%s AS %s%s%s ON ',
			$this->strEscapeIdentifierBegin, $strJoinTableName, $this->strEscapeIdentifierEnd,
			$this->strEscapeIdentifierBegin, $this->GetTableAlias($strJoinTableAlias), $this->strEscapeIdentifierEnd
		);

		$strJoinIndex = $strJoinItem;

		try {
			if (($strConditionClause = $objJoinCondition->GetWhereClause($this, true)))
				$strJoinItem .= ' AND ' . $strConditionClause;
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}

		$this->strJoinArray[$strJoinIndex] = $strJoinItem;
	}

	/**
	 * @param  string $strSql
	 */
	public function AddJoinCustomSqlItem($strSql) {
		$this->strJoinArray[$strSql] = $strSql;
	}

	/**
	 * @param  string $strItem
	 */
	public function AddWhereItem($strItem) {
		array_push($this->strWhereArray, $strItem);
	}

	/**
	 * @param  string $strItem
	 */
	public function AddOrderByItem($strItem) {
		array_push($this->strOrderByArray, $strItem);
	}

	/**
	 * @param  string $strItem
	 */
	public function AddGroupByItem($strItem) {
		array_push($this->strGroupByArray, $strItem);
	}

	/**
	 * @param  string $strItem
	 */
	public function AddHavingItem ($strItem) {
		array_push($this->strHavingArray, $strItem);
	}

	/**
	 * @param $strLimitInfo
	 */
	public function SetLimitInfo($strLimitInfo) {
		$this->strLimitInfo = $strLimitInfo;
	}

	public function SetDistinctFlag() {
		$this->blnDistinctFlag = true;
	}

	public function SetCountOnlyFlag() {
		$this->blnCountOnlyFlag = true;
	}

	/**
	 * @param $strName
	 * @param Node\Column $objNode
	 */
	public function SetVirtualNode($strName, Node\Column $objNode) {
		$this->objVirtualNodeArray[QQ::GetVirtualAlias($strName)] = $objNode;
	}

	/**
	 * @param string $strName
	 * @return Node\Column
	 * @throws Caller
	 */
	public function GetVirtualNode($strName) {
		$strName = QQ::GetVirtualAlias($strName);
		if (isset($this->objVirtualNodeArray[$strName])) {
			return $this->objVirtualNodeArray[$strName];
		}
		else throw new Caller('Undefined Virtual Node: ' . $strName);
	}

	/**
	 * @param Node\AbstractBase $objNode
	 * @throws Caller
	 */
	public function AddExpandAsArrayNode(Node\AbstractBase $objNode) {
		/** @var Node\ReverseReference|Node\Association $objNode */
		// build child nodes and find top node of given node
		$objNode->ExpandAsArray = true;
		while ($objNode->_ParentNode) {
			$objNode = $objNode->_ParentNode;
		}

		if (!$this->objExpandAsArrayNode) {
			$this->objExpandAsArrayNode = $objNode;
		}
		else {
			// integrate the information into current nodes
			$this->objExpandAsArrayNode->_MergeExpansionNode ($objNode);
		}
	}

	/**
	 * @return string
	 */
	public function GetStatement() {
		$this->ProcessClauses();

		// SELECT Clause
		if ($this->blnCountOnlyFlag) {
			if ($this->blnDistinctFlag) {
				$strSql = "SELECT\r\n    COUNT(*) AS q_row_count\r\n" .
					"FROM    (SELECT DISTINCT ";
				$strSql .= "    " . implode(",\r\n    ", $this->strSelectArray);
			} else
				$strSql = "SELECT\r\n    COUNT(*) AS q_row_count\r\n";
		} else {
			if ($this->blnDistinctFlag)
				$strSql = "SELECT DISTINCT\r\n";
			else
				$strSql = "SELECT\r\n";
			if ($this->strLimitInfo)
				$strSql .= $this->objDatabase->SqlLimitVariablePrefix($this->strLimitInfo) . "\r\n";
			$strSql .= "    " . implode(",\r\n    ", $this->strSelectArray);
		}

		// FROM and JOIN Clauses
		$strSql .= sprintf("\r\nFROM\r\n    %s\r\n    %s",
			implode(",\r\n    ", $this->strFromArray),
			implode("\r\n    ", $this->strJoinArray));

		// WHERE Clause
		if (count($this->strWhereArray)) {
			$strWhere = implode("\r\n    ", $this->strWhereArray);
			if (trim($strWhere) != '1=1')
				$strSql .= "\r\nWHERE\r\n    " . $strWhere;
		}

		// Additional Ordering/Grouping/Having clauses
		if (count($this->strGroupByArray))
			$strSql .= "\r\nGROUP BY\r\n    " . implode(",\r\n    ", $this->strGroupByArray);
		if (count($this->strHavingArray)) {
			$strHaving = implode("\r\n    ", $this->strHavingArray);
			$strSql .= "\r\nHaving\r\n    " . $strHaving;
		}
		if (count($this->strOrderByArray))
			$strSql .= "\r\nORDER BY\r\n    " . implode(",\r\n    ", $this->strOrderByArray);

		// Limit Suffix (if applicable)
		if ($this->strLimitInfo)
			$strSql .= "\r\n" . $this->objDatabase->SqlLimitVariableSuffix($this->strLimitInfo);

		// For Distinct Count Queries
		if ($this->blnCountOnlyFlag && $this->blnDistinctFlag)
			$strSql .= "\r\n) as q_count_table";

		return $strSql;
	}

	/**
	 * Sets the one order by clause allowed in a query. Stores it for delayed processing.
	 *
	 * @param Clause\OrderBy $objOrderByClause
	 * @throws Caller
	 */
	public function SetOrderByClause(Clause\OrderBy $objOrderByClause) {
		if ($this->objOrderByClause) {
			throw new Caller('You can only have one OrderBy clause in a query.');
		}
		$this->objOrderByClause = $objOrderByClause;
	}
	/**
	 * Final processing of delayed clauses. Clauses like OrderBy need to wait to be processed until the complete
	 * set of aliases is known.
	 */
	protected function ProcessClauses() {
		if ($this->objOrderByClause) {
			$this->objOrderByClause->_UpdateQueryBuilder($this);
		}
	}

	public function __get($strName) {
		switch ($strName) {
			case 'Database':
				return $this->objDatabase;
			case 'RootTableName':
				return $this->strRootTableName;
			case 'ColumnAliasArray':
				return $this->strColumnAliasArray;
			case 'ExpandAsArrayNode':
				return $this->objExpandAsArrayNode;

			default:
				try {
					return parent::__get($strName);
				} catch (Caller $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
		}
	}
}