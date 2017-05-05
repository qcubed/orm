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
 * Class Association
 * Describes a many-to-many relationship in the database that uses an association table to link two other tables together.
 * @package QCubed\Query\Node
 * @was QQAssociationNode
 */
class Association extends NodeBase
{
    /**
     * @param NodeBase $objParentNode
     * @throws \Exception
     */
    public function __construct(NodeBase $objParentNode)
    {
        $this->objParentNode = $objParentNode;
        if ($objParentNode) {
            $this->strRootTableName = $objParentNode->_RootTableName;
            $this->strAlias = $this->strName;
            $objParentNode->objChildNodeArray[$this->strAlias] = $this;
        } else {
            throw new \Exception ("Association Nodes must always have a parent node");
        }
    }

    /**
     * Join the node to the query. Join condition here gets applied to parent item.
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
        $objParentNode->join($objBuilder, $blnExpandSelection, null, $objSelect);
        if ($objJoinCondition && !$objJoinCondition->equalTables($this->fullAlias())) {
            throw new Caller("The join condition on the \"" . $this->strTableName . "\" table must only contain conditions for that table.");
        }

        try {
            $strParentAlias = $objParentNode->fullAlias();
            $strAlias = $this->fullAlias();
            //$strJoinTableAlias = $strParentAlias . '__' . ($this->strAlias ? $this->strAlias : $this->strName);
            $objBuilder->addJoinItem($this->strTableName, $strAlias,
                $strParentAlias, $objParentNode->_PrimaryKey, $this->strPrimaryKey, $objJoinCondition);

            if ($blnExpandSelection) {
                $this->putSelectFields($objBuilder, $strAlias, $objSelect);
            }
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }
}

