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


\QCubed\AutoloaderService::instance()
	->addClassmapFile(QCUBED_PROJECT_MODEL_GEN_DIR . '/_class_paths.inc.php')
	->addClassmapFile(QCUBED_PROJECT_MODEL_GEN_DIR . '/_type_class_paths.inc.php');
include (QCUBED_PROJECT_MODEL_GEN_DIR . '/QQN.php');
