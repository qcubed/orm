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

/**
 * Class Distinct
 * @package QCubed\Query\Clause
 * @was QQDistinct
 */
class Distinct extends ObjectBase implements ClauseInterface
{
    public function updateQueryBuilder(Builder $objBuilder)
    {
        $objBuilder->setDistinctFlag();
    }

    public function __toString()
    {
        return 'QQDistinct Clause';
    }
}

