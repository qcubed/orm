<?php

// Config file for travis build

if (!defined('SERVER_INSTANCE')) {
	// The Server Instance constant is used to help ease web applications with multiple environments.
	// Feel free to use, change or ignore.
	define('SERVER_INSTANCE', 'dev');
	define('ALLOW_REMOTE_ADMIN', true);

	// In the travis test build, the vendor directory is installed inside the application directory
    define ('QCUBED_BASE_DIR',  dirname(dirname(__DIR__)) . '/vendor/qcubed');

    // for travis build only, we point to the project directory inside the install directory
	define ('QCUBED_PROJECT_DIR', dirname(dirname(__DIR__)) . '/install/project');
    define ('QCUBED_PROJECT_GEN_DIR', QCUBED_PROJECT_DIR . '/generated');
    define ('QCUBED_PROJECT_INCLUDES_DIR', QCUBED_PROJECT_DIR . '/includes');

    define ('QCUBED_PROJECT_MODEL_DIR', QCUBED_PROJECT_INCLUDES_DIR . '/model' );
    define ('QCUBED_PROJECT_MODEL_GEN_DIR', QCUBED_PROJECT_GEN_DIR . '/model_base' );

    define ('QCUBED_APP_INCLUDES_DIR', QCUBED_PROJECT_DIR . '/includes/app_includes' );
    define ('QCUBED_ORM_TOOLS_DIR', QCUBED_BASE_DIR . '/orm/tools' );

	require_once (getenv("DB") . '.inc.php');

	define ('MAX_DB_CONNECTION_INDEX', 1);

	/** The value for QApplication::$EncodingType constant */
	define('QCUBED_ENCODING', 'UTF-8');

}
