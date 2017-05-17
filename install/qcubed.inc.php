<?php

/**
 * This default qcubed.inc.php file loads the correct configuration file. It is the top level qcubed.inc.php file, and many
 * parts of the framework search for this file, so don't move it. However, if you move the project directory, you should move the
 * pointer that is here to point to that new location.
 */
$configPath = __DIR__ . "/project/includes/configuration";

if (!defined ('__PREPEND_INCLUDED__')) {	// not already included some other way (like with .htaccess file)
    if (isset($__CONFIG_ONLY__) && $__CONFIG_ONLY__ == true) {
        if (file_exists($configPath . '/configuration.inc.php')) {
            require_once($configPath . '/configuration.inc.php');
        }
    } else {
        if (file_exists($configPath . '/prepend.inc.php')) {
            require_once($configPath . '/prepend.inc.php');
        }
    }
}
