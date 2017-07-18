<?php

/**
 * The base configuration file for the travis test. This is set as the bootstrap file in the phpunit.xml file.
 */
$workingDir = getcwd();
define('__WORKING_DIR__', $workingDir);

// Configure
require( __WORKING_DIR__ . '/test/travis/configuration.inc.php');

define ('QCUBED_CONFIG_DIR', __WORKING_DIR__ . '/test/travis');

\QCubed\AutoloaderService::instance()
	->initialize('./vendor')
	->addPsr4('QCubed\\', __WORKING_DIR__ . '/src');

// Codegen
require(QCUBED_CONFIG_DIR . '/CodegenBase.php');
require( QCUBED_ORM_TOOLS_DIR . '/codegen.cli.php');

// i18n is not required by the actual ORM library, but it is required by the generated type table files in order to pass unit testing
require(__WORKING_DIR__ . '/vendor/qcubed/i18n/tools/i18n-app.inc.php'); // Include the translation shortcuts. See the Application for translation setup.



\QCubed\AutoloaderService::instance()
	->addClassmapFile(QCUBED_PROJECT_MODEL_GEN_DIR . '/_class_paths.inc.php')
	->addClassmapFile(QCUBED_PROJECT_MODEL_GEN_DIR . '/_type_class_paths.inc.php');
include (QCUBED_PROJECT_MODEL_GEN_DIR . '/QQN.php');
