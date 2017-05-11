<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Clause;

use QCubed\ObjectBase;
use QCubed\Exception\Caller;
use QCubed\Exception\InvalidCast;
use QCubed\Query\Builder;
use QCubed\Query\Condition\ConditionInterface as iCondition;
use QCubed\Query\Node;

/**
 * Class OrderBy
 * Sort clause
 * @package QCubed\Query\Clause
 * @was QQOrderBy
 */
class OrderBy extends ObjectBase implements ClauseInterface
{
    /** @var mixed[] */
    protected $objNodeArray;

    /**
     * CollapseNodes makes sure a node list is vetted, and turned into a node list.
     * This also allows table nodes to be used in certain column node contexts, in which it will
     * substitute the primary key node in this situation.
     *
     * @param $mixParameterArray
     * @return array
     * @throws Caller
     * @throws InvalidCast
     */
    protected function collapseNodes($mixParameterArray)
    {
        /** @var Node\NodeBase[] $objNodeArray */
        $objNodeArray = array();
        foreach ($mixParameterArray as $mixParameter) {
            if (is_array($mixParameter)) {
                $objNodeArray = array_merge($objNodeArray, $mixParameter);
            } else {
                array_push($objNodeArray, $mixParameter);
            }
        }

        $blnPreviousIsNode = false;
        $objFinalNodeArray = array();
        foreach ($objNodeArray as $objNode) {
            if (!($objNode instanceof Node\NodeBase || $objNode instanceof iCondition)) {
                if (!$blnPreviousIsNode) {
                    throw new Caller('OrderBy clause parameters must all be Node\NodeBase or iCondition objects followed by an optional true/false "Ascending Order" option',
                        3);
                }
                $blnPreviousIsNode = false;
                array_push($objFinalNodeArray, $objNode);
            } elseif ($objNode instanceof iCondition) {
                $blnPreviousIsNode = true;
                array_push($objFinalNodeArray, $objNode);
            } else {
                if (!$objNode->_ParentNode) {
                    throw new InvalidCast('Unable to cast "' . $objNode->_Name . '" table to Column-based Node\NodeBase',
                        4);
                }
                if ($objNode->_PrimaryKeyNode) { // if a table node, then use the primary key of the table
                    array_push($objFinalNodeArray, $objNode->_PrimaryKeyNode);
                } else {
                    array_push($objFinalNodeArray, $objNode);
                }
                $blnPreviousIsNode = true;
            }
        }

        if (count($objFinalNodeArray)) {
            return $objFinalNodeArray;
        } else {
            throw new Caller('No parameters passed in to OrderBy clause', 3);
        }
    }

    /**
     * Constructor function
     *
     * @param $mixParameterArray
     *
     * @throws Caller|InvalidCast
     */
    public function __construct($mixParameterArray)
    {
        $this->objNodeArray = $this->collapseNodes($mixParameterArray);
    }

    /**
     * Updates the query builder. We delay processing of orderby clauses until just before statement creation.
     *
     * @param Builder $objBuilder
     */
    public function updateQueryBuilder(Builder $objBuilder)
    {
        $objBuilder->setOrderByClause($this);
    }

    /**
     * Updates the query builder according to this clause. This is called by the query builder only.
     *
     * @param Builder $objBuilder
     * @throws Caller
     */
    public function _UpdateQueryBuilder(Builder $objBuilder)
    {
        $intLength = count($this->objNodeArray);
        for ($intIndex = 0; $intIndex < $intLength; $intIndex++) {
            $objNode = $this->objNodeArray[$intIndex];
            if ($objNode instanceof Node\Virtual) {
                if ($objNode->hasSubquery()) {
                    throw new Caller('You cannot define a virtual node in an order by clause. You must use an Expand clause to define it.');
                }
                $strOrderByCommand = '__' . $objNode->getAttributeName();
            } elseif ($objNode instanceof Node\Column) {
                /** @var Node\Column $objNode */
                $strOrderByCommand = $objNode->getColumnAlias($objBuilder);
            } elseif ($objNode instanceof iCondition) {
                /** @var iCondition $objNode */
                $strOrderByCommand = $objNode->getWhereClause($objBuilder);
            } else {
                $strOrderByCommand = '';
            }

            // Check to see if they want a ASC/DESC declarator
            if ((($intIndex + 1) < $intLength) &&
                !($this->objNodeArray[$intIndex + 1] instanceof Node\NodeBase)
            ) {
                if ((!$this->objNodeArray[$intIndex + 1]) ||
                    (trim(strtolower($this->objNodeArray[$intIndex + 1])) == 'desc')
                ) {
                    $strOrderByCommand .= ' DESC';
                } else {
                    $strOrderByCommand .= ' ASC';
                }
                $intIndex++;
            }

            $objBuilder->addOrderByItem($strOrderByCommand);
        }
    }


    /**
     * This is used primarly by datagrids wanting to use the "old Beta 2" style of
     * Manual Queries.  This allows a datagrid to use QQ::OrderBy even though
     * the manually-written Load method takes in Beta 2 string-based SortByCommand information.
     *
     * @return string
     */
    public function getAsManualSql()
    {
        $strOrderByArray = array();
        $intLength = count($this->objNodeArray);
        for ($intIndex = 0; $intIndex < $intLength; $intIndex++) {
            $strOrderByCommand = $this->objNodeArray[$intIndex]->getAsManualSqlColumn();

            // Check to see if they want a ASC/DESC declarator
            if ((($intIndex + 1) < $intLength) &&
                !($this->objNodeArray[$intIndex + 1] instanceof Node\NodeBase)
            ) {
                if ((!$this->objNodeArray[$intIndex + 1]) ||
                    (trim(strtolower($this->objNodeArray[$intIndex + 1])) == 'desc')
                ) {
                    $strOrderByCommand .= ' DESC';
                } else {
                    $strOrderByCommand .= ' ASC';
                }
                $intIndex++;
            }

            array_push($strOrderByArray, $strOrderByCommand);
        }

        return implode(',', $strOrderByArray);
    }

    public function __toString()
    {
        return 'QQOrderBy Clause';
    }
}
