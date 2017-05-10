<?php
/**
 * Common functions that are used by the templates, loaded in to the root namespace.
 */

function GO_BACK($intNumChars) {
	$content_so_far = ob_get_contents();
	ob_end_clean();
	$content_so_far = substr($content_so_far, 0, strlen($content_so_far) - $intNumChars);
	ob_start();
	print $content_so_far;
}

/**
 * For indenting generated code.
 *
 * @param string $strText
 * @param integer $intCount	The number of indents to add
 * @return string
 */
function _indent_($strText, $intCount = 1) {
	$strRepeat = '    ';
	$strTabs = str_repeat($strRepeat, $intCount);
	$strRet = preg_replace ( '/^/m', $strTabs , $strText);
	return $strRet;
}
