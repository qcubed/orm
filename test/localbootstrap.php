<?php


// During development, these are not yet in the composer autoloader cache, so add them here
$strPackagePath =  dirname(__DIR__);
$loader = require dirname(dirname($strPackagePath)) . '/autoload.php'; // Add the Composer autoloader if using Composer

$loader->addPsr4('QCubed\\', $strPackagePath . '/src');
$loader->addPsr4('QCubed\\', $strPackagePath . '/../common/src');

$__CONFIG_ONLY__ = true;

include ($strPackagePath . '/tools/qcubed.inc.php');

include (__APP_INCLUDES__ . '/model_includes.php');

\QCubed\AutoloaderService::instance()
	->initialize(QCUBED_BASE_DIR . '/../autoload.php')
	->addClassmapFile(__MODEL_GEN__ . '/_class_paths.inc.php')
	->addClassmapFile(__MODEL_GEN__ . '/_type_class_paths.inc.php');


\QCubed\Database\Service::initializeDatabaseConnections();