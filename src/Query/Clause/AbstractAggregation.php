<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Clause;

use QCubed\AbstractBase;
use QCubed\Query\Builder;
use QCubed\Query\Node;
use QCubed\Query\QQ;

/**
 * Class AbstractAggregation
 * Base class for functions that work in cooperation with GroupBy clauses
 *
 * @package QCubed\Query\Clause
 * @was QQAggregationClause
 */
abstract class AbstractAggregation extends AbstractBase implements ClauseInterface
{
    /** @var Node\AbstractBase */
    protected $objNode;
    protected $strAttributeName;
    protected $strFunctionName;

    public function __construct(Node\Column $objNode, $strAttributeName)
    {
        $this->objNode = QQ::func($this->strFunctionName, $objNode);
        $this->strAttributeName = QQ::getVirtualAlias($strAttributeName); // virtual attributes are queried lower case
    }

    public function updateQueryBuilder(Builder $objBuilder)
    {
        $objBuilder->setVirtualNode($this->strAttributeName, $this->objNode);
        $objBuilder->addSelectFunction(null, $this->objNode->getColumnAlias($objBuilder), $this->strAttributeName);
    }
}
