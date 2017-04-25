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

/**
 * Class Distinct
 * @package QCubed\Query\Clause
 * @was QQDistinct
 */
class Distinct extends AbstractBase implements ClauseInterface {
	public function UpdateQueryBuilder(Builder $objBuilder) {
		$objBuilder->SetDistinctFlag();
	}
	public function __toString() {
		return 'QQDistinct Clause';
	}
}

