<?php
/**
 * Manually edited replacements
 */

$a['regex']['QApplication::\\$Database\\s*\\[(\\S+?)\\]'] = '\\QCubed\\Database\\Service::getDatabase($1)';

return $a;