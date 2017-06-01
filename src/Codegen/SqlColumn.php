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
 * A helper class used by the QCubed Code Generator to describe a table's column
 *
 * @package Codegen
 * @property SqlTable|TypeTable $OwnerTable             Table in which this column exists
 * @property boolean $PrimaryKey             Is the column a (part of) primary key
 * @property string $Name                   Column name
 * @property string $PropertyName           Corresponding property name for the table
 * @property string $VariableName           Corresponding variable name (in ORM class and elsewhere)
 * @property string $VariableType           Type of data this column is supposed to store (constant from Type class)
 * @property string $VariableTypeAsConstant Variable type expressed as Type casted string (integer column would have this value as: "\QCubed\Type::INTEGER")
 * @property string $DbType                 Type in the database
 * @property int $Length                 If applicable, the length of data to be stored (useful for varchar data types)
 * @property mixed $Default                Default value of the column
 * @property boolean $NotNull                Is this column a "NOT NULL" column?
 * @property boolean $Identity               Is this column an Identity column?
 * @property boolean $Indexed                Is there a single column index on this column?
 * @property boolean $Unique                 Does this column have a 'Unique' key defined on it?
 * @property boolean $Timestamp              Can this column contain a timestamp value?
 * @property Reference $Reference              Reference to another column (if this one is a foreign key)
 * @property array $Options                Options for codegen
 * @property string $Comment                Comment on the column
 * @property boolean $AutoUpdate             Whether column that is a Timestamp should generate code to automatically update the timestamp
 */
class SqlColumn extends ObjectBase implements ColumnInterface
{

    /////////////////////////////
    // Protected Member Variables
    /////////////////////////////

    /**
     * @var SqlTable The table in which this column exists.
     */
    protected $objOwnerTable;

    /**
     * Specifies whether or not the column is a Primary Key
     * @var bool PrimaryKey
     */
    protected $blnPrimaryKey;

    /**
     * Name of the column as defined in the database
     * So for example, "first_name"
     * @var string Name
     */
    protected $strName;

    /**
     * Name of the column as an object Property
     * So for "first_name", it would be FirstName
     * @var string PropertyName
     */
    protected $strPropertyName;

    /**
     * Name of the column as an object protected Member Variable
     * So for "first_name VARCHAR(50)", it would be strFirstName
     * @var string VariableName
     */
    protected $strVariableName;

    /**
     * The type of the protected member variable (uses one of the string constants from the Type class)
     * @var string VariableType
     */
    protected $strVariableType;

    /**
     * The type of the protected member variable (uses the actual constant from the Type class)
     * @var string VariableType
     */
    protected $strVariableTypeAsConstant;

    /**
     * The actual type of the column in the database (uses one of the string constants from the DatabaseType class)
     * @var string DbType
     */
    protected $strDbType;

    /**
     * Length of the column as defined in the database
     * @var int Length
     */
    protected $intLength;

    /**
     * The default value for the column as defined in the database
     * @var mixed Default
     */
    protected $mixDefault;

    /**
     * Specifies whether or not the column is specified as "NOT NULL"
     * @var bool NotNull
     */
    protected $blnNotNull;

    /**
     * Specifies whether or not the column is an identiy column (like auto_increment)
     * @var bool Identity
     */
    protected $blnIdentity;

    /**
     * Specifies whether or not the column is a single-column Index
     * @var bool Indexed
     */
    protected $blnIndexed;

    /**
     * Specifies whether or not the column is a unique
     * @var bool Unique
     */
    protected $blnUnique;

    /**
     * Specifies whether or not the column is a system-updated "timestamp" column
     * @var bool Timestamp
     */
    protected $blnTimestamp;

    /**
     * If the table column is foreign keyed off another column, then this
     * Column instance would be a reference to another object
     * @var Reference Reference
     */
    protected $objReference;

    /**
     * The string value of the comment field in the database.
     * @var string Comment
     */
    protected $strComment;

    /**
     * Various overrides and options embedded in the comment for the column as a json object.
     * @var array Overrides
     */
    protected $options = array();

    /**
     * For Timestamp columns, will add to the sql code to set this field to NOW whenever there is a save
     * @var boolean
     */
    protected $blnAutoUpdate;


