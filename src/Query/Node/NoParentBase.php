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
use QCubed\Type;

/**
 * Class NoParentBase
 * Node that represents special asub queries that do not have parent nodes.
 * @package QCubed\Query\Node
 * @was QQNoParentNode
 */
abstract class NoParentBase extends SubQueryBase
{
    /**
     * @return string
     */
    public function getTable()
    {
        return $this->fullAlias();
    }

    /**
     * Change the alias of the node, primarily for joining the same table more than once.
     *
     * @param $strAlias
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
            return $this->strAlias;
        }
    }
}