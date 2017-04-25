<?php
foreach ($objTable->ColumnArray as $objColumn) {
	$strVarNamePlural = strtolower($objTable->ClassNamePlural);
	$strVarName = strtolower($objTable->ClassName);
	if ($objColumn->Identity || $objColumn->Unique) {
?>

    /**
     *  Return an array of <?= $objTable->ClassNamePlural ?> keyed by the unique <?= $objColumn->PropertyName ?> property.
     *	@param <?= $objTable->ClassName ?>[]
     *	@return <?= $objTable->ClassName ?>[]
     **/
    public static function key<?= $objTable->ClassNamePlural ?>By<?= $objColumn->PropertyName ?>($<?= $strVarNamePlural ?>) {
        if (empty($<?= $strVarNamePlural ?>)) {
            return $<?= $strVarNamePlural ?>;
        }
        $ret = [];
        foreach ($<?= $strVarNamePlural ?> as $<?= $strVarName ?>) {
            $ret[$<?= $strVarName ?>-><?= $objColumn->VariableName ?>] = $<?= $strVarName ?>;
        }
        return $ret;
    }
<?php
	}
}
