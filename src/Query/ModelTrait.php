<?php

namespace QCubed\Query;

use QCubed\Exception\Caller;
use QCubed\Query\Condition\ConditionInterface as iCondition;
use QCubed\Query\Clause\ClauseInterface as iClause;
use QCubed\Query\Builder;
use QCubed\Query\Node;
use QCubed\Database;
use QCubed\Query\Clause;

/**
 * Class ModelTrait
 *
 * This trait class is a mixin helper for all the generated Model classes. It works together with the code generator
 * to create particular functions that are common to all the classes. For historical reasons, and to prevent problems
 * with polymorhpism, this is a trait and not a base class.
 *
 * Note: We know that this trait has many warnings in it. Some of the problem is due to PHP not allowing
 * abstract static functions. However, QCubed v5 should remove the need for this trait.
 *
 * @was QModelTrait
 */
trait ModelTrait {

	/*** Requirements of Model classes ***/

	/*
	 * The generated model classes must implement the following functions and members.
	 */

	/**
	 * Returns the value of the primary key for this object. If a composite primary key, this should return a string representation
	 * of the combined keys such that the combination will be unique.
	 * @return integer|string
	 */
	// protected function PrimaryKey();

	/**
	 * A helper function to get the primary key associated with this object type from a query result row.
	 *
	 * @param Database\AbstractRow $objDbRow
	 * @param string $strAliasPrefix	Prefix to use if this is a row expansion (as in, a join)
	 * @param string[] $strColumnAliasArray Array of column aliases associateing our column names with the minimized names in the query.
	 * @return mixed The primary key found in the row
	 */
	// protected static function GetRowPrimaryKey($objDbRow, $strAliasPrefix, $strColumnAliasArray){}

	/**
	 * Return the database object associated with this object.
	 *
	 * @return Database\AbstractBase
	 */
	//public static function GetDatabase(){}

	/**
	 * Return the name of the database table associated with this object.
	 *
	 * @return string
	 */
	//public static function GetTableName(){return '';}

	/**
	 * Add select fields to the query as part of the query building process. The superclass should override this to add the necessary fields
	 * to the query builder object. The default is to add all the fields in the object.
	 *
	 * @param Builder $objBuilder
	 * @param string|null $strPrefix	optional prefix to be used if this is an extended query (as in, a join)
	 * @param Clause\Select|null $objSelect optional Clause\Select clause to select specific fields, rather than the entire set of fields in the object
	 */
	//public static function GetSelectFields(Builder $objBuilder, $strPrefix = null, Clause\Select $objSelect = null){}


	/***  Implementation ***/

