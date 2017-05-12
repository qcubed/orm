    ///////////////////////////////
    // CLASS-WIDE LOAD AND COUNT METHODS
    ///////////////////////////////

    /**
     * Static method to retrieve the Database object that owns this class.
     * @return \QCubed\Database\DatabaseBase reference to the Database object that can query this class
     */
    public static function getDatabase()
    {
        return \QCubed\Database\Service::getDatabase(self::getDatabaseIndex());
    }

    /**
     * Load a <?= $objTable->ClassName ?> from PK Info
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
<?php if ($objColumn->PrimaryKey) { ?>
     * @param <?= $objColumn->VariableType ?> $<?= $objColumn->VariableName ?>
<?php } ?>
<?php } ?>

     * @param iClause[] $objOptionalClauses additional optional iClause objects for this query
     * @return <?= $objTable->ClassName ?>

     */
    public static function load(<?= $objCodeGen->ParameterListFromColumnArray($objTable->PrimaryKeyColumnArray); ?>, $objOptionalClauses = null)
    {
        if (!$objOptionalClauses) {
<?php if (count ($objTable->PrimaryKeyColumnArray) == 1) { ?>
            $objCachedObject = static::getFromCache ($<?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>);
<?php } else {
$aItems = array();
foreach ($objTable->PrimaryKeyColumnArray as $objColumn) {
    $aItems[] = '$' . $objColumn->VariableName;
}
?>
            $strCacheKey = static::makeMultiKey (array(<?= implode (', ', $aItems) ?>));
            $objCachedObject = static::getFromCache ($strCacheKey);
<?php } ?>
            if ($objCachedObject) return $objCachedObject;
        }

        // Use QuerySingle to Perform the Query
        $objToReturn = <?= $objTable->ClassName ?>::querySingle(
            QQ::AndCondition(
<?php foreach ($objTable->PrimaryKeyColumnArray as $objColumn) { ?>
                QQ::Equal(QQN::<?= $objTable->ClassName ?>()-><?= $objColumn->PropertyName ?>, $<?= $objColumn->VariableName ?>),
<?php } ?><?php GO_BACK(2); ?>

            ),
            $objOptionalClauses
        );
        return $objToReturn;
    }


    /**
     * Load all <?= $objTable->ClassNamePlural ?>

     * @param iClause[]|null $objOptionalClauses additional optional iClause objects for this query
     * @throws Caller
     * @return <?= $objTable->ClassName ?>[]
     * @throws Caller
     */
    public static function loadAll($objOptionalClauses = null)
    {
        if (func_num_args() > 1) {
            throw new Caller("LoadAll must be called with an array of optional clauses as a single argument");
        }
        // Call <?= $objTable->ClassName ?>::queryArray to perform the LoadAll query
        try {
            return <?= $objTable->ClassName; ?>::queryArray(QQ::All(), $objOptionalClauses);
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

    /**
     * Count all <?= $objTable->ClassNamePlural ?>

     * @return int
     */
    public static function countAll()
    {
        // Call <?= $objTable->ClassName ?>::queryCount to perform the CountAll query
        return <?= $objTable->ClassName ?>::queryCount(QQ::All());
    }
