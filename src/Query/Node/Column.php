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
use QCubed\Query\Builder;
use QCubed\Query\Condition\ConditionInterface as iCondition;
use QCubed\Query\Clause;

/**
 * Class Column
 * A node that represents a column in a table.
 * @package QCubed\Query\Condition
 * @was QQColumnNode
 */
class Column extends NodeBase
{
    /**
     * Initialize a column node.
     * @param string $strName
     * @param string $strPropertyName
     * @param string $strType
     * @param NodeBase|null $objParentNode
     */
    public function __construct($strName, $strPropertyName, $strType, NodeBase $objParentNode = null)
    {
        $this->objParentNode = $objParentNode;
        $this->strName = $strName;
        $this->strAlias = $strName;
        if ($objParentNode) {
            $objParentNode->objChildNodeArray[$strName] = $this;
        }

        $this->strPropertyName = $strPropertyName;
        $this->strType = $strType;
        if ($objParentNode) {
            $this->strRootTableName = $objParentNode->strRootTableName;
        } else {
            $this->strRootTableName = $strName;
        }
    }

    /**
     * @param Builder $objBuilder
     * @return string
     */
    public function getColumnAlias(Builder $objBuilder)
    {
        $this->join($objBuilder);
        $strParentAlias = $this->objParentNode->fullAlias();
        $strTableAlias = $objBuilder->getTableAlias($strParentAlias);
        // Pull the Begin and End Escape Identifiers from the Database Adapter
        return $this->makeColumnAlias($objBuilder, $strTableAlias);
    }

    /**
     * @param Builder $objBuilder
     * @param $strTableAlias
     * @return string
     */
    public function makeColumnAlias(Builder $objBuilder, $strTableAlias)
    {
        $strBegin = $objBuilder->Database->EscapeIdentifierBegin;
        $strEnd = $objBuilder->Database->EscapeIdentifierEnd;

        return sprintf('%s%s%s.%s%s%s',
            $strBegin, $strTableAlias, $strEnd,
            $strBegin, $this->strName, $strEnd);
    }


    /**
     * @return string
     */
    public function getTable()
    {
        return $this->objParentNode->fullAlias();
    }

    /**
     * Join the node to the given query. Since this is a leaf node, we pass on the join to the parent.
     *
     * @param Builder $objBuilder
     * @param bool $blnExpandSelection
     * @param iCondition|null $objJoinCondition
     * @param Clause\Select|null $objSelect
     * @throws Caller
     */
    public function join(
        Builder $objBuilder,
        $blnExpandSelection = false,
        iCondition $objJoinCondition = null,
        Clause\Select $objSelect = null
    ) {
        $objParentNode = $this->objParentNode;
        if (!$objParentNode) {
            throw new Caller('A column node must have a parent node.');
        } else {
            // Here we pass the join condition on to the parent object
            $objParentNode->join($objBuilder, $blnExpandSelection, $objJoinCondition, $objSelect);
        }
    }

    /**
     * Get the unaliased column name. For special situations, like order by, since you can't order by aliases.
     * @return string
     */
    public function getAsManualSqlColumn()
    {
        if ($this->strTableName) {
            return $this->strTableName . '.' . $this->strName;
        } else {
            if (($this->objParentNode) && ($this->objParentNode->strTableName)) {
                return $this->objParentNode->strTableName . '.' . $this->strName;
            } else {
                return $this->strName;
            }
        }
    }

}
