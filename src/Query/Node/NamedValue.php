<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Node;

use QCubed\Query\Clause;
use QCubed\Query\Builder;
use QCubed\Query\Condition\ConditionInterface as iCondition;

/**
 * Class NamedValue
 * Special node for referring to a node within a custom SQL clause.
 * @package QCubed\Query\Node
 * @was QQNamedValue
 */
class NamedValue extends NodeBase
{
    const DELIMITER_CODE = 3;

    /**
     * @param $strName
     */
    public function __construct($strName)
    {
        $this->strName = $strName;
    }

    /**
     * @param null $blnEqualityType
     * @return string
     */
    public function parameter($blnEqualityType = null)
    {
        if (is_null($blnEqualityType)) {
            return chr(NamedValue::DELIMITER_CODE) . '{' . $this->strName . '}';
        } else {
            if ($blnEqualityType) {
                return chr(NamedValue::DELIMITER_CODE) . '{=' . $this->strName . '=}';
            } else {
                return chr(NamedValue::DELIMITER_CODE) . '{!' . $this->strName . '!}';
            }
        }
    }

    /**
     * @param Builder $objBuilder
     * @param bool|false $blnExpandSelection
     * @param iCondition|null $objJoinCondition
     * @param Clause\Select|null $objSelect
     */
    public function join(
        Builder $objBuilder,
        $blnExpandSelection = false,
        iCondition $objJoinCondition = null,
        Clause\Select $objSelect = null
    ) {
        assert(0);    // This kind of node is never a parent.
    }
}
