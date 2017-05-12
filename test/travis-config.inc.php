<?php

/**
 * The base configuration file for the travis test. This is set as the bootstrap file in the phpunit.xml file.
 */
$workingDir = getcwd();
define('__WORKING_DIR__', $workingDir);

// Configure
require( __WORKING_DIR__ . '/test/travis/configuration.inc.php');
define ('__CONFIGURATION__', __WORKING_DIR__ . '/test/travis');

\QCubed\AutoloaderService::instance()
	->initialize('./vendor/autoload.php')
	->addPsr4('QCubed\\', __WORKING_DIR__ . '/src');

// Codegen
require(__CONFIGURATION__ . '/CodegenBase.php');
require( __DOCROOT__ . __SUBDIRECTORY__ . '/tools/codegen.cli.php');

// Load up generated classes
include ($strPackagePath . '/src/model_includes.inc.php');

\QCubed\AutoloaderService::instance()
	->addClassmapFile(__MODEL_GEN__ . '/_class_paths.inc.php')
	->addClassmapFile(__MODEL_GEN__ . '/_type_class_paths.inc.php');
