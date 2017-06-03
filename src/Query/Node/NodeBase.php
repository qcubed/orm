<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Node;

use QCubed\Exception\Caller;
use QCubed\ObjectBase;
use QCubed\Query\Builder;
use QCubed\Query\Clause\Select;
use QCubed\Query\Condition\ConditionInterface as iCondition;
use QCubed\Type;

/**
 * The abstract Node base class. This represents an "object" in a SQL join tree. There are a number of different subclasses of
 * the Node, depending on the kind of object represented. The top of the join tree is generally a table node, and
 * the bottom is generally a column node, but that depends on the context in which the node is being used.
 *
 * The properties begin with underscores to prevent name conflicts with codegenerated subclasses.
 *
 * @property-read NodeBase $_ParentNode        // Parent object in tree.
 * @property-read string $_Name                // Default SQL name in query, or default alias
 * @property-read string $_Alias                // Actual alias. Usually the name, unless changed by QQ::alias() call
 * @property-read string $_PropertyName        // The name as used in PHP
 * @property-read string $_Type                // The type of object. A SQL type if referring to a column.
 * @property-read string $_RootTableName        // The name of the table at the top of the tree. Rednundant, since it could be found be following the chain.
 * @property-read string $_TableName            // The name of the table associated with this node, if its not a column node.
 * @property-read string $_PrimaryKey
 * @property-read string $_ClassName
 * @property-read NodeBase $_PrimaryKeyNode
 * @property bool $ExpandAsArray True if this node should be array expanded.
 * @property-read bool $IsType Is a type table node. For association type arrays.
 * @property-read NodeBase $ChildNodeArray
 * @was QQNode
 */
abstract class NodeBase extends ObjectBase
{
    /** @var null|NodeBase|bool */
    protected $objParentNode;
    /** @var  string Type node. SQL type or table type */
    protected $strType;
    /** @var  string SQL Name of related object in the database */
    protected $strName;
    /** @var  string Alias, if one was assigned using QQ::alias(). Otherwise, same as name. */
    protected $strAlias;
    /** @var  string resolved alias that includes parent join tables. */
    protected $strFullAlias;
    /** @var  string PHP property name of the related PHP object */
    protected $strPropertyName;
    /** @var  string copy of the root table name at the top of the node tree. */
    protected $strRootTableName;
    /** @var  string name of SQL table associated with this node. Generally set by subclasses. */
    protected $strTableName;

    /** @var  string SQL primary key, for nodes that have primary keys */
    protected $strPrimaryKey;
    /** @var  string PHP class name */
    protected $strClassName;

    // used by expansion nodes
    /** @var  bool True if this is an expand as array node point */
    protected $blnExpandAsArray;
    /** @var  NodeBase[] the array of child nodes if this is an expand as array point */
    protected $objChildNodeArray;
    /** @var  bool True if this is a Type node */
    protected $blnIsType;

    abstract public function join(
        Builder $objBuilder,
        $blnExpandSelection = false,
        iCondition $objJoinCondition = null,
        Select $objSelect = null
    );

    /**
     * Return the variable type. Should be a FieldType enum.
     * @return string
     */
    public function getType()
    {
        return $this->strType;
    }

