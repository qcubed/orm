<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Node;

use QCubed\Exception\Caller;
use QCubed\Query\Builder;
use QCubed\Query\QQ;

/**
 * Class Virtual
 * Class to represent a computed value or sub sql expression with an alias that can be used to query and sort
 *
 * @package QCubed\Query\Node
 * @was QQVirtualNode
 */
class Virtual extends NoParentBase
{
    protected $objSubQueryDefinition;

    /**
     * @param $strName
     * @param SubQueryBase|null $objSubQueryDefinition
     */
    public function __construct($strName, SubQueryBase $objSubQueryDefinition = null)
    {
        parent::__construct('', '', '');
        $this->objParentNode = true;
        $this->strName = QQ::getVirtualAlias($strName);
        $this->strAlias = $this->strName;
        $this->objSubQueryDefinition = $objSubQueryDefinition;
    }

    /**
     * @param Builder $objBuilder
     * @return string
     * @throws Caller
     */
    public function getColumnAlias(Builder $objBuilder)
    {
        if ($this->objSubQueryDefinition) {
            $objBuilder->setVirtualNode($this->strName, $this->objSubQueryDefinition);
            return $this->objSubQueryDefinition->getColumnAlias($objBuilder);
        } else {
            try {
                $objNode = $objBuilder->getVirtualNode($this->strName);
                return $objNode->getColumnAlias($objBuilder);
            } catch (Caller $objExc) {
                $objExc->incrementOffset();
                $objExc->incrementOffset();
                throw $objExc;
            }
        }
    }

    public function getAttributeName()
    {
        return $this->strName;
    }

    public function hasSubquery()
    {
        return $this->objSubQueryDefinition != null;
    }
}
