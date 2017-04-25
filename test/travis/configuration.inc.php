<?php

// Config file for travis build

if (!defined('SERVER_INSTANCE')) {
	// The Server Instance constant is used to help ease web applications with multiple environments.
	// Feel free to use, change or ignore.
	define('SERVER_INSTANCE', 'dev');

	define('ALLOW_REMOTE_ADMIN', true);

	define ('__DOCROOT__', __WORKING_DIR__);
	define ('__VIRTUAL_DIRECTORY__', '');
	if (!defined ('__SUBDIRECTORY__')) {
		define ('__SUBDIRECTORY__', '');
	}

	// for travis build only, we point to the project directory inside the install directory
	define ('__PROJECT__', __DOCROOT__ . __SUBDIRECTORY__ . '/install/project');
	define ('__INCLUDES__', __PROJECT__ . '/includes');

	// The application includes directory
	define ('__APP_INCLUDES__', __INCLUDES__ . '/app_includes');


	// The QCubed Core
	define ('__QCUBED_CORE__', __DOCROOT__ . __SUBDIRECTORY__ . '/includes');

	// Destination for Code Generated class files
	define ('__MODEL__', __INCLUDES__ . '/model' );
	define ('__MODEL_GEN__', __PROJECT__ . '/generated/model_base' );

	require_once (getenv("DB") . '.inc.php');

	define ('MAX_DB_CONNECTION_INDEX', 1);

	/** The value for QApplication::$EncodingType constant */
	define('__APPLICATION_ENCODING_TYPE__', 'UTF-8');

}
