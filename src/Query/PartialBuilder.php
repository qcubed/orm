<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query;

/**
 * Class PartialBuilder
 *    Subclasses Builder to handle the building of conditions for conditional expansions, subqueries, etc.
 *    Since regular queries use WhereClauses for conditions, we just use the where clause portion, and
 *    only build a condition clause appropriate for a conditional expansion.
 * @was QPartialQueryBuilder
 */
class PartialBuilder extends Builder
{
    protected $objParentBuilder;

    /**
     * @param Builder $objBuilder
     */
    public function __construct(Builder $objBuilder)
    {
        parent::__construct($objBuilder->objDatabase, $objBuilder->strRootTableName);
        $this->objParentBuilder = $objBuilder;
        $this->strColumnAliasArray = &$objBuilder->strColumnAliasArray;
        $this->strTableAliasArray = &$objBuilder->strTableAliasArray;

        $this->intTableAliasCount = &$objBuilder->intTableAliasCount;
        $this->intColumnAliasCount = &$objBuilder->intColumnAliasCount;
    }

    /**
     * @return string
     */
    public function getWhereStatement()
    {
        return implode(' ', $this->strWhereArray);
    }

    /**
     * @return string
     */
    public function getFromStatement()
    {
        return implode(' ', $this->strFromArray) . ' ' . implode(' ', $this->strJoinArray);
    }
}

