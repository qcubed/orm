<?php
	/** @var QSqlTable[] $objTableArray */
	/** @var \QCubed\Codegen\DatabaseCodeGen $objCodeGen */
	global $_TEMPLATE_SETTINGS;
	$_TEMPLATE_SETTINGS = array(
		'OverwriteFlag' => true,
		'DirectorySuffix' => '',
		'TargetDirectory' => QCUBED_PROJECT_MODEL_GEN_DIR,
		'TargetFileName' => '_type_class_paths.inc.php'
	);
?>
<?php print("<?php\n"); ?>
$a = [];

<?php foreach ($objTableArray as $objTable) { ?>
// ClassPaths for the <?= $objTable->ClassName ?> type class
<?php if (QCUBED_PROJECT_MODEL_DIR) { ?>
$a['<?= strtolower($objTable->ClassName) ?>'] = QCUBED_PROJECT_MODEL_DIR . '/<?= $objTable->ClassName ?>.php';
$a['node<?= strtolower($objTable->ClassName) ?>'] = QCUBED_PROJECT_MODEL_DIR . '/<?= $objTable->ClassName ?>.php';<?php } ?>
<?php } ?>


return $a;
