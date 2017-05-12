<?php
	$blnAutoInitialize = $objCodeGen->AutoInitialize;
	if ($blnAutoInitialize) {
?>

    /**
     * Construct a new <?= $objTable->ClassName ?> object.
     * @param bool $blnInitialize
     */
    public function __construct($blnInitialize = true)
    {
        if ($blnInitialize) {
            $this->Initialize();
        }
    }
<?php } ?>

    /**
     * Initialize each property with default values from database definition
     */
    public function initialize()
    {
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
<?php 	if ($objColumn->Identity ||
				$objColumn->Timestamp) {
			// do not initialize with a default value
	 	}
	 	else { ?>
        $this-><?= $objColumn->VariableName ?> = <?php
        $defaultVarName = $objTable->ClassName . '::' . strtoupper($objColumn->Name) . '_DEFAULT';
        if ($objColumn->VariableType != \QCubed\Type::DATE_TIME)
            print ($defaultVarName);
        else
            print "(" . $defaultVarName . " === null)?null:new QDateTime(" . $defaultVarName . ")";
        ?>;
        $this->__blnValid[self::<?= strtoupper($objColumn->Name) ?>_FIELD] = true;
<?php 	} ?>
<?php } ?>
    }

   /**
    *
    * @returns string
    */
    abstract function __toString();