<?php
/**
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\Exception;

use QCubed\Translator;

/**
 * Class OptimisticLocking
 * Thrown when optimistic locking (in ORM Save() method) detects that DB data was updated
 * @package QCubed\Database\Exception
 */
class OptimisticLocking extends \QCubed\Exception\Caller {
	/**
	 * Constructor method
	 * @param string $strClass
	 */
	public function __construct($strClass) {
		parent::__construct(sprintf(Translator::translate('Optimistic Locking constraint when trying to update %s object.  To update anyway, call ->save() with $blnForceUpdate set to true'), $strClass, 2));
	}
}
