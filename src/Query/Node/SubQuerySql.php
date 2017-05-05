<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Node;

use QCubed\Query\Builder;

/**
 * Class SubQuerySql
 * Node to output custom SQL as a sub-query
 * @package QCubed\Query\Node
 * @was QQSubQuerySqlNode
 */
class SubQuerySql extends NoParentBase
{
    protected $strSql;
    /** @var NodeBase[] */
    protected $objParentQueryNodes;

    /**
     * @param $strSql
     * @param null|Column[] $objParentQueryNodes
     */
    public function __construct($strSql, $objParentQueryNodes = null)
    {
        parent::__construct('', '', '');
        $this->objParentNode = true;
        $this->objParentQueryNodes = $objParentQueryNodes;
        $this->strSql = $strSql;
    }

    /**
     * @param Builder $objBuilder
     * @return string
     */
    public function getColumnAlias(Builder $objBuilder)
    {
        $strSql = $this->strSql;
        for ($intIndex = 1; $intIndex < count($this->objParentQueryNodes); $intIndex++) {
            if (!is_null($this->objParentQueryNodes[$intIndex])) {
                $strSql = str_replace('{' . $intIndex . '}',
                    $this->objParentQueryNodes[$intIndex]->getColumnAlias($objBuilder), $strSql);
            }
        }
        if (stripos($strSql, 'SELECT') === 0) {
            return '(' . $strSql . ')';
        } else {
            return $strSql;
        }
    }
}