    ////////////////////
    // Public Overriders
    ////////////////////

    /**
     * Override method to perform a property "Get"
     * This will get the value of $strName
     *
     * @param string $strName Name of the property to get
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function __get($strName)
    {
        switch ($strName) {
            case 'OwnerTable':
                return $this->objOwnerTable;
            case 'PrimaryKey':
                return $this->blnPrimaryKey;
            case 'Name':
                return $this->strName;
            case 'PropertyName':
                return $this->strPropertyName;
            case 'VariableName':
                return $this->strVariableName;
            case 'VariableType':
                return $this->strVariableType;
            case 'VariableTypeAsConstant':
                return $this->strVariableTypeAsConstant;
            case 'DbType':
                return $this->strDbType;
            case 'Length':
                return $this->intLength;
            case 'Default':
                return $this->mixDefault;
            case 'NotNull':
                return $this->blnNotNull;
            case 'Identity':
                return $this->blnIdentity;
            case 'Indexed':
                return $this->blnIndexed;
            case 'Unique':
                return $this->blnUnique;
            case 'Timestamp':
                return $this->blnTimestamp;
            case 'Reference':
                return $this->objReference;
            case 'Comment':
                return $this->strComment;
            case 'Options':
                return $this->options;
            case 'AutoUpdate':
                return $this->blnAutoUpdate;
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
                case 'OwnerTable':
                    //return $this->objOwnerTable = \QCubed\Type::cast($mixValue, 'SqlTable');
                    // $mixValue might be a SqlTable or a QTypeTable
                    $this->objOwnerTable = $mixValue;
                    break;
                case 'PrimaryKey':
                    $this->blnPrimaryKey = Type::cast($mixValue, Type::BOOLEAN);
                    break;
                case 'Name':
                    $this->strName = Type::cast($mixValue, Type::STRING);
                    break;
                case 'PropertyName':
                    $this->strPropertyName = Type::cast($mixValue, Type::STRING);
                    break;
                case 'VariableName':
                    $this->strVariableName = Type::cast($mixValue, Type::STRING);
                    break;
                case 'VariableType':
                    $this->strVariableType = Type::cast($mixValue, Type::STRING);
                    break;
                case 'VariableTypeAsConstant':
                    $this->strVariableTypeAsConstant = Type::cast($mixValue, Type::STRING);
                    break;
                case 'DbType':
                    $this->strDbType = Type::cast($mixValue, Type::STRING);
                    break;
                case 'Length':
                    $this->intLength = Type::cast($mixValue, Type::INTEGER);
                    break;
                case 'Default':
                    if ($mixValue === null || (($mixValue === '' || $mixValue === '0000-00-00 00:00:00' || $mixValue === '0000-00-00') && !$this->blnNotNull)) {
                        $this->mixDefault = null;
                    } else {
                        if (is_int($mixValue)) {
                            $this->mixDefault = Type::cast($mixValue, Type::INTEGER);
                        } else {
                            if (is_numeric($mixValue)) {
                                $this->mixDefault = Type::cast($mixValue, Type::FLOAT);
                            } else {
                                $this->mixDefault = Type::cast($mixValue, Type::STRING);
                            }
                        }
                    }
                    break;
                case 'NotNull':
                    $this->blnNotNull = Type::cast($mixValue, Type::BOOLEAN);
                    break;
                case 'Identity':
                    $this->blnIdentity = Type::cast($mixValue, Type::BOOLEAN);
                    break;
                case 'Indexed':
                    $this->blnIndexed = Type::cast($mixValue, Type::BOOLEAN);
                    break;
                case 'Unique':
                    $this->blnUnique = Type::cast($mixValue, Type::BOOLEAN);
                    break;
                case 'Timestamp':
                    $this->blnTimestamp = Type::cast($mixValue, Type::BOOLEAN);
                    break;
                case 'Reference':
                    $this->objReference = Type::cast($mixValue, Reference::class);
                    break;
                case 'Comment':
                    $this->strComment = Type::cast($mixValue, Type::STRING);
                    break;
                case 'Options':
                    $this->options = Type::cast($mixValue, Type::ARRAY_TYPE);
                    break;
                case 'AutoUpdate':
                    $this->blnAutoUpdate = Type::cast($mixValue, Type::BOOLEAN);
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
