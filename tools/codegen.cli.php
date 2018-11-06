<?php

use QCubed\Project\Codegen\CodegenBase as Codegen;

/* This includes library file is used by the codegen.cli and codegen.phpexe scripts
 * to simply fire up and run the Codegen object, itself.
 *
 * Depends on QCUBED_PROJECT_INCLUDES_DIR and QCUBED_CONFIG_DIR defines
 */


$strOrmPath = dirname(__DIR__);
$strQCubedPath = dirname($strOrmPath);

function PrintInstructions() {
		global $strCommandName;
		print('QCubed Code Generator (Command Line Interface) 
Copyright (c) 2001 - 2009, QuasIdea Development, LLC, QCubed Project
This program is free software with ABSOLUTELY NO WARRANTY; you may
redistribute it under the terms of The MIT License.

Usage: ' . $strCommandName . ' CODEGEN_SETTINGS

Where CODEGEN_SETTINGS is the absolute filepath of the codegen_settings.xml
file, containing the code generator settings.

For more information, please go to http://qcu.be
');
	exit();
}

\QCubed\Database\Service::InitializeDatabaseConnections();

$settingsFile = QCUBED_CONFIG_DIR . '/codegen_settings.xml';

if (!is_file($settingsFile)) {
	echo "Settings file: " . $settingsFile;
	PrintInstructions();
}

/////////////////////
// Run Code Gen
Codegen::run($settingsFile);
/////////////////////


if ($strErrors = Codegen::$RootErrors) {
	printf("The following ROOT ERRORS were reported:\r\n%s\r\n\r\n", $strErrors);
} else {
	printf("Codegen settings (as evaluted from %s):\r\n%s\r\n\r\n", $settingsFile, Codegen::GetSettingsXml());
}

print ("Template files:\r\n");
$strFiles = Codegen::$TemplatePaths;
echo implode("\r\n", $strFiles);


foreach (Codegen::$CodegenArray as $objCodegen) {
	printf("%s\r\n---------------------------------------------------------------------\r\n", $objCodegen->GetTitle());
	printf("%s\r\n", $objCodegen->GetReportLabel());
	printf("%s\r\n", $objCodegen->GenerateAll());
	if ($strErrors = $objCodegen->Errors)
		printf("The following errors were reported:\r\n%s\r\n", $strErrors);
	print("\r\n");
}

foreach (Codegen::GenerateAggregate() as $strMessage) {
	printf("%s\r\n\r\n", $strMessage);
}