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
use QCubed\Exception\Caller;
use QCubed\Query\Builder;
use QCubed\Type;

/**
 * Class Limit
 * @package QCubed\Query\Clause
 * @was QQLimitInfo
 */
class Limit extends ObjectBase implements ClauseInterface
{
    protected $intMaxRowCount;
    protected $intOffset;

    public function __construct($intMaxRowCount, $intOffset = 0)
    {
        try {
            $this->intMaxRowCount = Type::cast($intMaxRowCount, Type::INTEGER);
            $this->intOffset = Type::cast($intOffset, Type::INTEGER);
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

    public function updateQueryBuilder(Builder $objBuilder)
    {
        if ($this->intOffset) {
            $objBuilder->setLimitInfo($this->intOffset . ',' . $this->intMaxRowCount);
        } else {
            $objBuilder->setLimitInfo($this->intMaxRowCount);
        }
    }

    public function __toString()
    {
        return 'QQLimitInfo Clause';
    }

    public function __get($strName)
    {
        switch ($strName) {
            case 'MaxRowCount':
                return $this->intMaxRowCount;
            case 'Offset':
                return $this->intOffset;
            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }
}
