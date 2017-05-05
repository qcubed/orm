<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Clause;

use QCubed\Exception\Caller;
use QCubed\ObjectBase;
use QCubed\Query\Builder;
use QCubed\Query\Node\Virtual;

/**
 * Class ExpandVirtualNode
 * Node representing an expansion on a virtual node
 * @package QCubed\Query\Clause
 * @was QQExpandVirtualNode
 */
class ExpandVirtualNode extends ObjectBase implements ClauseInterface
{
    protected $objNode;

    public function __construct(Virtual $objNode)
    {
        $this->objNode = $objNode;
    }

    public function updateQueryBuilder(Builder $objBuilder)
    {
        try {
            $objBuilder->addSelectFunction(null, $this->objNode->getColumnAlias($objBuilder),
                $this->objNode->getAttributeName());
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

    public function __toString()
    {
        return 'QQExpandVirtualNode Clause';
    }
}