	/**
	 * Takes a query builder object and outputs the sql query that corresponds to its structure and the given parameters.
	 *
	 * @param Builder &$objQueryBuilder the QueryBuilder object that will be created
	 * @param iCondition $objConditions any conditions on the query, itself
	 * @param iClause[] $objOptionalClauses additional optional iClause object or array of iClause objects for this query
	 * @param mixed[] $mixParameterArray a array of name-value pairs to perform PrepareStatement with (sending in null will skip the PrepareStatement step)
	 * @param boolean $blnCountOnly only select a rowcount
	 * @return string the query statement
	 * @throws Caller
	 */
	protected static function BuildQueryStatement(&$objQueryBuilder, iCondition $objConditions, $objOptionalClauses, $mixParameterArray, $blnCountOnly) {
		// Get the Database Object for this Class
		$objDatabase = static::GetDatabase();
		$strTableName = static::GetTableName();

		// Create/Build out the QueryBuilder object with class-specific SELECT and FROM fields
		$objQueryBuilder = new Builder($objDatabase, $strTableName);

		$blnAddAllFieldsToSelect = true;
		if ($objDatabase->OnlyFullGroupBy) {
			// see if we have any group by or aggregation clauses, if yes, don't add all the fields to select clause by default
			// because these databases post an error instead of just choosing a value to return when a select item could
			// have multiple values
			if ($objOptionalClauses instanceof iClause) {
				if ($objOptionalClauses instanceof Clause\AbstractAggregation || $objOptionalClauses instanceof Clause\GroupBy) {
					$blnAddAllFieldsToSelect = false;
				}
			} else if (is_array($objOptionalClauses)) {
				foreach ($objOptionalClauses as $objClause) {
					if ($objClause instanceof Clause\AbstractAggregation || $objClause instanceof Clause\GroupBy) {
						$blnAddAllFieldsToSelect = false;
						break;
					}
				}
			}
		}

		$objSelectClauses = QQ::ExtractSelectClause($objOptionalClauses);
		if ($objSelectClauses || $blnAddAllFieldsToSelect) {
			static::BaseNode()->PutSelectFields($objQueryBuilder, null, $objSelectClauses);
		}

		$objQueryBuilder->AddFromItem($strTableName);

		// Set "CountOnly" option (if applicable)
		if ($blnCountOnly)
			$objQueryBuilder->SetCountOnlyFlag();

		// Apply Any Conditions
		if ($objConditions)
			try {
				$objConditions->UpdateQueryBuilder($objQueryBuilder);
			} catch (Caller $objExc) {
				$objExc->IncrementOffset();
				$objExc->IncrementOffset();
				throw $objExc;
			}

		// Iterate through all the Optional Clauses (if any) and perform accordingly
		if ($objOptionalClauses) {
			if ($objOptionalClauses instanceof iClause) {
				try {
					$objOptionalClauses->UpdateQueryBuilder($objQueryBuilder);
				} catch (Caller $objExc) {
					$objExc->IncrementOffset();
					$objExc->IncrementOffset();
					throw $objExc;
				}

			}
			else if (is_array($objOptionalClauses)) {
				foreach ($objOptionalClauses as $objClause) {
					try {
						$objClause->UpdateQueryBuilder($objQueryBuilder);
					} catch (Caller $objExc) {
						$objExc->IncrementOffset();
						$objExc->IncrementOffset();
						throw $objExc;
					}

				}
			}
			else
				throw new Caller('Optional Clauses must be a iClause object or an array of iClause objects');
		}

		// Get the SQL Statement
		$strQuery = $objQueryBuilder->GetStatement();

		// Substitute the correct sql variable names for the placeholders specified in the query, if any.
		if ($mixParameterArray) {
			if (is_array($mixParameterArray)) {
				if (count($mixParameterArray))
					$strQuery = $objDatabase->PrepareStatement($strQuery, $mixParameterArray);

				// Ensure that there are no other Unresolved Named Parameters
				if (strpos($strQuery, chr(Node\NamedValue::DelimiterCode) . '{') !== false)
					throw new Caller('Unresolved named parameters in the query');
			} else
				throw new Caller('Parameter Array must be an array of name-value parameter pairs');
		}

		// Return the Objects
		return $strQuery;
	}

	/**
	 * Static Qcubed Query method to query for a single <?php echo $objTable->ClassName  ?> object.
	 * Uses BuildQueryStatment to perform most of the work.
	 * Is called by QuerySincle function of each object so that the correct return type will be put in the comments.
	 * 
	 * @param iCondition $objConditions any conditions on the query, itself
	 * @param null $objOptionalClauses
	 * @param mixed[] $mixParameterArray a array of name-value pairs to perform PrepareStatement with
	 * @throws Exception
	 * @throws Caller
	 * @return null|object the queried object
	 */
	protected static function _QuerySingle(iCondition $objConditions, $objOptionalClauses = null, $mixParameterArray = null) {
		// Get the Query Statement
		try {
			$strQuery = static::BuildQueryStatement($objQueryBuilder, $objConditions, $objOptionalClauses, $mixParameterArray, false);
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}

		// Perform the Query, Get the First Row, and Instantiate a new object
		$objDbResult = $objQueryBuilder->Database->Query($strQuery);

		// Do we have to expand anything?
		if ($objQueryBuilder->ExpandAsArrayNode) {
			$objToReturn = array();
			$objPrevItemArray = array();
			while ($objDbRow = $objDbResult->GetNextRow()) {
				$objItem = static::InstantiateDbRow($objDbRow, null, $objQueryBuilder->ExpandAsArrayNode, $objPrevItemArray, $objQueryBuilder->ColumnAliasArray);
				if ($objItem) {
					$objToReturn[] = $objItem;
					$pk = $objItem->PrimaryKey();
					if ($pk) {
						$objPrevItemArray[$pk][] = $objItem;
					} else {
						$objPrevItemArray[] = $objItem;
					}
				}
			}
			if (count($objToReturn)) {
				// Since we only want the object to return, lets return the object and not the array.
				return $objToReturn[0];
			} else {
				return null;
			}
		} else {
			// No expands just return the first row
			$objDbRow = $objDbResult->GetNextRow();
			if(null === $objDbRow)
				return null;
			return static::InstantiateDbRow($objDbRow, null, null, null, $objQueryBuilder->ColumnAliasArray);
		}
	}

