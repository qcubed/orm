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
 * Used by the QCubed Code Generator to describe a database Type Table
 * "Type" tables must be defined with at least two columns, the first one being an integer-based primary key,
 * and the second one being the name of the type.
 * @package Codegen
 *
 * @property string $Name
 * @property string $ClassName
 * @property string[] $NameArray
 * @property string[] $TokenArray
 * @property array $ExtraPropertyArray
 * @property array[] $ExtraFieldsArray
 * @property-read QSqlColumn[] $PrimaryKeyColumnArray
 * @property-write QSqlColumn $KeyColumn
 * @property QManyToManyReference[] $ManyToManyReferenceArray
 */
class TypeTable extends ObjectBase
{

    /////////////////////////////
    // Protected Member Variables
    /////////////////////////////

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
     * Array of Type Names (as entered into the rows of this database table)
     * This is indexed by integer which represents the ID in the database, starting with 1
     * @var string[] NameArray
     */
    protected $strNameArray;

    /**
     * Column names for extra properties (beyond the 2 basic columns), if any.
     */
    protected $extraFields;

    /**
     * Array of extra properties. This is a double-array - array of arrays. Example:
     *      1 => ['col1' => 'valueA', 'col2 => 'valueB'],
     *      2 => ['col1' => 'valueC', 'col2 => 'valueD'],
     *      3 => ['col1' => 'valueC', 'col2 => 'valueD']
     */
    protected $arrExtraPropertyArray;

    /**
     * Array of Type Names converted into Tokens (can be used as PHP Constants)
     * This is indexed by integer which represents the ID in the database, starting with 1
     * @var string[] TokenArray
     */
    protected $strTokenArray;

    protected $objKeyColumn;
    protected $objManyToManyReferenceArray;

    /////////////////////
    // Public Constructor
    /////////////////////

    /**
     * TypeTable constructor.
     * @param string $strName
     */
    public function __construct($strName)
    {
        $this->strName = $strName;
    }

    /**
     * Returns the string that will be used to represent the literal value given when codegenning a type table
     * @param mixed $mixColValue
     * @return string
     */
    public static function literal($mixColValue)
    {
        if (is_null($mixColValue)) {
            return 'null';
        } elseif (is_integer($mixColValue)) {
            return $mixColValue;
        } elseif (is_bool($mixColValue)) {
            return ($mixColValue ? 'true' : 'false');
        } elseif (is_float($mixColValue)) {
            return "(float)$mixColValue";
        } elseif (is_object($mixColValue)) {
            return "t('" . $mixColValue->_toString() . "')";
        }    // whatever is suitable for the constructor of the object
        else {
            return "t('" . str_replace("'", "\\'", $mixColValue) . "')";
        }
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
    public function __get($strName)
    {
        switch ($strName) {
            case 'Name':
                return $this->strName;
            case 'ClassName':
                return $this->strClassName;
            case 'NameArray':
                return $this->strNameArray;
            case 'TokenArray':
                return $this->strTokenArray;
            case 'ExtraPropertyArray':
                return $this->arrExtraPropertyArray;
            case 'ExtraFieldsArray':
                return $this->extraFields;
            case 'PrimaryKeyColumnArray':
                $a[] = $this->objKeyColumn;
                return $a;
            case 'ManyToManyReferenceArray':
                return (array)$this->objManyToManyReferenceArray;

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
    public function __set($strName, $mixValue)
    {
        try {
            switch ($strName) {
                case 'Name':
                    return $this->strName = Type::cast($mixValue, Type::STRING);
                case 'ClassName':
                    return $this->strClassName = Type::cast($mixValue, Type::STRING);
                case 'NameArray':
                    return $this->strNameArray = Type::cast($mixValue, Type::ARRAY_TYPE);
                case 'TokenArray':
                    return $this->strTokenArray = Type::cast($mixValue, Type::ARRAY_TYPE);
                case 'ExtraPropertyArray':
                    return $this->arrExtraPropertyArray = Type::cast($mixValue, Type::ARRAY_TYPE);
                case 'ExtraFieldsArray':
                    return $this->extraFields = Type::cast($mixValue, Type::ARRAY_TYPE);
                case 'KeyColumn':
                    return $this->objKeyColumn = $mixValue;
                case 'ManyToManyReferenceArray':
                    return $this->objManyToManyReferenceArray = Type::cast($mixValue, Type::ARRAY_TYPE);
                default:
                    return parent::__set($strName, $mixValue);
            }
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }
}