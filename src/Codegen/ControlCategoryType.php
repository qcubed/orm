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
abstract class ControlCategoryType
{
    /** Large binary object or large text data */
    const BLOB = Database\FieldType::BLOB;
    /** Character sequence - variable length */
    const TEXT = Database\FieldType::VAR_CHAR;
    /** Character sequence - fixed length */
    const CHAR = Database\FieldType::CHAR;
    /** Integers */
    const INTEGER = Database\FieldType::INTEGER;
    /** Date and Time together */
    const DATE_TIME = Database\FieldType::DATE_TIME;
    /** Date only */
    const DATE = Database\FieldType::DATE;
    /** Time only */
    const TIME = Database\FieldType::TIME;
    /** Float, Double and real (postgresql) */
    const FLOAT = Database\FieldType::FLOAT;
    /** Boolean */
    const BOOLEAN = Database\FieldType::BIT;
    /** Select one item from a list of items. A foreign key or a unique reverse relationship. */
    const SINGLE_SELECT = 'single';
    /** Select multiple items from a list of items. A non-unique reverse relationship or association table. */
    const MULTI_SELECT = 'multi';
    /** Display a representation of an entire database table. Click actions would typically be done on this list. */
    const TABLE = 'table';
}
