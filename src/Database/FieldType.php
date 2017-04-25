<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database;

/**
 * Data types in a database
 * @package DatabaseAdapters
 */

abstract class FieldType {
	/** Binary Data */
	const Blob = "Blob";
	/** Character sequence - variable length */
	const VarChar = "VarChar";
	/** Character sequence - fixed length */
	const Char = "Char";
	/** Integers */
	const Integer = "Integer";
	/** Date and Time together */
	const DateTime = "DateTime";
	/** Date only */
	const Date = "Date";
	/** Time only */
	const Time = "Time";
	/** Float, Double and real (postgresql) */
	const Float = "Float";
	/** Boolean */
	const Bit = "Bit";
	/** New JSON type */
	const Json = "Json";
}