<?php
	/** @var QSqlTable[] $objTableArray */
	/** @var \QCubed\Codegen\DatabaseCodeGen $objCodeGen */
	global $_TEMPLATE_SETTINGS;
	$_TEMPLATE_SETTINGS = array(
		'OverwriteFlag' => true,
		'DocrootFlag' => false,
		'DirectorySuffix' => '',
		'TargetDirectory' => __MODEL_GEN__,
		'TargetFileName' => '_type_class_paths.inc.php'
	);
?>
<?php print("<?php\n"); ?>
$a = [];

<?php foreach ($objTableArray as $objTable) { ?>
// ClassPaths for the <?= $objTable->ClassName ?> type class
<?php if (__MODEL__) { ?>
$a['<?= strtolower($objTable->ClassName) ?>'] = __MODEL__ . '/<?= $objTable->ClassName ?>.class.php';
$a['node<?= strtolower($objTable->ClassName) ?>'] = __MODEL__ . '/<?= $objTable->ClassName ?>.class.php';<?php } ?>
<?php } ?>


return $a;
