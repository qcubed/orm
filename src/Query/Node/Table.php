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
use QCubed\Query\Clause;
use QCubed\Query\Builder;
use QCubed\Query\Condition\ConditionInterface as iCondition;

/**
 * Class Table
 * A node that represents a regular table. This can either be a root of the query node chain, or a forward looking
 * foreign key (as in one-to-one relationship).
 * @package QCubed\Query\Node
 * @was QQTableNode
 */
abstract class Table extends NodeBase
{
    /**
     * Initialize a table node. The subclass should fill in the table name, primary key and class name.
     *
     * @param $strName
     * @param null|string $strPropertyName If it has a parent, the property the parent uses to refer to this node.
     * @param null|string $strType If it has a parent, the type of the column in the parent that is the fk to this node. (Likely Integer).
     * @param NodeBase|null $objParentNode
     */
    public function __construct($strName, $strPropertyName = null, $strType = null, NodeBase $objParentNode = null)
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
     * Join the node to the query.
     * Otherwise, its a straightforward
     * one-to-one join. Conditional joins in this situation are really only useful when combined with condition
     * clauses that select out rows that were not joined (null FK).
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
            if ($this->strTableName != $objBuilder->RootTableName) {
                throw new Caller('Cannot use Node for "' . $this->strTableName . '" when querying against the "' . $objBuilder->RootTableName . '" table',
                    3);
            }
        } else {

            // Special case situation to allow applying a join condition on an association table.
            // The condition must be testing against the primary key of the joined table.
            if ($objJoinCondition &&
                $this->objParentNode instanceof Association &&
                $objJoinCondition->equalTables($this->objParentNode->fullAlias())
            ) {

                $objParentNode->join($objBuilder, $blnExpandSelection, $objJoinCondition, $objSelect);
                $objJoinCondition = null; // prevent passing join condition to this level
            } else {
                $objParentNode->join($objBuilder, $blnExpandSelection, null, $objSelect);
                if ($objJoinCondition && !$objJoinCondition->equalTables($this->fullAlias())) {
                    throw new Caller("The join condition on the \"" . $this->strTableName . "\" table must only contain conditions for that table.");
                }
            }

            try {
                $strParentAlias = $objParentNode->fullAlias();
                $strAlias = $this->fullAlias();
                //$strJoinTableAlias = $strParentAlias . '__' . ($this->strAlias ? $this->strAlias : $this->strName);
                $objBuilder->addJoinItem($this->strTableName, $strAlias,
                    $strParentAlias, $this->strName, $this->strPrimaryKey, $objJoinCondition);

                if ($blnExpandSelection) {
                    $this->putSelectFields($objBuilder, $strAlias, $objSelect);
                }
            } catch (Caller $objExc) {
                $objExc->incrementOffset();
                throw $objExc;
            }
        }
    }
}
