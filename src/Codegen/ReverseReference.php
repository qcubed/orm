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
 * Used by the QCubed Code Generator to describe a column reference from
 * the table's perspective (aka a Foreign Key from the referenced Table's point of view)
 * @package Codegen
 *
 * @property Reference $Reference
 * @property string $KeyName
 * @property string $Table
 * @property string $Column
 * @property boolean $NotNull
 * @property boolean $Unique
 * @property string $VariableName
 * @property string $VariableType
 * @property string $PropertyName
 * @property string $ObjectDescription
 * @property string $ObjectDescriptionPlural
 * @property string $ObjectMemberVariable
 * @property string $ObjectPropertyName
 * @property array $Options
 */
class ReverseReference extends ObjectBase implements ColumnInterface
{

    /////////////////////////////
    // Protected Member Variables
    /////////////////////////////

    /**
     * The peer QReference object for which this object is the reverse reference of
     * @var Reference KeyName
     */
    protected $objReference;

    /**
     * Name of the foreign key object itself, as defined in the database or create script
     * @var string KeyName
     */
    protected $strKeyName;

    /**
     * Name of the referencing table (the table that owns the column that is the foreign key)
     * @var string Table
     */
    protected $strTable;

    /**
     * Name of the referencing column (the column that owns the foreign key)
     * @var string Column
     */
    protected $strColumn;

    /**
     * Specifies whether the referencing column is specified as "NOT NULL"
     * @var bool NotNull
     */
    protected $blnNotNull;

    /**
     * Specifies whether the referencing column is unique
     * @var bool Unique
     */
    protected $blnUnique;

    /**
     * Name of the reverse-referenced object as an function parameter.
     * So if this is a reverse reference to "person" via "report.person_id",
     * the VariableName would be "objReport"
     * @var string VariableName
     */
    protected $strVariableName;

    /**
     * Type of the reverse-referenced object as a class.
     * So if this is a reverse reference to "person" via "report.person_id",
     * the VariableName would be "Report"
     * @var string VariableType
     */
    protected $strVariableType;

    /**
     * Property Name of the referencing column (the column that owns the foreign key)
     * in the associated Class.  So if this is a reverse reference to the "person" table
     * via the table/column "report.owner_person_id", the PropertyName would be "OwnerPersonId"
     * @var string PropertyName
     */
    protected $strPropertyName;

    /**
     * Singular object description used in the function names for the
     * reverse reference.  See documentation for more details.
     * @var string ObjectDescription
     */
    protected $strObjectDescription;

    /**
     * Plural object description used in the function names for the
     * reverse reference.  See documentation for more details.
     * @var string ObjectDescriptionPlural
     */
    protected $strObjectDescriptionPlural;

    /**
     * A member variable name to be used by classes that contain the local member variable
     * for this unique reverse reference.  Only aggregated when blnUnique is true.
     * @var string ObjectMemberVariable
     */
    protected $strObjectMemberVariable;

    /**
     * A property name to be used by classes that contain the property
     * for this unique reverse reference.  Only aggregated when blnUnique is true.
     * @var string ObjectPropertyName
     */
    protected $strObjectPropertyName;

    /**
     * Keyed array of overrides read from the override file
     * @var array Overrides
     */
    protected $options;


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
            case 'Reference':
                return $this->objReference;
            case 'KeyName':
                return $this->strKeyName;
            case 'Table':
                return $this->strTable;
            case 'Column':
                return $this->strColumn;
            case 'NotNull':
                return $this->blnNotNull;
            case 'Unique':
                return $this->blnUnique;
            case 'VariableName':
                return $this->strVariableName;
            case 'VariableType':
                return $this->strVariableType;
            case 'PropertyName':
                return $this->strPropertyName;
            case 'ObjectDescription':
                return $this->strObjectDescription;
            case 'ObjectDescriptionPlural':
                return $this->strObjectDescriptionPlural;
            case 'ObjectMemberVariable':
                return $this->strObjectMemberVariable;
            case 'ObjectPropertyName':
                return $this->strObjectPropertyName;
            case 'Options':
                return $this->options;

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
                case 'Reference':
                    return $this->objReference = Type::cast($mixValue, Reference::class);
                case 'KeyName':
                    return $this->strKeyName = Type::cast($mixValue, Type::STRING);
                case 'Table':
                    return $this->strTable = Type::cast($mixValue, Type::STRING);
                case 'Column':
                    return $this->strColumn = Type::cast($mixValue, Type::STRING);
                case 'NotNull':
                    return $this->blnNotNull = Type::cast($mixValue, Type::BOOLEAN);
                case 'Unique':
                    return $this->blnUnique = Type::cast($mixValue, Type::BOOLEAN);
                case 'VariableName':
                    return $this->strVariableName = Type::cast($mixValue, Type::STRING);
                case 'VariableType':
                    return $this->strVariableType = Type::cast($mixValue, Type::STRING);
                case 'PropertyName':
                    return $this->strPropertyName = Type::cast($mixValue, Type::STRING);
                case 'ObjectDescription':
                    return $this->strObjectDescription = Type::cast($mixValue, Type::STRING);
                case 'ObjectDescriptionPlural':
                    return $this->strObjectDescriptionPlural = Type::cast($mixValue, Type::STRING);
                case 'ObjectMemberVariable':
                    return $this->strObjectMemberVariable = Type::cast($mixValue, Type::STRING);
                case 'ObjectPropertyName':
                    return $this->strObjectPropertyName = Type::cast($mixValue, Type::STRING);
                case 'Options':
                    return $this->options = Type::cast($mixValue, Type::ARRAY_TYPE);
                default:
                    return parent::__set($strName, $mixValue);
            }
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }
}