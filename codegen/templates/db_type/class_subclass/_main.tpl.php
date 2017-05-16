<?php
	/** @var \QCubed\Codegen\TypeTable $objTypeTable */
	/** @var \QCubed\Codegen\DatabaseCodeGen $objCodeGen */
	global $_TEMPLATE_SETTINGS;
	$_TEMPLATE_SETTINGS = array(
		'OverwriteFlag' => false,
		'DirectorySuffix' => '',
		'TargetDirectory' => QCUBED_PROJECT_MODEL_DIR,
		'TargetFileName' => $objTypeTable->ClassName . '.php'
	);
?>
<?php print("<?php\n"); ?>
	require(QCUBED_PROJECT_MODEL_GEN_DIR . '/<?= $objTypeTable->ClassName ?>Gen.php');

	/**
	 * The <?= $objTypeTable->ClassName ?> class defined here contains any
	 * customized code for the <?= $objTypeTable->ClassName ?> enumerated type.
	 *
	 * It represents the enumerated values found in the "<?= $objTypeTable->Name ?>" table in the database,
	 * and extends from the code generated abstract <?= $objTypeTable->ClassName ?>Gen
	 * class, which contains all the values extracted from the database.
	 *
	 * Type classes which are generally used to attach a type to data object.
	 * However, they may be used as simple database indepedant enumerated type.
	 *
	 * @package <?= \QCubed\Project\Codegen\CodegenBase::$ApplicationName; ?>

	 * @subpackage DataObjects
	 */
	abstract class <?= $objTypeTable->ClassName ?> extends <?= $objTypeTable->ClassName ?>Gen {
	}