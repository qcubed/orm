<?php
	/** @var QSqlTable[] $objTableArray */
	/** @var \QCubed\Codegen\DatabaseCodeGen $objCodeGen */
	global $_TEMPLATE_SETTINGS;
	$_TEMPLATE_SETTINGS = array(
		'OverwriteFlag' => true,
		'DirectorySuffix' => '',
		'TargetDirectory' => QCUBED_PROJECT_MODEL_GEN_DIR,
		'TargetFileName' => '_class_paths.inc.php'
	);
?>
<?php print("<?php\n"); ?>
$a = [];

<?php foreach ($objTableArray as $objTable) { ?>
// ClassPaths for the <?= $objTable->ClassName ?> class
<?php if (defined('QCUBED_PROJECT_MODEL_DIR')) { ?>
$a['<?= strtolower($objTable->ClassName) ?>'] = QCUBED_PROJECT_MODEL_DIR . '/<?= $objTable->ClassName ?>.php';
$a['node<?= strtolower($objTable->ClassName) ?>'] = QCUBED_PROJECT_MODEL_DIR . '/<?= $objTable->ClassName ?>.php';
$a['reversereferencenode<?= strtolower($objTable->ClassName) ?>'] = QCUBED_PROJECT_MODEL_DIR . '/<?= $objTable->ClassName ?>.php';
<?php foreach ($objTable->ManyToManyReferenceArray as $objReference) { ?>
$a['node<?= strtolower($objTable->ClassName) ?><?= strtolower($objReference->ObjectDescription) ?>'] = QCUBED_PROJECT_MODEL_DIR . '/<?= $objTable->ClassName ?>.php';
<?php } ?>
<?php } ?><?php if (defined('QCUBED_PROJECT_MODELCONNECTOR_DIR')) { ?>
$a['<?= strtolower($objTable->ClassName) ?>connector'] = QCUBED_PROJECT_MODELCONNECTOR_DIR . '/<?= $objTable->ClassName ?>Connector.php';
$a['<?= strtolower($objTable->ClassName) ?>list'] = QCUBED_PROJECT_MODELCONNECTOR_DIR . '/<?= $objTable->ClassName ?>List.php';
<?php } ?>

<?php } ?>


return $a;
