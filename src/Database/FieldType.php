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

abstract class FieldType
{
    /** Binary Data */
    const BLOB = "Blob";
    /** Character sequence - variable length */
    const VAR_CHAR = "VarChar";
    /** Character sequence - fixed length */
    const CHAR = "Char";
    /** Integers */
    const INTEGER = "Integer";
    /** Date and Time together */
    const DATE_TIME = "DateTime";
    /** Date only */
    const DATE = "Date";
    /** Time only */
    const TIME = "Time";
    /** Float, Double and real (postgresql) */
    const FLOAT = "Float";
    /** Boolean */
    const BIT = "Bit";
    /** New JSON type */
    const JSON = "Json";
}