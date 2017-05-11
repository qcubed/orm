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
 * Class ReverseReference
 * Describes a foreign key relationship that links to the primary key in the parent table. Relationship can be unique (one-to-one) or
 * not unique (many-to-one).
 * @package QCubed\Query\Node
 * @was QQReverseReferenceNode
 */
class ReverseReference extends Table
{
    /** @var string The name of the foreign key in the linked table. */
    protected $strForeignKey;

    /**
     * Construct the reverse reference.
     *
     * @param NodeBase $objParentNode
     * @param null|string $strName
     * @param null|string $strType
     * @param null|string $strForeignKey
     * @param null $strPropertyName If a unique reverse relationship, the name of property that will be used in the model class.
     * @throws Caller
     */
    public function __construct(
        NodeBase $objParentNode,
        $strName,
        $strType,
        $strForeignKey,
        $strPropertyName = null
    ) {
        parent::__construct($strName, $strPropertyName, $strType, $objParentNode);
        if (!$objParentNode) {
            throw new Caller('ReverseReferenceNodes must have a Parent Node');
        }
        $objParentNode->objChildNodeArray[$strName] = $this;
        $this->strForeignKey = $strForeignKey;
    }

    /**
     * Return true if this is a unique reverse relationship.
     *
     * @return bool
     */
    public function isUnique()
    {
        return !empty($this->strPropertyName);
    }

    /**
     * Join a node to the query. Since this is a reverse looking node, conditions control which items are joined.
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
                $strParentAlias, $this->objParentNode->_PrimaryKey, $this->strForeignKey, $objJoinCondition);

            if ($blnExpandSelection) {
                $this->putSelectFields($objBuilder, $strAlias, $objSelect);
            }
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

}
