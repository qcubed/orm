<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Query\Clause;

/**
 * Class Minimum
 * @package QCubed\Query\Clause
 * @was QQMinimum
 */
class Minimum extends AbstractAggregation {
	protected $strFunctionName = 'MIN';
	public function __toString() {
		return 'Minimum Clause';
	}
}
