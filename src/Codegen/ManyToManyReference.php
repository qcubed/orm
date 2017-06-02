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
 * @property string $KeyName
 * @property string $Table
 * @property string $Column
 * @property string $PropertyName
 * @property string $OppositeColumn
 * @property string $OppositeVariableType
 * @property string $OppositeDbType
 * @property string $OppositeVariableName
 * @property string $OppositePropertyName
 * @property string $OppositeObjectDescription
 * @property string $AssociatedTable
 * @property string $VariableName
 * @property string $VariableType
 * @property string $ObjectDescription
 * @property string $ObjectDescriptionPlural
 * @property SqlColumn[] $ColumnArray
 * @property boolean $IsTypeAssociation
 * @property array $Options
 */
class ManyToManyReference extends ObjectBase implements ColumnInterface
{

    /////////////////////////////
    // Protected Member Variables
    /////////////////////////////

    /**
     * Name of the foreign key object itself, as defined in the database or create script
     * @var string KeyName
     */
    protected $strKeyName;

    /**
     * Name of the association table, itself (the many-to-many table that maps
     * the relationshipfor this ManyToManyReference)
     * @var string Table
     */
    protected $strTable;

    /**
     * Name of the referencing column (the column that owns the foreign key to this table)
     * @var string Column
     */
    protected $strColumn;

    /**
     * Name of property corresponding to this column as used in the node.
     * @var string PropertyName
     */
    protected $strPropertyName;

    /**
     * Name of the opposite column (the column that owns the foreign key to the related table)
     * @var string OppositeColumn
     */
    protected $strOppositeColumn;

    /**
     * Type of the opposite column (the column that owns the foreign key to the related table)
     * as a Variable type (for example, to be used to define the input parameter type to a Load function)
     * @var string OppositeVariableType
     */
    protected $strOppositeVariableType;

    /**
     * Database type of the opposite column (the column that owns the foreign key to the related table)
     * as a  DbType (for example, to be used to define the input parameter type to a Node)
     * @var string OppositeDbType
     */
    protected $strOppositeDbType;


    /**
     * Name of the opposite column (the column that owns the foreign key to the related table)
     * as a Variable name (for example, to be used as an input parameter to a Load function)
     * @var string OppositeVariableName
     */
    protected $strOppositeVariableName;

    /**
     * Name of the opposite column (the column that owns the foreign key to the related table)
     * as a Property name (for example, to be used as a QQAssociationNode parameter name for the
     * column itself)
     * @var string OppositePropertyName
     */
    protected $strOppositePropertyName;

    /**
     * Name of the opposite column (the column that owns the foreign key to the related table)
     * as an Object Description (see "ObjectDescription" below)
     * @var string OppositeObjectDescription
     */
    protected $strOppositeObjectDescription;

    /**
     * The name of the associated table (the table that the OTHER
     * column in the association table points to)
     * @var string AssociatedTable
     */
    protected $strAssociatedTable;

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
     * Singular object description used in the function names for the
     * reverse reference.  See documentation for more details.
     * @var string ObjectDescription
     */
    protected $strObjectDescription;

    /**
     * Plural object description used in the function names for the
     * reverse reference.  See documentation for more details.
     * @var string VariableType
     */
    protected $strObjectDescriptionPlural;

    /**
     * Array of non-FK Column objects (as indexed by Column name)
     * @var SqlColumn[] ColumnArray
     */
    protected $objColumnArray;
    /**
     * Array of non-FK Column objects (as indexed by Column name)
     * @var boolean IsTypeAssociation
     */
    protected $blnIsTypeAssociation;

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
            case 'KeyName':
                return $this->strKeyName;
            case 'Table':
                return $this->strTable;
            case 'Column':
                return $this->strColumn;
            case 'PropertyName':
                return $this->strPropertyName;
            case 'OppositeColumn':
                return $this->strOppositeColumn;
            case 'OppositeVariableType':
                return $this->strOppositeVariableType;
            case 'OppositeDbType':
                return $this->strOppositeDbType;
            case 'OppositeVariableName':
                return $this->strOppositeVariableName;
            case 'OppositePropertyName':
                return $this->strOppositePropertyName;
            case 'OppositeObjectDescription':
                return $this->strOppositeObjectDescription;
            case 'AssociatedTable':
                return $this->strAssociatedTable;
            case 'VariableName':
                return $this->strVariableName;
            case 'VariableType':
                return $this->strVariableType;
            case 'ObjectDescription':
                return $this->strObjectDescription;
            case 'ObjectDescriptionPlural':
                return $this->strObjectDescriptionPlural;
            case 'ColumnArray':
                return $this->objColumnArray;
            case 'IsTypeAssociation':
                return $this->blnIsTypeAssociation;
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
     * @return void
     */
    public function __set($strName, $mixValue)
    {
        try {
            switch ($strName) {
                case 'KeyName':
                    $this->strKeyName = Type::cast($mixValue, Type::STRING);
                    break;
                case 'Table':
                    $this->strTable = Type::cast($mixValue, Type::STRING);
                    break;
                case 'Column':
                    $this->strColumn = Type::cast($mixValue, Type::STRING);
                    break;
                case 'PropertyName':
                    $this->strPropertyName = Type::cast($mixValue, Type::STRING);
                    break;
                case 'OppositeColumn':
                    $this->strOppositeColumn = Type::cast($mixValue, Type::STRING);
                    break;
                case 'OppositeVariableType':
                    $this->strOppositeVariableType = Type::cast($mixValue, Type::STRING);
                    break;
                case 'OppositeDbType':
                    $this->strOppositeDbType = Type::cast($mixValue, Type::STRING);
                    break;
                case 'OppositeVariableName':
                    $this->strOppositeVariableName = Type::cast($mixValue, Type::STRING);
                    break;
                case 'OppositePropertyName':
                    $this->strOppositePropertyName = Type::cast($mixValue, Type::STRING);
                    break;
                case 'OppositeObjectDescription':
                    $this->strOppositeObjectDescription = Type::cast($mixValue, Type::STRING);
                    break;
                case 'AssociatedTable':
                    $this->strAssociatedTable = Type::cast($mixValue, Type::STRING);
                    break;
                case 'VariableName':
                    $this->strVariableName = Type::cast($mixValue, Type::STRING);
                    break;
                case 'VariableType':
                    $this->strVariableType = Type::cast($mixValue, Type::STRING);
                    break;
                case 'ObjectDescription':
                    $this->strObjectDescription = Type::cast($mixValue, Type::STRING);
                    break;
                case 'ObjectDescriptionPlural':
                    $this->strObjectDescriptionPlural = Type::cast($mixValue, Type::STRING);
                    break;
                case 'ColumnArray':
                    $this->objColumnArray = Type::cast($mixValue, Type::ARRAY_TYPE);
                    break;
                case 'IsTypeAssociation':
                    $this->blnIsTypeAssociation = Type::cast($mixValue, Type::BOOLEAN);
                    break;
                case 'Options':
                    $this->options = Type::cast($mixValue, Type::ARRAY_TYPE);
                    break;
                default:
                    parent::__set($strName, $mixValue);
                    break;
            }
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }
}