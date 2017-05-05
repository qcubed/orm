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
 * Class Math
 * Node to represent a math operation between a set of values
 * @package QCubed\Query\Node
 * @was QQMathNode
 */
class Math extends SubQueryBase
{
    /** @var  string */
    protected $strOperation;
    /** @var  array Could be constants or column nodes */
    protected $params;

    /**
     * Math constructor.
     * @param string $strOperation
     * @param mixed[] $params
     */
    public function __construct($strOperation, array $params)
    {
        parent::__construct('', '', '');
        $this->strOperation = $strOperation;
        $this->params = $params;
    }

    /**
     * @param Builder $objBuilder
     * @return string
     */
    public function getColumnAlias(Builder $objBuilder)
    {
        if (count($this->params) == 0) {
            return '';
        }

        $strSql = '(';

        if (count($this->params) == 1) {
            // unary
            $strSql .= $this->strOperation;
        }
        foreach ($this->params as $param) {
            if ($param instanceof Column) {
                $strSql .= $param->getColumnAlias($objBuilder);
            } else {
                // just a basic value
                $strSql .= $param;
            }
            $strSql .= ' ' . $this->strOperation . ' ';
        }
        $strSql = substr($strSql, 0, -(strlen($this->strOperation) + 2));    // get rid of last operation
        $strSql .= ')';
        return $strSql;
    }

    public function __toString()
    {
        return 'Math Node ' . $this->strOperation;
    }

}

