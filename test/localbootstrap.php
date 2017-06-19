<?php


// During development, these are not yet in the composer autoloader cache, so add them here
$strPackagePath =  dirname(__DIR__);
$loader = require dirname(dirname($strPackagePath)) . '/autoload.php'; // Add the Composer autoloader if using Composer

$loader->addPsr4('QCubed\\', $strPackagePath . '/src');
$loader->addPsr4('QCubed\\', $strPackagePath . '/../common/src');

$__CONFIG_ONLY__ = true;

include ($strPackagePath . '/tools/qcubed.inc.php');

//include ($strPackagePath . '/src/model_includes.inc.php');

\QCubed\AutoloaderService::instance()
	->initialize(dirname(QCUBED_BASE_DIR))
	->addClassmapFile(QCUBED_PROJECT_MODEL_GEN_DIR . '/_class_paths.inc.php')
	->addClassmapFile(QCUBED_PROJECT_MODEL_GEN_DIR . '/_type_class_paths.inc.php');

require_once(QCUBED_PROJECT_MODEL_GEN_DIR . '/QQN.php');


\QCubed\Database\Service::initializeDatabaseConnections();