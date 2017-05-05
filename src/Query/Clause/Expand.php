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
 * Class Expand
 * @package QCubed\Query\Clause
 * @was QQExpand
 */
class Expand extends ObjectBase implements ClauseInterface
{
    /** @var Node\NodeBase */
    protected $objNode;
    protected $objJoinCondition;
    protected $objSelect;

    public function __construct($objNode, iCondition $objJoinCondition = null, Select $objSelect = null)
    {
        // Check against root and table QQNodes
        if ($objNode instanceof Node\Association) {
            throw new Caller('Expand clause parameter cannot be an association table node. Try expanding one level deeper.',
                2);
        } else {
            if (!($objNode instanceof Node\NodeBase)) {
                throw new Caller('Expand clause parameter must be a QQNode object', 2);
            } else {
                if (!$objNode->_ParentNode) {
                    throw new InvalidCast('Cannot expand on this kind of node.', 3);
                }
            }
        }

        $this->objNode = $objNode;
        $this->objJoinCondition = $objJoinCondition;
        $this->objSelect = $objSelect;
    }

    public function updateQueryBuilder(Builder $objBuilder)
    {
        $this->objNode->join($objBuilder, true, $this->objJoinCondition, $this->objSelect);
    }

    public function __toString()
    {
        return 'QQExpand Clause';
    }
}
