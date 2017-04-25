<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Clause;

use QCubed\AbstractBase;
use QCubed\Query\Builder;
use QCubed\Query\Node\AbstractSubQuery;

/**
 * Class Having
 * Allows a custom sql injection as a having clause. Its up to you to make sure its correct, but you can use subquery placeholders
 * to expand column names. Standard SQL has limited Having capabilities, but many SQL engines have useful extensions.
 * @package QCubed\Query\Clause
 * @was QQHavingClause
 */
class Having extends AbstractBase implements ClauseInterface {
	protected $objNode;
	public function __construct(AbstractSubQuery $objSubQueryDefinition) {
		$this->objNode = $objSubQueryDefinition;
	}
	public function UpdateQueryBuilder(Builder $objBuilder) {
		$objBuilder->AddHavingItem (
			$this->objNode->GetColumnAlias($objBuilder)
		);
	}
	public function GetAttributeName() {
		return $this->objNode->_Name;
	}
	public function __toString() {
		return "Having Clause";
	}

}

