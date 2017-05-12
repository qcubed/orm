<?php if ($objTable->PrimaryKeyColumnArray)  {
if (count($objTable->PrimaryKeyColumnArray) == 1) {
    $pkType = $objTable->PrimaryKeyColumnArray[0]->VariableType;
} else {
    $pkType = 'string';	// combined pk
}

?>
<?php 	if (count ($objTable->PrimaryKeyColumnArray) > 1) { ?>
    /**
    * Convert the composite key to a single unique value suitable for use in caching. Override to provide
    * a more suitable method of combining the keys if necessary.
    * @var mixed[] array of values to use as the key.
    * @return string
    */

    protected static function makeMultiKey($keyValues)
    {
        return implode (':', $keyValues);
    }
<?php 	} ?>

    /**
     * Returns a single unique value representing the primary key.
     * @return <?= $pkType ?>

     */
    public function primaryKey()
    {
<?php 	if (count ($objTable->PrimaryKeyColumnArray) == 1) { ?>
        return $this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>;
<?php 	} else {
        $aItems = array();
        foreach ($objTable->PrimaryKeyColumnArray as $objPKColumn) {
            $aItems[] = '$this->' . $objPKColumn->VariableName;
        }
?>
        return static::makeMultiKey (array(<?= implode (', ', $aItems) ?>));
<?php 	} ?>
    }

    /**
    * Returns the primary key directly from a database row.
    * @param \QCubed\Database\RowBase $objDbRow
    * @param string $strAliasPrefix
    * @param string[] $strColumnAliasArray
    * @return <?= $pkType ?>

    **/
    protected static function getRowPrimaryKey(\QCubed\Database\RowBase $objDbRow, $strAliasPrefix, $strColumnAliasArray)
    {
<?php 	if (count ($objTable->PrimaryKeyColumnArray) == 1) { ?>
        $strAlias = $strAliasPrefix . '<?= $objTable->PrimaryKeyColumnArray[0]->Name ?>';
        $strAliasName = !empty($strColumnAliasArray[$strAlias]) ? $strColumnAliasArray[$strAlias] : $strAlias;
        $strColumns = $objDbRow->GetColumnNameArray();
        $mixVal = (isset ($strColumns[$strAliasName]) ? $strColumns[$strAliasName] : null);
        <?php if ($s = \QCubed\Codegen\DatabaseCodeGen::GetCastString($objTable->PrimaryKeyColumnArray[0]))	echo $s; ?>

        return $mixVal;
<?php 	} else { ?>
        $strColumns = $objDbRow->GetColumnNameArray();
<?php 		foreach ($objTable->PrimaryKeyColumnArray as $objPKColumn) {?>
        $strAlias = $strAliasPrefix . '<?= $objPKColumn->Name ?>';
        $strAliasName = !empty($strColumnAliasArray[$strAlias]) ? $strColumnAliasArray[$strAlias] : $strAlias;
        $mixVal = (isset ($strColumns[$strAliasName]) ? $strColumns[$strAliasName] : null);
        if ($mixVal === null) return null;
<?php 			if ($s = \QCubed\Codegen\DatabaseCodeGen::GetCastString($objPKColumn))	echo $s; ?>
        $values[] = $mixVal;
<?php 		} ?>

        return static::MakeMultiKey ($values);
<?php 	} ?>
    }
<?php } else { ?>
   /**
    * @return null
    */
    protected function primaryKey()
    {
        return null;
    }

   /**
    * @return null
    */
    protected static function getRowPrimaryKey($objDbRow, $strAliasPrefix, $strColumnAliasArray)
    {
        return null;
    }
<?php }