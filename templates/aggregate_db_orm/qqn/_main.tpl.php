<?php
	/** @var QSqlTable[] $objTableArray */
	/** @var \QCubed\Codegen\DatabaseCodeGen $objCodeGen */
	global $_TEMPLATE_SETTINGS;
	$_TEMPLATE_SETTINGS = array(
		'OverwriteFlag' => true,
		'DocrootFlag' => false,
		'DirectorySuffix' => '',
		'TargetDirectory' => __MODEL_GEN__,
		'TargetFileName' => 'QQN.class.php'
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
		static public function <?= $objTable->ClassName ?>() {
			return new Node<?= $objTable->ClassName ?>('<?= $objTable->Name ?>', null, null);
		}
<?php } ?>
	}