<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Codegen;

use \QCubed\Database;

/**
 * Control categories, used by the ModelConnectorEditDlg to pair controls with database types or relationships *
 */

abstract class ControlCategoryType {
	/** Large binary object or large text data */
	const Blob = Database\FieldType::Blob;
	/** Character sequence - variable length */
	const Text = Database\FieldType::VarChar;
	/** Character sequence - fixed length */
	const Char = Database\FieldType::Char;
	/** Integers */
	const Integer = Database\FieldType::Integer;
	/** Date and Time together */
	const DateTime = Database\FieldType::DateTime;
	/** Date only */
	const Date = Database\FieldType::Date;
	/** Time only */
	const Time = Database\FieldType::Time;
	/** Float, Double and real (postgresql) */
	const Float = Database\FieldType::Float;
	/** Boolean */
	const Boolean = Database\FieldType::Bit;
	/** Select one item from a list of items. A foreign key or a unique reverse relationship. */
	const SingleSelect = 'single';
	/** Select multiple items from a list of items. A non-unique reverse relationship or association table. */
	const MultiSelect = 'multi';
	/** Display a representation of an entire database table. Click actions would typically be done on this list. */
	const Table = 'table';
}
