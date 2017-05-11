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
 * Class FunctionNode
 * A node representing a SQL function call
 *
 * @package QCubed\Query\Node
 * @was QQFunctionNode
 */
class FunctionNode extends SubQueryBase
{
    /** @var  string */
    protected $strFunctionName;
    /** @var  array Could be constants or column nodes */
    protected $params;

    /**
     * QQFunctionNode constructor.
     * @param string $strFunctionName
     * @param string $params
     */
    public function __construct($strFunctionName, $params)
    {
        parent::__construct('', '', '');
        $this->strFunctionName = $strFunctionName;
        $this->params = $params;
    }

    /**
     * @param Builder $objBuilder
     * @return string
     */
    public function getColumnAlias(Builder $objBuilder)
    {
        $strSql = $this->strFunctionName . '(';
        foreach ($this->params as $param) {
            if ($param instanceof Column) {
                $strSql .= $param->getColumnAlias($objBuilder);
            } else {
                // just a basic value
                $strSql .= $param;
            }
            $strSql .= ',';
        }
        $strSql = substr($strSql, 0, -1);    // get rid of last comma
        $strSql .= ')';
        return $strSql;
    }

    public function __toString()
    {
        return 'Function Node ' . $this->strFunctionName;
    }
}

