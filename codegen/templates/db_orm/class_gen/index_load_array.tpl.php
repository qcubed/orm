<?php $objColumnArray = $objCodegen->GetColumnArray($objTable, $objIndex->ColumnNameArray); ?>
    /**
     * Load an array of <?= $objTable->ClassName ?> objects,
     * by <?= $objCodegen->ImplodeObjectArray(', ', '', '', 'PropertyName', $objCodegen->GetColumnArray($objTable, $objIndex->ColumnNameArray)) ?> Index(es)
<?php foreach ($objColumnArray as $objColumn) { ?>
     * @param <?= $objColumn->VariableType ?> $<?= $objColumn->VariableName ?>

<?php } ?>
     * @param iClause[] $objOptionalClauses additional optional iClause objects for this query
     * @throws Caller
     * @return <?= $objTable->ClassName ?>[]
    */
    public static function loadArrayBy<?= $objCodegen->ImplodeObjectArray('', '', '', 'PropertyName', $objColumnArray); ?>(<?= $objCodegen->ParameterListFromColumnArray($objColumnArray); ?>, $objOptionalClauses = null)
    {
        // Call <?= $objTable->ClassName ?>::QueryArray to perform the LoadArrayBy<?= $objCodegen->ImplodeObjectArray('', '', '', 'PropertyName', $objColumnArray); ?> query
        try {
            return <?= $objTable->ClassName; ?>::QueryArray(
<?php if (count($objColumnArray) > 1) { ?>
                QQ::AndCondition(
<?php } ?>
<?php foreach ($objColumnArray as $objColumn) { ?>
                QQ::Equal(QQN::<?= $objTable->ClassName ?>()-><?= $objColumn->PropertyName ?>, $<?= $objColumn->VariableName ?>),
<?php } ?><?php GO_BACK(2); ?>
<?php if (count($objColumnArray) > 1) { ?>
                )
<?php } ?>,
                $objOptionalClauses);
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

    /**
     * Count <?= $objTable->ClassNamePlural ?>

     * by <?= $objCodegen->ImplodeObjectArray(', ', '', '', 'PropertyName', $objCodegen->GetColumnArray($objTable, $objIndex->ColumnNameArray)) ?> Index(es)
<?php foreach ($objColumnArray as $objColumn) { ?>
     * @param <?= $objColumn->VariableType ?> $<?= $objColumn->VariableName ?>

<?php } ?>
     * @return int
    */
    public static function countBy<?= $objCodegen->ImplodeObjectArray('', '', '', 'PropertyName', $objColumnArray); ?>(<?= $objCodegen->ParameterListFromColumnArray($objColumnArray); ?>)
    {
        // Call <?= $objTable->ClassName ?>::QueryCount to perform the CountBy<?= $objCodegen->ImplodeObjectArray('', '', '', 'PropertyName', $objColumnArray); ?> query
        return <?= $objTable->ClassName ?>::QueryCount(
<?php if (count($objColumnArray) > 1) { ?>
            QQ::AndCondition(
<?php } ?>
<?php foreach ($objColumnArray as $objColumn) { ?>
            QQ::Equal(QQN::<?= $objTable->ClassName ?>()-><?= $objColumn->PropertyName ?>, $<?= $objColumn->VariableName ?>),
<?php } ?><?php GO_BACK(2); ?>
<?php if (count($objColumnArray) > 1) { ?>
            )
<?php } ?>

        );
    }