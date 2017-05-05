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
use QCubed\Query\Builder;
use QCubed\Query\Node\SubQueryBase;

/**
 * Class Having
 * Allows a custom sql injection as a having clause. Its up to you to make sure its correct, but you can use subquery placeholders
 * to expand column names. Standard SQL has limited Having capabilities, but many SQL engines have useful extensions.
 * @package QCubed\Query\Clause
 * @was QQHavingClause
 */
class Having extends ObjectBase implements ClauseInterface
{
    protected $objNode;

    public function __construct(SubQueryBase $objSubQueryDefinition)
    {
        $this->objNode = $objSubQueryDefinition;
    }

    public function updateQueryBuilder(Builder $objBuilder)
    {
        $objBuilder->addHavingItem(
            $this->objNode->getColumnAlias($objBuilder)
        );
    }

    public function getAttributeName()
    {
        return $this->objNode->_Name;
    }

    public function __toString()
    {
        return "Having Clause";
    }

}

