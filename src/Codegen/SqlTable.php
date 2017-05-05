<?php
/**
*
* Part of the QCubed PHP framework.
*
* @license MIT
*
*/

namespace QCubed\Codegen;

use QCubed\Exception\Caller;
use QCubed\ObjectBase;
use QCubed\Type;

/**
 * A helper class used by the QCubed Code Generator to describe a database Table
 * @package Codegen
 *
 * @property int $OwnerDbIndex
 * @property string $Name
 * @property string $ClassNamePlural
 * @property string $ClassName
 * @property SqlColumn[] $ColumnArray
 * @property SqlColumn[] $PrimaryKeyColumnArray
 * @property ReverseReference[] $ReverseReferenceArray
 * @property ManyToManyReference[] $ManyToManyReferenceArray
 * @property Index[] $IndexArray
 * @property-read int $ReferenceCount
 * @property array $Options
 */
class SqlTable extends ObjectBase {

	/////////////////////////////
	// Protected Member Variables
	/////////////////////////////

	/**
	 * @var int DB Index to which it belongs in the configuration.inc.php and codegen_settings.xml files.
	 */
	protected $intOwnerDbIndex;

	/**
	 * Name of the table (as defined in the database)
	 * @var string Name
	 */
	protected $strName;

	/**
	 * Name as a PHP Class
	 * @var string ClassName
	 */
	protected $strClassName;

	/**
	 * Pluralized Name as a collection of objects of this PHP Class
	 * @var string ClassNamePlural;
	 */
	protected $strClassNamePlural;

	/**
	 * Array of Column objects (as indexed by Column name)
	 * @var SqlColumn[] ColumnArray
	 */
	protected $objColumnArray;

	/**
	 * Array of ReverseReverence objects (indexed numerically)
	 * @var ReverseReference[] ReverseReferenceArray
	 */
	protected $objReverseReferenceArray;

	/**
	 * Array of ManyToManyReference objects (indexed numerically)
	 * @var ManyToManyReference[] ManyToManyReferenceArray
	 */
	protected $objManyToManyReferenceArray;

	/**
	 * Array of Index objects (indexed numerically)
	 * @var Index[] IndexArray
	 */
	protected $objIndexArray;

	/**
	 * @var array developer specified options.
	 */
	protected $options;



	/////////////////////
	// Public Constructor
	/////////////////////

	/**
	 * Default Constructor.  Simply sets up the TableName and ensures that ReverseReferenceArray is a blank array.
	 *
	 * @param string $strName Name of the Table
	 */
	public function __construct($strName) {
		$this->strName = $strName;
		$this->objReverseReferenceArray = array();
		$this->objManyToManyReferenceArray = array();
		$this->objColumnArray = array();
		$this->objIndexArray = array();
	}


	/**
	 * return the SqlColumn object related to that column name
	 * @param string $strColumnName Name of the column
	 * @return SqlColumn
	 */
	public function getColumnByName($strColumnName) {
		if ($this->objColumnArray) {
			foreach ($this->objColumnArray as $objColumn){
				if ($objColumn->Name == $strColumnName)
					return $objColumn;
			}
		}
		return null;
	}

	/**
	 * Search within the table's columns for the given column
	 * @param string $strColumnName Name of the column
	 * @return boolean
	 */
	public function hasColumn($strColumnName){
		return ($this->getColumnByName($strColumnName) !== null);
	}

	/**
	 * Return the property name for a given column name (false if it doesn't exists)
	 * @param string $strColumnName name of the column
	 * @return string
	 */
	public function lookupColumnPropertyName($strColumnName){
		$objColumn = $this->getColumnByName($strColumnName);
		if ($objColumn)
			return $objColumn->PropertyName;
		else
			return null;
	}

	public function hasImmediateArrayExpansions() {
		$intCount = count($this->objManyToManyReferenceArray);
		foreach ($this->objReverseReferenceArray as $objReverseReference) {
			if (!$objReverseReference->Unique) {
				$intCount++;
			}
		}
		return $intCount > 0;
	}

