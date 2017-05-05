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
use QCubed\ObjectBase;

/**
 * To handle index in a table in database
 * @package DatabaseAdapters
 * @was QDatabaseIndex
 */
class Index extends ObjectBase
{
    /** @var string Name of the index */
    protected $strKeyName;
    /** @var bool Is the Index a primary key index? */
    protected $blnPrimaryKey;
    /** @var bool Is this a Unique index? */
    protected $blnUnique;
    /** @var array Array of column names on which this index is defined */
    protected $strColumnNameArray;

    /**
     * @param string $strKeyName Name of the index
     * @param string $blnPrimaryKey Is this index a Primary key index?
     * @param string $blnUnique Is this index unique?
     * @param array $strColumnNameArray Columns on which this index is defined
     */
    public function __construct($strKeyName, $blnPrimaryKey, $blnUnique, $strColumnNameArray)
    {
        $this->strKeyName = $strKeyName;
        $this->blnPrimaryKey = $blnPrimaryKey;
        $this->blnUnique = $blnUnique;
        $this->strColumnNameArray = $strColumnNameArray;
    }

    /**
     * PHP magic function
     * @param string $strName
     *
     * @return mixed
     * @throws \Exception|Caller
     */
    public function __get($strName)
    {
        switch ($strName) {
            case "KeyName":
                return $this->strKeyName;
            case "PrimaryKey":
                return $this->blnPrimaryKey;
            case "Unique":
                return $this->blnUnique;
            case "ColumnNameArray":
                return $this->strColumnNameArray;
            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }
}

