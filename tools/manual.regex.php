<?php
/**
 * Manually edited replacements
 */

$a['regex']['QApplication::\\$Database\\s*\\[(\\S+?)\\]'] = '\\QCubed\\Database\\Service::getDatabase($1)';

$a['warn']['QQClause'] = "QQClause cannot be automatically fixed. Should extend ObjectBase, and implement ClauseInterface";

return $a;