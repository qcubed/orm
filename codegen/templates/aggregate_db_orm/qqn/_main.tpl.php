<?php
	/** @var QSqlTable[] $objTableArray */
	/** @var \QCubed\Codegen\DatabaseCodeGen $objCodeGen */
	global $_TEMPLATE_SETTINGS;
	$_TEMPLATE_SETTINGS = array(
		'OverwriteFlag' => true,
		'DirectorySuffix' => '',
		'TargetDirectory' => QCUBED_PROJECT_MODEL_GEN_DIR,
		'TargetFileName' => 'QQN.php'
	);
?>
<?php print("<?php\n"); ?>
    /**
     * Class QQN
     * Factory methods for generating database nodes at the top of a node chain.
     */
	class QQN {
<?php foreach ($objTableArray as $objTable) { ?>
		/**
		 * @return Node<?= $objTable->ClassName ?>

		 */
		static public function <?= lcfirst($objTable->ClassName) ?>() {
			return new Node<?= $objTable->ClassName ?>('<?= $objTable->Name ?>', null, null);
		}
<?php } ?>
	}