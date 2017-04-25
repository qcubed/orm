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
 */
class QQ {
	/////////////////////////
	// Condition Factories
	/////////////////////////

	/**
	 * @return Cond\All
	 */
	static public function All() {
		return new Cond\All(func_get_args());
	}

	/**
	 * @return Cond\None
	 */
	static public function None() {
		return new Cond\None(func_get_args());
	}

	/**
	 * @param mixed [$arg1, $arg2, ...]
	 * @return Cond\OrCondition
	 */
	static public function OrCondition(/* array and/or parameterized list of objects*/) {
		return new Cond\OrCondition(func_get_args());
	}

	/**
	 * @param mixed [$arg1, $arg2, ...]
	 * @return Cond\AndCondition
	 */
	static public function AndCondition(/* array and/or parameterized list of objects*/) {
		return new Cond\AndCondition(func_get_args());
	}

	/**
	 * @param iCondition $objCondition
	 * @return Cond\Not
	 */
	static public function Not(iCondition $objCondition) {
		return new Cond\Not($objCondition);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $mixValue
	 * @return Cond\Equal
	 */
	static public function Equal(Node\Column $objQueryNode, $mixValue) {
		return new Cond\Equal($objQueryNode, $mixValue);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $mixValue
	 * @return Cond\Equal
	 */
	static public function NotEqual(Node\Column $objQueryNode, $mixValue) {
		return new Cond\NotEqual($objQueryNode, $mixValue);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $mixValue
	 * @return Cond\GreaterThan
	 */
	static public function GreaterThan(Node\Column $objQueryNode, $mixValue) {
		return new Cond\GreaterThan($objQueryNode, $mixValue);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $mixValue
	 * @return Cond\GreaterOrEqual
	 */
	static public function GreaterOrEqual(Node\Column $objQueryNode, $mixValue) {
		return new Cond\GreaterOrEqual($objQueryNode, $mixValue);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $mixValue
	 * @return Cond\LessThan
	 */
	static public function LessThan(Node\Column $objQueryNode, $mixValue) {
		return new Cond\LessThan($objQueryNode, $mixValue);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $mixValue
	 * @return Cond\LessOrEqual
	 */
	static public function LessOrEqual(Node\Column $objQueryNode, $mixValue) {
		return new Cond\LessOrEqual($objQueryNode, $mixValue);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @return Cond\IsNull
	 */
	static public function IsNull(Node\Column $objQueryNode) {
		return new Cond\IsNull($objQueryNode);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @return Cond\IsNotNull
	 */
	static public function IsNotNull(Node\Column $objQueryNode) {
		return new Cond\IsNotNull($objQueryNode);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $mixValuesArray
	 * @return Cond\In
	 */
	static public function In(Node\Column $objQueryNode, $mixValuesArray) {
		return new Cond\In($objQueryNode, $mixValuesArray);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $mixValuesArray
	 * @return Cond\NotIn
	 */
	static public function NotIn(Node\Column $objQueryNode, $mixValuesArray) {
		return new Cond\NotIn($objQueryNode, $mixValuesArray);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $strValue
	 * @return Cond\Like
	 */
	static public function Like(Node\Column $objQueryNode, $strValue) {
		return new Cond\Like($objQueryNode, $strValue);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $strValue
	 * @return Cond\NotLike
	 */
	static public function NotLike(Node\Column $objQueryNode, $strValue) {
		return new Cond\NotLike($objQueryNode, $strValue);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param $mixMinValue
	 * @param $mixMaxValue
	 * @return Cond\Between
	 */
	static public function Between(Node\Column $objQueryNode, $mixMinValue, $mixMaxValue) {
		return new Cond\Between($objQueryNode, $mixMinValue, $mixMaxValue);
	}

	/**
	 * @param Node\Column $objQueryNode
	 * @param string $strMinValue
	 * @param string $strMaxValue
	 * @return Cond\NotBetween
	 */
	static public function NotBetween(Node\Column $objQueryNode, $strMinValue, $strMaxValue) {
		return new Cond\NotBetween($objQueryNode, $strMinValue, $strMaxValue);
	}

	/**
	 * @param Node\SubQuerySql $objQueryNode
	 * @return Cond\Exists
	 */
	static public function Exists(Node\SubQuerySql $objQueryNode) {
		return new Cond\Exists($objQueryNode);
	}

	/**
	 * @param Node\SubQuerySql $objQueryNode
	 * @return Cond\NotExists
	 */
	static public function NotExists(Node\SubQuerySql $objQueryNode) {
		return new Cond\NotExists($objQueryNode);
	}
	
	/////////////////////////
	// QQSubQuery Factories
	/////////////////////////

	/**
	 * @param string $strSql Sql string. Use {1}, {2}, etc. to represent nodes inside of the sql string.
	 * @param null|Node\AbstractBase[] $objParentQueryNodes	Array of nodes to specify replacement value in the sql.
	 * @return Node\SubQuerySql
	 */
	static public function SubSql($strSql, $objParentQueryNodes = null) {
		$objParentQueryNodeArray = func_get_args();
		return new Node\SubQuerySql($strSql, $objParentQueryNodeArray);
	}

	static public function Virtual($strName, Node\AbstractSubQuery $objSubQueryDefinition = null) {
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
	static public function GetVirtualAlias($strName) {
		$strName = trim($strName);
		$strName = str_replace(" ", "_", $strName);
		$strName = strtolower($strName);
		return $strName;
	}

	/////////////////////////
	// Clause\AbstractBase Factories
	/////////////////////////

	static public function Clause(/* parameterized list of Clause\AbstractBase objects */) {
		$objClauseArray = array();

		foreach (func_get_args() as $objClause)
			if ($objClause) {
				if (!($objClause instanceof Clause\ClauseInterface))
					throw new Caller('Non-Clause object was passed in to QQ::Clause');
				else
					array_push($objClauseArray, $objClause);
			}

		return $objClauseArray;
	}

	static public function OrderBy(/* array and/or parameterized list of Node\AbstractBase objects*/) {
		return new Clause\OrderBy(func_get_args());
	}

	static public function GroupBy(/* array and/or parameterized list of Node\AbstractBase objects*/) {
		return new Clause\GroupBy(func_get_args());
	}

	static public function Having(Node\SubQuerySql $objNode) {
		return new Clause\Having($objNode);
	}

	static public function Count(Node\Column $objNode, $strAttributeName) {
		return new Clause\Count($objNode, $strAttributeName);
	}

	static public function Sum(Node\Column $objNode, $strAttributeName) {
		return new Clause\Sum($objNode, $strAttributeName);
	}

	static public function Minimum(Node\Column $objNode, $strAttributeName) {
		return new Clause\Minimum($objNode, $strAttributeName);
	}

	static public function Maximum(Node\Column $objNode, $strAttributeName) {
		return new Clause\Maximum($objNode, $strAttributeName);
	}

	static public function Average(Node\Column $objNode, $strAttributeName) {
		return new Clause\Average($objNode, $strAttributeName);
	}

	static public function Expand(Node\AbstractBase $objNode, iCondition $objJoinCondition = null, Clause\Select $objSelect = null) {
//			if (gettype($objNode) == 'string')
//				return new Clause\ExpandVirtualNode(new Node\Virtual($objNode));

		if ($objNode instanceof Node\Virtual)
			return new Clause\ExpandVirtualNode($objNode);
		else
			return new Clause\Expand($objNode, $objJoinCondition, $objSelect);
	}

	static public function ExpandAsArray(Node\AbstractBase $objNode, $objCondition = null, Clause\Select $objSelect = null) {
		return new Clause\ExpandAsArray($objNode, $objCondition, $objSelect);
	}

	static public function Select(/* array and/or parameterized list of Node\AbstractBase objects*/) {
		if (func_num_args() == 1 && is_array($a = func_get_arg(0))) {
			return new Clause\Select($a);
		} else {
			return new Clause\Select(func_get_args());
		}
	}

	static public function LimitInfo($intMaxRowCount, $intOffset = 0) {
		return new Clause\Limit($intMaxRowCount, $intOffset);
	}

	static public function Distinct() {
		return new Clause\Distinct();
	}

	/**
	 * Searches for all the Clause\Select clauses and merges them into one clause and returns that clause.
	 * Returns null if none found.
	 *
	 * @param Clause\AbstractBase[]|Clause\AbstractBase|null $objClauses Clause\AbstractBase object or array of Clause\AbstractBase objects
	 * @return Clause\Select Clause\Select clause containing all the nodes from all the Clause\Select clauses from $objClauses,
	 * or null if $objClauses contains no Clause\Select clauses
	 */
	public static function ExtractSelectClause($objClauses) {
		if ($objClauses instanceof Clause\Select)
			return $objClauses;

		if (is_array($objClauses)) {
			$hasSelects = false;
			$objSelect = QQ::Select();
			foreach ($objClauses as $objClause) {
				if ($objClause instanceof Clause\Select) {
					$hasSelects = true;
					$objSelect->Merge($objClause);
				}
			}
			if (!$hasSelects)
				return null;
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
	 * @param Node\AbstractBase $objNode The node object to set alias on
	 * @param string $strAlias The alias to set
	 * @return mixed The same node that was passed in, but with the alias set
	 *
	 */
	static public function Alias(Node\AbstractBase $objNode, $strAlias)
	{
		$objNode->SetAlias($strAlias);
		return $objNode;
	}

	/////////////////////////
	// NamedValue QQ Node
	/////////////////////////
	static public function NamedValue($strName) {
		return new Node\NamedValue($strName);
	}

	/**
	 * Apply an arbitrary scalar function using the given parameters. See below for functions that let you apply
	 * common SQL functions. The list below only includes sql operations that are generic to all supported versions
	 * of SQL. However, you can call Func directly with any named function that works in your current SQL version,
	 * knowing that it might not be cross platform compatible if you ever change SQL engines.
	 *
	 * @param string $strName The function name, like ABS or POWER
	 * @param Node\AbstractBase|mixed $param1 The function parameter. Can be a qq node or a number.
	 * @return Node\FunctionNode The resulting wrapper node
	 */
	static public function Func($strName, $param1 /** ... */) {
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
	 * @param Node\AbstractBase $param The qq node to apply the function to.
	 * @return Node\FunctionNode The resulting wrapper node
	 */
	static public function Abs($param) {
		return QQ::Func('ABS', $param);
	}
	/**
	 * Return the smallest integer value not less than the argument
	 *
	 * @param Node\AbstractBase $param The qq node to apply the function to.
	 * @return Node\FunctionNode The resulting wrapper node
	 */
	static public function Ceil($param) {
		return QQ::Func('CEIL', $param);
	}
	/**
	 * Return the largest integer value not greater than the argument
	 *
	 * @param Node\AbstractBase $param The qq node to apply the function to.
	 * @return Node\FunctionNode The resulting wrapper node
	 */
	static public function Floor($param) {
		return QQ::Func('FLOOR', $param);
	}
	/**
	 * Return the remainder
	 *
	 * @param mixed $dividend
	 * @param mixed $divider
	 * @return Node\FunctionNode
	 */
	static public function Mod($dividend, $divider) {
		return QQ::Func('MOD', $dividend, $divider);
	}
	/**
	 * Return the argument raised to the specified power
	 *
	 * @param mixed $base
	 * @param mixed $exponent
	 * @return Node\FunctionNode
	 */
	static public function Power($base, $exponent) {
		return QQ::Func('POWER', $base, $exponent);
	}
	/**
	 * 	Return the square root of the argument
	 *
	 * @param Node\AbstractBase $param The qq node to apply the function to.
	 * @return Node\FunctionNode The resulting wrapper node
	 */
	static public function Sqrt($param) {
		return QQ::Func('SQRT', $param);
	}

	/**
	 * Apply an arbitrary math operation to 2 or more operands. Operands can be scalar values, or column nodes.
	 *
	 * @param string $strOperation The operation symbol, like + or *
	 * @param Node\AbstractBase|mixed $param1 The first parameter
	 * @return Node\Math The resulting wrapper node
	 */
	static public function MathOp($strOperation, $param1 /** ... */) {
		$args = func_get_args();
		$strFunc = array_shift($args);
		return new Node\Math($strFunc, $args);
	}

	/**
	 * The multiplication operation
	 *
	 * @param Node\AbstractBase|mixed $op1 The first operand
	 * @param Node\AbstractBase|mixed $op2 The second operand
	 * @return Node\Math The resulting wrapper node
	 */
	static public function Mul($op1, $op2 /** ... */) {
		return new Node\Math('*', func_get_args());
	}
	/**
	 * The division operation
	 *
	 * @param Node\AbstractBase|mixed $op1 The first operand
	 * @param Node\AbstractBase|mixed $op2 The second operand
	 * @return Node\Math The resulting wrapper node
	 */
	static public function Div($op1, $op2 /** ... */) {
		return new Node\Math('/', func_get_args());
	}
	/**
	 * The subtraction operation
	 *
	 * @param Node\AbstractBase|mixed $op1 The first operand
	 * @param Node\AbstractBase|mixed $op2 The second operand
	 * @return Node\Math The resulting wrapper node
	 */
	static public function Sub($op1, $op2 /** ... */) {
		return new Node\Math('-', func_get_args());
	}
	/**
	 * The addition operation
	 *
	 * @param Node\AbstractBase|mixed $op1 The first operand
	 * @param Node\AbstractBase|mixed $op2 The second operand
	 * @return Node\Math The resulting wrapper node
	 */
	static public function Add($op1, $op2 /** ... */) {
		return new Node\Math('+', func_get_args());
	}
	/**
	 * The negation unary operation
	 *
	 * @param Node\AbstractBase|mixed $op1 The first operand
	 * @return Node\Math The resulting wrapper node
	 */
	static public function Neg($op1) {
		return new Node\Math('-', [$op1]);
	}

}






