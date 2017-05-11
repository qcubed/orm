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
use QCubed\Query\Node;

/**
 * Class GroupBy
 * @package QCubed\Query\Clause
 * @was QQGroupBy
 */
class GroupBy extends \QCubed\ObjectBase implements ClauseInterface
{
    /** @var Node\Column[] */
    protected $objNodeArray;

    /**
     * CollapseNodes makes sure a node list is vetted, and turned into a node list.
     * This also allows table nodes to be used in certain column node contexts, in which it will
     * substitute the primary key node in this situation.
     *
     * @param $mixParameterArray
     * @return Node\Column[]
     * @throws Caller
     * @throws InvalidCast
     */
    protected function collapseNodes($mixParameterArray)
    {
        $objNodeArray = array();
        foreach ($mixParameterArray as $mixParameter) {
            if (is_array($mixParameter)) {
                $objNodeArray = array_merge($objNodeArray, $mixParameter);
            } else {
                array_push($objNodeArray, $mixParameter);
            }
        }

        $objFinalNodeArray = array();
        foreach ($objNodeArray as $objNode) {
            /** @var Node\NodeBase $objNode */
            if ($objNode instanceof Node\Association) {
                throw new Caller('GroupBy clause parameter cannot be an association table node.', 3);
            } else {
                if (!($objNode instanceof Node\NodeBase)) {
                    throw new Caller('GroupBy clause parameters must all be QQNode objects.', 3);
                }
            }

            if (!$objNode->_ParentNode) {
                throw new InvalidCast('Unable to cast "' . $objNode->_Name . '" table to Column-based QQNode', 4);
            }

            if ($objNode->_PrimaryKeyNode) {
                array_push($objFinalNodeArray,
                    $objNode->_PrimaryKeyNode);    // if a table node, use the primary key of the table instead
            } else {
                array_push($objFinalNodeArray, $objNode);
            }
        }

        if (count($objFinalNodeArray)) {
            return $objFinalNodeArray;
        } else {
            throw new Caller('No parameters passed in to Expand clause', 3);
        }
    }

    public function __construct($mixParameterArray)
    {
        $this->objNodeArray = $this->collapseNodes($mixParameterArray);
    }

    public function updateQueryBuilder(Builder $objBuilder)
    {
        $intLength = count($this->objNodeArray);
        for ($intIndex = 0; $intIndex < $intLength; $intIndex++) {
            $objBuilder->addGroupByItem($this->objNodeArray[$intIndex]->getColumnAlias($objBuilder));
        }
    }

    public function __toString()
    {
        return 'GroupBy Clause';
    }
}

