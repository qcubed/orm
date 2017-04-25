<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */
namespace QCubed\Database;

use QCubed\Exception\Caller;
use QCubed\Exception\InvalidCast;
use QCubed\Type;

/**
 */

/**
 * Class AbstractResult
 *
 * Class to handle results sent by database upon querying
 *
 * @property string[] ColumnAliasArray;
 * @package QCubed\Database
 * @was QDatabaseResultBase
 */
abstract class AbstractResult extends \QCubed\AbstractBase {
	/** @var array The column alias array. This is needed for instantiating cursors. */
	protected $strColumnAliasArray;

	/**
	 * Fetches one row as indexed (column=>value style) array from the result set
	 * @abstract
	 * @return mixed
	 */
	abstract public function FetchArray();

	/**
	 * Fetches one row as enumerated (with numerical indexes) array from the result set
	 * @abstract
	 * @return mixed
	 */
	abstract public function FetchRow();

	abstract public function FetchField();
	abstract public function FetchFields();
	abstract public function CountRows();
	abstract public function CountFields();

	/**
	 * @return AbstractRow
	 */
	abstract public function GetNextRow();
	abstract public function GetRows();

	abstract public function Close();

	/**
	 * PHP magic method
	 *
	 * @param string $strName Property name
	 *
	 * @return mixed
	 * @throws \Exception|Caller
	 */
	public function __get($strName) {
		switch ($strName) {
			case 'ColumnAliasArray':
				return $this->strColumnAliasArray;
			default:
				try {
					return parent::__get($strName);
				} catch (Caller $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
		}
	}

	public function __set($strName, $mixValue) {
		switch ($strName) {
			case 'ColumnAliasArray':
				try {
					return ($this->strColumnAliasArray = Type::Cast($mixValue, Type::ArrayType));
				} catch (InvalidCast $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
			default:
				try {
					return parent::__set($strName, $mixValue);
				} catch (Caller $objExc) {
					$objExc->IncrementOffset();
					throw $objExc;
				}
		}
	}
}

