<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Condition;

use QCubed\Exception\Caller;
use QCubed\Query\Builder;
use QCubed\Type;
use QCubed\Query\Node;


/**
 * Class In
 * Represent a test for an item being in a set of values.
 * @package QCubed\Query\Condition
 * @was QQConditionIn
 */
class In extends ComparisonBase
{
    /**
     * In constructor.
     * @param Node\Column $objQueryNode
     * @param mixed|null $mixValuesArray
     * @throws Caller
     */
    public function __construct(Node\Column $objQueryNode, $mixValuesArray)
    {
        parent::__construct($objQueryNode);

        if ($mixValuesArray instanceof Node\NamedValue) {
            $this->mixOperand = $mixValuesArray;
        } else {
            if ($mixValuesArray instanceof Node\SubQueryBase) {
                $this->mixOperand = $mixValuesArray;
            } else {
                try {
                    $this->mixOperand = Type::cast($mixValuesArray, Type::ARRAY_TYPE);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            }
        }
    }

    /**
     * @param Builder $objBuilder
     */
    public function updateQueryBuilder(Builder $objBuilder)
    {
        $mixOperand = $this->mixOperand;
        if ($mixOperand instanceof Node\NamedValue) {
            /** @var Node\NamedValue $mixOperand */
            $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' IN (' . $mixOperand->parameter() . ')');
        } else {
            if ($mixOperand instanceof Node\SubQueryBase) {
                /** @var Node\SubQueryBase $mixOperand */
                $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' IN ' . $mixOperand->getColumnAlias($objBuilder));
            } else {
                $strParameters = array();
                foreach ($mixOperand as $mixParameter) {
                    array_push($strParameters, $objBuilder->Database->sqlVariable($mixParameter));
                }
                if (count($strParameters)) {
                    $objBuilder->addWhereItem($this->objQueryNode->getColumnAlias($objBuilder) . ' IN (' . implode(',',
                            $strParameters) . ')');
                } else {
                    $objBuilder->addWhereItem('1=0');
                }
            }
        }
    }
}
