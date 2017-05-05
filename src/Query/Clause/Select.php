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
use QCubed\Query\Node;

/**
 * Class Select
 * @package QCubed\Query\Clause
 * no was clause here! It has a name conflict
 */
class Select extends ObjectBase implements ClauseInterface
{
    /** @var Node\NodeBase[] */
    protected $arrNodeObj = array();
    protected $blnSkipPrimaryKey = false;

    /**
     * @param Node\NodeBase[] $arrNodeObj
     * @throws Caller
     */
    public function __construct($arrNodeObj)
    {
        $this->arrNodeObj = $arrNodeObj;
        foreach ($this->arrNodeObj as $objNode) {
            if (!($objNode instanceof Node\Column)) {
                throw new Caller('Select nodes must be column nodes.', 3);
            }
        }
    }

    public function updateQueryBuilder(Builder $objBuilder)
    {
    }

    public function addSelectItems(Builder $objBuilder, $strTableName, $strAliasPrefix)
    {
        foreach ($this->arrNodeObj as $objNode) {
            $strNodeTable = $objNode->getTable();
            if ($strNodeTable == $strTableName) {
                $objBuilder->addSelectItem($strTableName, $objNode->_Name, $strAliasPrefix . $objNode->_Name);
            }
        }
    }

    public function merge(Select $objSelect = null)
    {
        if ($objSelect) {
            foreach ($objSelect->arrNodeObj as $objNode) {
                array_push($this->arrNodeObj, $objNode);
            }
            if ($objSelect->blnSkipPrimaryKey) {
                $this->blnSkipPrimaryKey = true;
            }
        }
    }

    /**
     * @return boolean
     */
    public function skipPrimaryKey()
    {
        return $this->blnSkipPrimaryKey;
    }

    /**
     * @param boolean $blnSkipPrimaryKey
     */
    public function setSkipPrimaryKey($blnSkipPrimaryKey)
    {
        $this->blnSkipPrimaryKey = $blnSkipPrimaryKey;
    }

    public function __toString()
    {
        return 'QQSelectColumn Clause';
    }
}
