<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Mysqli5;

use QCubed\Database\FieldType;

/**
 * Class Field
 * @package QCubed\Database\Mysqli5
 * @was QMySqli5DatabaseField
 */
class Field extends MysqliField {
	protected function setFieldType($intMySqlFieldType, $intFlags) {
		switch ($intMySqlFieldType) {
			case MYSQLI_TYPE_NEWDECIMAL:
				$this->strType = FieldType::VarChar;
				break;

			case MYSQLI_TYPE_BIT:
				$this->strType = FieldType::Bit;
				break;

			default:
				parent::setFieldType($intMySqlFieldType, $intFlags);
		}
	}
}