	/**
	 * Static Qcubed Query method to query for an array of objects.
	 * Uses BuildQueryStatment to perform most of the work.
	 * Is called by QueryArray function of each object so that the correct return type will be put in the comments.
	 *
	 * @param iCondition $objConditions any conditions on the query, itself
	 * @param iClause[]|null $objOptionalClauses additional optional iClause objects for this query
	 * @param mixed[]|null $mixParameterArray an array of name-value pairs to substitute in to the placeholders in the query, if needed
	 * @return mixed[] an array of objects
	 * @throws Caller
	 */
	protected static function _QueryArray(iCondition $objConditions, $objOptionalClauses = null, $mixParameterArray = null) {
		// Get the Query Statement
		try {
			$strQuery = static::BuildQueryStatement($objQueryBuilder, $objConditions, $objOptionalClauses, $mixParameterArray, false);
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}

		// Perform the Query and Instantiate the Array Result
		$objDbResult = $objQueryBuilder->Database->Query($strQuery);
		return static::InstantiateDbResult($objDbResult, $objQueryBuilder->ExpandAsArrayNode, $objQueryBuilder->ColumnAliasArray);
	}

	/**
	 * Static Qcubed query method to issue a query and get a cursor to progressively fetch its results.
	 * Uses BuildQueryStatment to perform most of the work.
	 *
	 * @param iCondition $objConditions any conditions on the query, itself
	 * @param iClause[] $objOptionalClauses additional optional iClause objects for this query
	 * @param mixed[] $mixParameterArray an array of name-value pairs to substitute in to the placeholders in the query, if needed
	 * @return Database\AbstractResult the cursor resource instance
	 * @throws Exception
	 * @throws Caller
	 */
	public static function QueryCursor(iCondition $objConditions, $objOptionalClauses = null, $mixParameterArray = null) {
		// Get the query statement
		try {
			$strQuery = static::BuildQueryStatement($objQueryBuilder, $objConditions, $objOptionalClauses, $mixParameterArray, false);
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}

		// Pull Expansions
		$objExpandAsArrayNode = $objQueryBuilder->ExpandAsArrayNode;
		if (!empty ($objExpandAsArrayNode)) {
			throw new Caller ("Cannot use QueryCursor with ExpandAsArray");
		}

		// Perform the query
		$objDbResult = $objQueryBuilder->Database->Query($strQuery);

		// Get the alias array so we know how to instantiate a row from the result
		$objDbResult->ColumnAliasArray = $objQueryBuilder->ColumnAliasArray;
		return $objDbResult;
	}

	/**
	 * Static Qcubed Query method to query for a count of objects.
	 * Uses BuildQueryStatment to perform most of the work.
	 *
	 * @param iCondition $objConditions any conditions on the query, itself
	 * @param iClause[] $objOptionalClauses additional optional iClause objects for this query
	 * @param mixed[] $mixParameterArray a array of name-value pairs to perform PrepareStatement with
	 * @return integer the count of queried objects as an integer
	 */
	public static function QueryCount(iCondition $objConditions, $objOptionalClauses = null, $mixParameterArray = null) {
		// Get the Query Statement
		try {
			$strQuery = static::BuildQueryStatement($objQueryBuilder, $objConditions, $objOptionalClauses, $mixParameterArray, true);
		} catch (Caller $objExc) {
			$objExc->IncrementOffset();
			throw $objExc;
		}

		// Perform the Query and return the row_count
		$objDbResult = $objQueryBuilder->Database->Query($strQuery);

		// Figure out if the query is using GroupBy
		$blnGrouped = false;

		if ($objOptionalClauses) {
			if ($objOptionalClauses instanceof iClause) {
				if ($objOptionalClauses instanceof Clause\GroupBy) {
					$blnGrouped = true;
				}
			} else if (is_array($objOptionalClauses)) {
				foreach ($objOptionalClauses as $objClause) {
					if ($objClause instanceof Clause\GroupBy) {
						$blnGrouped = true;
						break;
					}
				}
			} else {
				throw new Caller('Optional Clauses must be a iClause object or an array of iClause objects');
			}
		}

		if ($blnGrouped)
			// Groups in this query - return the count of Groups (which is the count of all rows)
			return $objDbResult->CountRows();
		else {
			// No Groups - return the sql-calculated count(*) value
			$strDbRow = $objDbResult->FetchRow();
			return (integer)$strDbRow[0];
		}
	}
/*
	public static function QueryArrayCached(iCondition $objConditions, $objOptionalClauses = null, $mixParameterArray = null, $blnForceUpdate = false) {
		$strQuery = static::BuildQueryStatement($objQueryBuilder, $objConditions, $objOptionalClauses, $mixParameterArray, false);

		$strTableName = static::GetTableName();
		$objCache = new QCache(sprintf('qquery/%s', $strTableName), $strQuery);
		$cacheData = $objCache->GetData();

		if (!$cacheData || $blnForceUpdate) {
			$objDbResult = $objQueryBuilder->Database->Query($strQuery);
			$arrResult = static::InstantiateDbResult($objDbResult, $objQueryBuilder->ExpandAsArrayNode, $objQueryBuilder->ColumnAliasArray);
			$objCache->SaveData(serialize($arrResult));
		} else {
			$arrResult = unserialize($cacheData);
		}

		return $arrResult;
	}*/

