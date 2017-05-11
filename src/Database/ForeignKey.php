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
 *
 */

/**
 * Class ForeignKey
 *
 * @property-read string $KeyName
 * @property-read string[] $ColumnNameArray
 * @property-read string $ReferenceTableName
 * @property-read string[] $ReferenceColumnNameArray
 *
 * @was QDatabaseForeignKey
 * @package QCubed\Database
 */
class ForeignKey extends ObjectBase
{
    protected $strKeyName;
    protected $strColumnNameArray;
    protected $strReferenceTableName;
    protected $strReferenceColumnNameArray;

    public function __construct($strKeyName, $strColumnNameArray, $strReferenceTableName, $strReferenceColumnNameArray)
    {
        $this->strKeyName = $strKeyName;
        $this->strColumnNameArray = $strColumnNameArray;
        $this->strReferenceTableName = $strReferenceTableName;
        $this->strReferenceColumnNameArray = $strReferenceColumnNameArray;
    }

    /**
     * PHP magic method
     *
     * @param string $strName Property name
     *
     * @return mixed
     * @throws \Exception|Caller
     */
    public function __get($strName)
    {
        switch ($strName) {
            case "KeyName":
                return $this->strKeyName;
            case "ColumnNameArray":
                return $this->strColumnNameArray;
            case "ReferenceTableName":
                return $this->strReferenceTableName;
            case "ReferenceColumnNameArray":
                return $this->strReferenceColumnNameArray;
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