	public function hasExtendedArrayExpansions(DatabaseCodeGen $objCodeGen, $objCheckedTableArray = array()) {
		$objCheckedTableArray[] = $this;
		foreach ($this->ColumnArray as $objColumn) {
			if (($objReference = $objColumn->Reference) && !$objReference->IsType) {
				if ($objTable2 = $objCodeGen->getTable($objReference->Table)) {
					if ($objTable2->hasImmediateArrayExpansions()) {
						return true;
					}
					if (!in_array($objTable2, $objCheckedTableArray) &&	// watch out for circular references
							$objTable2->hasExtendedArrayExpansions($objCodeGen, $objCheckedTableArray)) {
						return true;
					}
				}
			}
		}
		return false;
	}




	////////////////////
	// Public Overriders
	////////////////////

	/**
	 * Override method to perform a property "Get"
	 * This will get the value of $strName
	 *
	 * @param string $strName Name of the property to get
	 * @throws Caller
	 * @return mixed
	 */
	public function __get($strName) {
		switch ($strName) {
			case 'OwnerDbIndex':
				return $this->intOwnerDbIndex;
			case 'Name':
				return $this->strName;
			case 'ClassNamePlural':
				return $this->strClassNamePlural;
			case 'ClassName':
				return $this->strClassName;
			case 'ColumnArray':
				return (array) $this->objColumnArray;
			case 'PrimaryKeyColumnArray':
				if ($this->objColumnArray) {
					$objToReturn = array();
					foreach ($this->objColumnArray as $objColumn)
						if ($objColumn->PrimaryKey)
							array_push($objToReturn, $objColumn);
					return $objToReturn;
				} else
					return null;
			case 'ReverseReferenceArray':
				return (array) $this->objReverseReferenceArray;
			case 'ManyToManyReferenceArray':
				return (array) $this->objManyToManyReferenceArray;
			case 'IndexArray':
				return (array) $this->objIndexArray;
			case 'ReferenceCount':
				$intCount = count($this->objManyToManyReferenceArray);
				foreach ($this->objColumnArray as $objColumn)
					if ($objColumn->Reference)
						$intCount++;
				return $intCount;

			case 'Options':
				return $this->options;

			default:
				try {
					return parent::__get($strName);
				} catch (Caller $objExc) {
					$objExc->incrementOffset();
					throw $objExc;
				}
		}
	}

	/**
	 * Override method to perform a property "Set"
	 * This will set the property $strName to be $mixValue
	 *
	 * @param string $strName Name of the property to set
	 * @param string $mixValue New value of the property
	 * @throws Caller
	 * @return mixed
	 */
	public function __set($strName, $mixValue) {
		try {
			switch ($strName) {
				case 'OwnerDbIndex':
					return $this->intOwnerDbIndex = Type::cast($mixValue, Type::INTEGER);
				case 'Name':
					return $this->strName = Type::cast($mixValue, Type::STRING);
				case 'ClassName':
					return $this->strClassName = Type::cast($mixValue, Type::STRING);
				case 'ClassNamePlural':
					return $this->strClassNamePlural = Type::cast($mixValue, Type::STRING);
				case 'ColumnArray':
					return $this->objColumnArray = Type::cast($mixValue, Type::ARRAY_TYPE);
				case 'ReverseReferenceArray':
					return $this->objReverseReferenceArray = Type::cast($mixValue, Type::ARRAY_TYPE);
				case 'ManyToManyReferenceArray':
					return $this->objManyToManyReferenceArray = Type::cast($mixValue, Type::ARRAY_TYPE);
				case 'IndexArray':
					return $this->objIndexArray = Type::cast($mixValue, Type::ARRAY_TYPE);
				case 'Options':
					return $this->options = Type::cast($mixValue, Type::ARRAY_TYPE);
				default:
					return parent::__set($strName, $mixValue);
			}
		} catch (Caller $objExc) {
			$objExc->incrementOffset();
			throw $objExc;
		}
	}
}
