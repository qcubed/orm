<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query;

use QCubed\Exception\Caller;
use QCubed\Query\Condition as Cond;
use QCubed\Query\Node;
use QCubed\Query\Condition\ConditionInterface as iCondition;
use QCubed\Query\Clause;


/**
 * Class QQ
 * Factory class of shortcuts for generating the various classes that eventually go in to a query.
 *
 * @package QCubed\Query
 * @was QQ
 */
class QQ
{
    /////////////////////////
    // Condition Factories
    /////////////////////////

    /**
     * @return Cond\All
     */
    static public function all()
    {
        return new Cond\All(func_get_args());
    }

    /**
     * @return Cond\None
     */
    static public function none()
    {
        return new Cond\None(func_get_args());
    }

    /**
     * @param mixed [$arg1, $arg2, ...]
     * @return Cond\OrCondition
     */
    static public function orCondition(/* array and/or parameterized list of objects*/)
    {
        return new Cond\OrCondition(func_get_args());
    }

    /**
     * @param mixed [$arg1, $arg2, ...]
     * @return Cond\AndCondition
     */
    static public function andCondition(/* array and/or parameterized list of objects*/)
    {
        return new Cond\AndCondition(func_get_args());
    }

    /**
     * @param iCondition $objCondition
     * @return Cond\Not
     */
    static public function not(iCondition $objCondition)
    {
        return new Cond\Not($objCondition);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $mixValue
     * @return Cond\Equal
     */
    static public function equal(Node\Column $objQueryNode, $mixValue)
    {
        return new Cond\Equal($objQueryNode, $mixValue);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $mixValue
     * @return Cond\NotEqual
     */
    static public function notEqual(Node\Column $objQueryNode, $mixValue)
    {
        return new Cond\NotEqual($objQueryNode, $mixValue);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $mixValue
     * @return Cond\GreaterThan
     */
    static public function greaterThan(Node\Column $objQueryNode, $mixValue)
    {
        return new Cond\GreaterThan($objQueryNode, $mixValue);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $mixValue
     * @return Cond\GreaterOrEqual
     */
    static public function greaterOrEqual(Node\Column $objQueryNode, $mixValue)
    {
        return new Cond\GreaterOrEqual($objQueryNode, $mixValue);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $mixValue
     * @return Cond\LessThan
     */
    static public function lessThan(Node\Column $objQueryNode, $mixValue)
    {
        return new Cond\LessThan($objQueryNode, $mixValue);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $mixValue
     * @return Cond\LessOrEqual
     */
    static public function lessOrEqual(Node\Column $objQueryNode, $mixValue)
    {
        return new Cond\LessOrEqual($objQueryNode, $mixValue);
    }

    /**
     * @param Node\Column $objQueryNode
     * @return Cond\IsNull
     */
    static public function isNull(Node\Column $objQueryNode)
    {
        return new Cond\IsNull($objQueryNode);
    }

    /**
     * @param Node\Column $objQueryNode
     * @return Cond\IsNotNull
     */
    static public function isNotNull(Node\Column $objQueryNode)
    {
        return new Cond\IsNotNull($objQueryNode);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $mixValuesArray
     * @return Cond\In
     */
    static public function in(Node\Column $objQueryNode, $mixValuesArray)
    {
        return new Cond\In($objQueryNode, $mixValuesArray);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $mixValuesArray
     * @return Cond\NotIn
     */
    static public function notIn(Node\Column $objQueryNode, $mixValuesArray)
    {
        return new Cond\NotIn($objQueryNode, $mixValuesArray);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $strValue
     * @return Cond\Like
     */
    static public function like(Node\Column $objQueryNode, $strValue)
    {
        return new Cond\Like($objQueryNode, $strValue);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $strValue
     * @return Cond\NotLike
     */
    static public function notLike(Node\Column $objQueryNode, $strValue)
    {
        return new Cond\NotLike($objQueryNode, $strValue);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param $mixMinValue
     * @param $mixMaxValue
     * @return Cond\Between
     */
    static public function between(Node\Column $objQueryNode, $mixMinValue, $mixMaxValue)
    {
        return new Cond\Between($objQueryNode, $mixMinValue, $mixMaxValue);
    }

    /**
     * @param Node\Column $objQueryNode
     * @param string $strMinValue
     * @param string $strMaxValue
     * @return Cond\NotBetween
     */
    static public function notBetween(Node\Column $objQueryNode, $strMinValue, $strMaxValue)
    {
        return new Cond\NotBetween($objQueryNode, $strMinValue, $strMaxValue);
    }

    /**
     * @param Node\SubQuerySql $objQueryNode
     * @return Cond\Exists
     */
    static public function exists(Node\SubQuerySql $objQueryNode)
    {
        return new Cond\Exists($objQueryNode);
    }

    /**
     * @param Node\SubQuerySql $objQueryNode
     * @return Cond\NotExists
     */
    static public function notExists(Node\SubQuerySql $objQueryNode)
    {
        return new Cond\NotExists($objQueryNode);
    }

    /////////////////////////
    // QQSubQuery Factories
    /////////////////////////

    /**
     * @param string $strSql Sql string. Use {1}, {2}, etc. to represent nodes inside of the sql string.
     * @param null|Node\NodeBase[] $objParentQueryNodes Array of nodes to specify replacement value in the sql.
     * @return Node\SubQuerySql
     */
    static public function subSql($strSql, $objParentQueryNodes = null)
    {
        $objParentQueryNodeArray = func_get_args();
        return new Node\SubQuerySql($strSql, $objParentQueryNodeArray);
    }

    static public function virtual($strName, Node\SubQueryBase $objSubQueryDefinition = null)
    {
        return new Node\Virtual($strName, $objSubQueryDefinition);
    }

    /**
     * Converts a virtual attribute name to an alias used in the query. The name is converted to an identifier
     * that will work on any SQL database. In the query itself, the name
     * will have two underscores in front of the alias name to prevent conflicts with column names.
     *
     * @param $strName
     * @return mixed|string
     */
    static public function getVirtualAlias($strName)
    {
        $strName = trim($strName);
        $strName = str_replace(" ", "_", $strName);
        $strName = strtolower($strName);
        return $strName;
    }

    /////////////////////////
    // Clause\Base Factories
    /////////////////////////

    static public function clause(/* parameterized list of Clause\Base objects */)
    {
        $objClauseArray = array();

        foreach (func_get_args() as $objClause) {
            if ($objClause) {
                if (!($objClause instanceof Clause\ClauseInterface)) {
                    throw new Caller('Non-Clause object was passed in to QQ::Clause');
                } else {
                    array_push($objClauseArray, $objClause);
                }
            }
        }

        return $objClauseArray;
    }

    static public function orderBy(/* array and/or parameterized list of Node\NodeBase objects*/)
    {
        return new Clause\OrderBy(func_get_args());
    }

    static public function groupBy(/* array and/or parameterized list of Node\NodeBase objects*/)
    {
        return new Clause\GroupBy(func_get_args());
    }

    static public function having(Node\SubQuerySql $objNode)
    {
        return new Clause\Having($objNode);
    }

    static public function count(Node\Column $objNode, $strAttributeName)
    {
        return new Clause\Count($objNode, $strAttributeName);
    }

    static public function sum(Node\Column $objNode, $strAttributeName)
    {
        return new Clause\Sum($objNode, $strAttributeName);
    }

    static public function minimum(Node\Column $objNode, $strAttributeName)
    {
        return new Clause\Minimum($objNode, $strAttributeName);
    }

    static public function maximum(Node\Column $objNode, $strAttributeName)
    {
        return new Clause\Maximum($objNode, $strAttributeName);
    }

    static public function average(Node\Column $objNode, $strAttributeName)
    {
        return new Clause\Average($objNode, $strAttributeName);
    }

    static public function expand(
        Node\NodeBase $objNode,
        iCondition $objJoinCondition = null,
        Clause\Select $objSelect = null
    ) {
//			if (gettype($objNode) == 'string')
//				return new Clause\ExpandVirtualNode(new Node\Virtual($objNode));

        if ($objNode instanceof Node\Virtual) {
            return new Clause\ExpandVirtualNode($objNode);
        } else {
            return new Clause\Expand($objNode, $objJoinCondition, $objSelect);
        }
    }

    static public function expandAsArray(
        Node\NodeBase $objNode,
        $objCondition = null,
        Clause\Select $objSelect = null
    ) {
        return new Clause\ExpandAsArray($objNode, $objCondition, $objSelect);
    }

    static public function select(/* array and/or parameterized list of Node\NodeBase objects*/)
    {
        if (func_num_args() == 1 && is_array($a = func_get_arg(0))) {
            return new Clause\Select($a);
        } else {
            return new Clause\Select(func_get_args());
        }
    }

    static public function limitInfo($intMaxRowCount, $intOffset = 0)
    {
        return new Clause\Limit($intMaxRowCount, $intOffset);
    }

    static public function distinct()
    {
        return new Clause\Distinct();
    }

    /**
     * Searches for all the Clause\Select clauses and merges them into one clause and returns that clause.
     * Returns null if none found.
     *
     * @param Clause\Base[]|Clause\Base|null $objClauses Clause\Base object or array of Clause\Base objects
     * @return Clause\Select Clause\Select clause containing all the nodes from all the Clause\Select clauses from $objClauses,
     * or null if $objClauses contains no Clause\Select clauses
     */
    public static function extractSelectClause($objClauses)
    {
        if ($objClauses instanceof Clause\Select) {
            return $objClauses;
        }

        if (is_array($objClauses)) {
            $hasSelects = false;
            $objSelect = QQ::select();
            foreach ($objClauses as $objClause) {
                if ($objClause instanceof Clause\Select) {
                    $hasSelects = true;
                    $objSelect->merge($objClause);
                }
            }
            if (!$hasSelects) {
                return null;
            }
            return $objSelect;
        }
        return null;
    }

    /////////////////////////
    // Aliased QQ Node
    /////////////////////////
    /**
     * Returns the supplied node object, after setting its alias to the value supplied
     *
     * @param Node\NodeBase $objNode The node object to set alias on
     * @param string $strAlias The alias to set
     * @return mixed The same node that was passed in, but with the alias set
     *
     */
    static public function alias(Node\NodeBase $objNode, $strAlias)
    {
        $objNode->setAlias($strAlias);
        return $objNode;
    }

    /////////////////////////
    // NamedValue QQ Node
    /////////////////////////
    static public function namedValue($strName)
    {
        return new Node\NamedValue($strName);
    }

    /**
     * Apply an arbitrary scalar function using the given parameters. See below for functions that let you apply
     * common SQL functions. The list below only includes sql operations that are generic to all supported versions
     * of SQL. However, you can call Func directly with any named function that works in your current SQL version,
     * knowing that it might not be cross platform compatible if you ever change SQL engines.
     *
     * @param string $strName The function name, like ABS or POWER
     * @param Node\NodeBase|mixed $param1 The function parameter. Can be a qq node or a number.
     * @return Node\FunctionNode The resulting wrapper node
     */
    static public function func($strName, $param1/** ... */)
    {
        $args = func_get_args();
        $strFunc = array_shift($args);
        return new Node\FunctionNode($strFunc, $args);
    }

    //////////////////////////////
    // Various common functions
    //////////////////////////////

    /**
     * Return the absolute value
     *
     * @param Node\NodeBase $param The qq node to apply the function to.
     * @return Node\FunctionNode The resulting wrapper node
     */
    static public function abs($param)
    {
        return QQ::func('ABS', $param);
    }

    /**
     * Return the smallest integer value not less than the argument
     *
     * @param Node\NodeBase $param The qq node to apply the function to.
     * @return Node\FunctionNode The resulting wrapper node
     */
    static public function ceil($param)
    {
        return QQ::func('CEIL', $param);
    }

    /**
     * Return the largest integer value not greater than the argument
     *
     * @param Node\NodeBase $param The qq node to apply the function to.
     * @return Node\FunctionNode The resulting wrapper node
     */
    static public function floor($param)
    {
        return QQ::func('FLOOR', $param);
    }

    /**
     * Return the remainder
     *
     * @param mixed $dividend
     * @param mixed $divider
     * @return Node\FunctionNode
     */
    static public function mod($dividend, $divider)
    {
        return QQ::func('MOD', $dividend, $divider);
    }

    /**
     * Return the argument raised to the specified power
     *
     * @param mixed $base
     * @param mixed $exponent
     * @return Node\FunctionNode
     */
    static public function power($base, $exponent)
    {
        return QQ::func('POWER', $base, $exponent);
    }

    /**
     *    Return the square root of the argument
     *
     * @param Node\NodeBase $param The qq node to apply the function to.
     * @return Node\FunctionNode The resulting wrapper node
     */
    static public function sqrt($param)
    {
        return QQ::func('SQRT', $param);
    }

    /**
     * Apply an arbitrary math operation to 2 or more operands. Operands can be scalar values, or column nodes.
     *
     * @param string $strOperation The operation symbol, like + or *
     * @param Node\NodeBase|mixed $param1 The first parameter
     * @return Node\Math The resulting wrapper node
     */
    static public function mathOp($strOperation, $param1/** ... */)
    {
        $args = func_get_args();
        $strFunc = array_shift($args);
        return new Node\Math($strFunc, $args);
    }

    /**
     * The multiplication operation
     *
     * @param Node\NodeBase|mixed $op1 The first operand
     * @param Node\NodeBase|mixed $op2 The second operand
     * @return Node\Math The resulting wrapper node
     */
    static public function mul($op1, $op2/** ... */)
    {
        return new Node\Math('*', func_get_args());
    }

    /**
     * The division operation
     *
     * @param Node\NodeBase|mixed $op1 The first operand
     * @param Node\NodeBase|mixed $op2 The second operand
     * @return Node\Math The resulting wrapper node
     */
    static public function div($op1, $op2/** ... */)
    {
        return new Node\Math('/', func_get_args());
    }

    /**
     * The subtraction operation
     *
     * @param Node\NodeBase|mixed $op1 The first operand
     * @param Node\NodeBase|mixed $op2 The second operand
     * @return Node\Math The resulting wrapper node
     */
    static public function sub($op1, $op2/** ... */)
    {
        return new Node\Math('-', func_get_args());
    }

    /**
     * The addition operation
     *
     * @param Node\NodeBase|mixed $op1 The first operand
     * @param Node\NodeBase|mixed $op2 The second operand
     * @return Node\Math The resulting wrapper node
     */
    static public function add($op1, $op2/** ... */)
    {
        return new Node\Math('+', func_get_args());
    }

    /**
     * The negation unary operation
     *
     * @param Node\NodeBase|mixed $op1 The first operand
     * @return Node\Math The resulting wrapper node
     */
    static public function neg($op1)
    {
        return new Node\Math('-', [$op1]);
    }

}






