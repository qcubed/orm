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
 * Class FieldBase
 *
 * @property-read string $Name
 * @property-read string $OriginalName
 * @property-read string $Table
 * @property-read string $OriginalTable
 * @property-read string $Default
 * @property-read integer $MaxLength
 * @property-read boolean $Identity
 * @property-read boolean $NotNull
 * @property-read boolean $PrimaryKey
 * @property-read boolean $Unique
 * @property-read boolean $Timestamp
 * @property-read string $Type
 * @property-read string $Comment
 * @package QCubed\Database
 * @was QDatabaseFieldBase
 */
abstract class FieldBase extends ObjectBase
{
    protected $strName;
    protected $strOriginalName;
    protected $strTable;
    protected $strOriginalTable;
    protected $strDefault;
    protected $intMaxLength;
    protected $strComment;

    // Bool
    protected $blnIdentity;
    protected $blnNotNull;
    protected $blnPrimaryKey;
    protected $blnUnique;
    protected $blnTimestamp;

    protected $strType;

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
            case "Name":
                return $this->strName;
            case "OriginalName":
                return $this->strOriginalName;
            case "Table":
                return $this->strTable;
            case "OriginalTable":
                return $this->strOriginalTable;
            case "Default":
                return $this->strDefault;
            case "MaxLength":
                return $this->intMaxLength;
            case "Identity":
                return $this->blnIdentity;
            case "NotNull":
                return $this->blnNotNull;
            case "PrimaryKey":
                return $this->blnPrimaryKey;
            case "Unique":
                return $this->blnUnique;
            case "Timestamp":
                return $this->blnTimestamp;
            case "Type":
                return $this->strType;
            case "Comment":
                return $this->strComment;
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