	/**
	 * Do a possible array expansion on the given node. If the node is an ExpandAsArray node,
	 * it will add to the corresponding array in the object. Otherwise, it will follow the node
	 * so that any leaf expansions can be handled.
	 *
	 * @param \QCubed\Database\AbstractRow $objDbRow
	 * @param string $strAliasPrefix
	 * @param Node\AbstractBase $objNode
	 * @param array $objPreviousItemArray
	 * @param string[] $strColumnAliasArray
	 * @return boolean|null Returns true if the we used the row for an expansion, false if we already expanded this node in a previous row, or null if no expansion data was found
	 */
	public static function ExpandArray ($objDbRow, $strAliasPrefix, $objNode, $objPreviousItemArray, $strColumnAliasArray) {
		if (!$objNode->ChildNodeArray) {
			return null;
		}
		$blnExpanded = null;

		$pk = static::GetRowPrimaryKey ($objDbRow, $strAliasPrefix, $strColumnAliasArray);

		foreach ($objPreviousItemArray as $objPreviousItem) {
			if ($pk != $objPreviousItem->PrimaryKey()) {
				continue;
			}

			foreach ($objNode->ChildNodeArray as $objChildNode) {
				$strPropName = $objChildNode->_PropertyName;
				$strClassName = $objChildNode->_ClassName;
				$strLongAlias = $objChildNode->FullAlias();
				$blnExpandAsArray = false;

				if ($objChildNode->ExpandAsArray) {
					$strPostfix = 'Array';
					$blnExpandAsArray = true;
				} else {
					$strPostfix = '';
				}
				$nodeType = $objChildNode->_Type;
				if ($nodeType == 'reverse_reference') {
					$strPrefix = '_obj';
				} elseif ($nodeType == 'association') {
					$objChildNode = $objChildNode->FirstChild();
					if ($objChildNode->IsType) {
						$strPrefix = '_int';
					} else {
						$strPrefix = '_obj';
					}
				} else {
					$strPrefix = 'obj';
				}

				$strVarName = $strPrefix . $strPropName . $strPostfix;

				if ($blnExpandAsArray) {
					if (null === $objPreviousItem->$strVarName) {
						$objPreviousItem->$strVarName = array();
					}
					if (count($objPreviousItem->$strVarName)) {
						$objPreviousChildItems = $objPreviousItem->$strVarName;
						$nextAlias = $objChildNode->FullAlias() . '__';

						$objChildItem = $strClassName::InstantiateDbRow ($objDbRow, $nextAlias, $objChildNode, $objPreviousChildItems, $strColumnAliasArray, true);

						if ($objChildItem) {
							$objPreviousItem->{$strVarName}[] = $objChildItem;
							$blnExpanded = true;
						} elseif ($objChildItem === false) {
							$blnExpanded = true;
						}
					}
				} elseif (!$objChildNode->IsType) {

					// Follow single node if keys match
					if (null === $objPreviousItem->$strVarName) {
						return false;
					}
					$objPreviousChildItems = array($objPreviousItem->$strVarName);
					$blnResult = $strClassName::ExpandArray ($objDbRow, $strLongAlias . '__', $objChildNode, $objPreviousChildItems, $strColumnAliasArray);

					if ($blnResult) {
						$blnExpanded = true;
					}
				}
			}
		}
		return $blnExpanded;
	}

	/**
	 * Return an object corresponding to the given key, or null.
	 *
	 * The key might be null if:
	 * 	The table has no primary key, or
	 *  SetSkipPrimaryKey was used in a query with QSelect.
	 *
	 * Otherwise, the default here is to use the local cache.
	 *
	 * Note that you rarely would want to change this. Caching at this level happens
	 * after a query has executed. Using a cache like APC or MemCache at this point would
	 * be really expensive, and would only be worth it for a large table.
	 *
	 * @param $key
	 * @return null|object
	 */
	public static function _GetFromCache($key) {
		return null;
	}

	/**
	 * Put the current object in the cache for future reference.
	 */
	public function WriteToCache() {
	}

	/**
	 * Delete this particular object from the cache
	 * @return void
	 */
	public function DeleteFromCache() {
	}


	/**
	 * Clears the caches associated with this table.
	 */
	public static function ClearCache() {
	}

} 