    /**
     * Change the alias of the node, primarily for joining the same table more than once.
     * @param string $strAlias
     * @throws Caller
     * @throws \Exception
     */
    public function setAlias($strAlias)
    {
        if ($this->strFullAlias) {
            throw new \Exception ("You cannot set an alias on a node after you have used it in a query. See the examples doc. You must set the alias while creating the node.");
        }
        try {
            // Changing the alias of the node. Must change pointers to the node too.
            $strNewAlias = Type::cast($strAlias, Type::STRING);
            if ($this->objParentNode) {
                assert(is_object($this->objParentNode));
                unset($this->objParentNode->objChildNodeArray[$this->strAlias]);
                $this->objParentNode->objChildNodeArray[$strNewAlias] = $this;
            }
            $this->strAlias = $strNewAlias;
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

    /**
     * Aid to generating full aliases. Recursively gets and sets the parent alias, eventually creating, caching and returning
     * an alias for itself.
     * @return string
     */
    public function fullAlias()
    {
        if ($this->strFullAlias) {
            return $this->strFullAlias;
        } else {
            assert(!empty($this->strAlias));    // Alias should always be set by default
            if ($this->objParentNode) {
                assert(is_object($this->objParentNode));
                return $this->objParentNode->fullAlias() . '__' . $this->strAlias;
            } else {
                return $this->strAlias;
            }
        }
    }

    /**
     * Returns the fields in this node. Assumes its a table node.
     * @return string[]
     */
    public function fields()
    {
        return [];
    }

    /**
     * Returns the primary key fields in this node. Assumes its a table node.
     * @return string[]
     */
    public function primaryKeyFields()
    {
        return [];
    }

    /**
     * Merges a node tree into this node, building the child nodes. The node being received
     * is assumed to be specially built node such that only one child node exists, if any,
     * and the last node in the chain is designated as array expansion. The goal of all of this
     * is to set up a node chain where intermediate nodes can be designated as being array
     * expansion nodes, as well as the leaf nodes.
     *
     * @param NodeBase $objNewNode
     * @throws Caller
     */
    public function _MergeExpansionNode(NodeBase $objNewNode)
    {
        if (!$objNewNode || empty($objNewNode->objChildNodeArray)) {
            return;
        }
        if ($objNewNode->strName != $this->strName) {
            throw new Caller('Expansion node tables must match.');
        }

        if (!$this->objChildNodeArray) {
            $this->objChildNodeArray = $objNewNode->objChildNodeArray;
        } else {
            $objChildNode = reset($objNewNode->objChildNodeArray);
            if (isset ($this->objChildNodeArray[$objChildNode->strAlias])) {
                if ($objChildNode->blnExpandAsArray) {
                    $this->objChildNodeArray[$objChildNode->strAlias]->blnExpandAsArray = true;
                    // assume this is a leaf node, so don't follow any more.
                } else {
                    $this->objChildNodeArray[$objChildNode->strAlias]->_MergeExpansionNode($objChildNode);
                }
            } else {
                $this->objChildNodeArray[$objChildNode->strAlias] = $objChildNode;
            }
        }
    }

    /**
     * Puts the "Select" clause fields for this node into builder.
     *
     * @param Builder $objBuilder
     * @param null|string $strPrefix
     * @param null|Select $objSelect
     */
    public function putSelectFields($objBuilder, $strPrefix = null, $objSelect = null)
    {
        if ($strPrefix) {
            $strTableName = $strPrefix;
            $strAliasPrefix = $strPrefix . '__';
        } else {
            $strTableName = $this->strTableName;
            $strAliasPrefix = '';
        }

        if ($objSelect) {
            if (!$objSelect->skipPrimaryKey() && !$objBuilder->Distinct) {
                $strFields = $this->primaryKeyFields();
                foreach ($strFields as $strField) {
                    $objBuilder->addSelectItem($strTableName, $strField, $strAliasPrefix . $strField);
                }
            }
            $objSelect->addSelectItems($objBuilder, $strTableName, $strAliasPrefix);
        } else {
            $strFields = $this->fields();
            foreach ($strFields as $strField) {
                $objBuilder->addSelectItem($strTableName, $strField, $strAliasPrefix . $strField);
            }
        }
    }

    /**
     * @return NodeBase|null
     */
    public function firstChild()
    {
        $a = $this->objChildNodeArray;
        if ($a) {
            return reset($a);
        } else {
            return null;
        }
    }

    /**
     * Returns the extended table associated with the node.
     * @return string
     */
    public function getTable()
    {
        return $this->fullAlias();
    }

    /**
     * @param mixed $mixValue
     * @param Builder $objBuilder
     * @param boolean $blnEqualityType can be null (for no equality), true (to add a standard "equal to") or false (to add a standard "not equal to")
     * @return string
     * @throws Caller
     */
    public static function getValue($mixValue, Builder $objBuilder, $blnEqualityType = null)
    {
        if ($mixValue instanceof NamedValue) {
            /** @var NamedValue $mixValue */
            return $mixValue->parameter($blnEqualityType);
        }

        if ($mixValue instanceof NodeBase) {
            /** @var NodeBase $mixValue */
            if ($n = $mixValue->_PrimaryKeyNode) {
                $mixValue = $n;    // Convert table node to column node
            }
            /** @var Column $mixValue */
            if (is_null($blnEqualityType)) {
                $strToReturn = '';
            } else {
                if ($blnEqualityType) {
                    $strToReturn = '= ';
                } else {
                    $strToReturn = '!= ';
                }
            }

            try {
                return $strToReturn . $mixValue->getColumnAlias($objBuilder);
            } catch (Caller $objExc) {
                $objExc->incrementOffset();
                throw $objExc;
            }
        } else {
            if (is_null($blnEqualityType)) {
                $blnIncludeEquality = false;
                $blnReverseEquality = false;
            } else {
                $blnIncludeEquality = true;
                if ($blnEqualityType) {
                    $blnReverseEquality = false;
                } else {
                    $blnReverseEquality = true;
                }
            }

            return $objBuilder->Database->sqlVariable($mixValue, $blnIncludeEquality, $blnReverseEquality);
        }
    }

    public function __get($strName)
    {
        switch ($strName) {
            case '_ParentNode':
                return $this->objParentNode;
            case '_Name':
                return $this->strName;
            case '_Alias':
                return $this->strAlias;
            case '_PropertyName':
                return $this->strPropertyName;
            case '_Type':
                return $this->strType;
            case '_RootTableName':
                return $this->strRootTableName;
            case '_TableName':
                return $this->strTableName;
            case '_PrimaryKey':
                return $this->strPrimaryKey;
            case '_ClassName':
                return $this->strClassName;
            case '_PrimaryKeyNode':
                return null;

            case 'ExpandAsArray':
                return $this->blnExpandAsArray;
            case 'IsType':
                return $this->blnIsType;

            case 'ChildNodeArray':
                return $this->objChildNodeArray;

            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }

    public function __set($strName, $mixValue)
    {
        switch ($strName) {
            case 'ExpandAsArray':
                try {
                    return ($this->blnExpandAsArray = Type::cast($mixValue, Type::BOOLEAN));
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            default:
                try {
                    return parent::__set($strName, $mixValue);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }


}
