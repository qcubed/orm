<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Mysqli5;

/**
 * Class QMySqli5DatabaseResult
 * @package QCubed\Database\Mysqli5
 * @was QMySqli5DatabaseResult
 */
class Result extends MysqliResult
{
    public function fetchFields()
    {
        $objArrayToReturn = array();
        while ($objField = $this->objMySqliResult->fetch_field()) {
            array_push($objArrayToReturn, new Field($objField, $this->objDb));
        }
        return $objArrayToReturn;
    }

    public function fetchField()
    {
        if ($objField = $this->objMySqliResult->fetch_field()) {
            return new Field($objField, $this->objDb);
        }
        return null;
    }
}
