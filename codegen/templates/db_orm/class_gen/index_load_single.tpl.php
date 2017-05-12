<?php $objColumnArray = $objCodeGen->GetColumnArray($objTable, $objIndex->ColumnNameArray); ?>
    /**
     * Load a single <?= $objTable->ClassName ?> object,
     * by <?= $objCodeGen->ImplodeObjectArray(', ', '', '', 'PropertyName', $objCodeGen->GetColumnArray($objTable, $objIndex->ColumnNameArray)) ?> Index(es)
<?php foreach ($objColumnArray as $objColumn) { ?>
     * @param <?= $objColumn->VariableType ?> $<?= $objColumn->VariableName ?>

<?php } ?>
     * @param iClause[] $objOptionalClauses additional optional iClause objects for this query
     * @return <?= $objTable->ClassName ?>

    */
    public static function loadBy<?= $objCodeGen->ImplodeObjectArray('', '', '', 'PropertyName', $objColumnArray); ?>(<?= $objCodeGen->ParameterListFromColumnArray($objColumnArray); ?>, $objOptionalClauses = null)
    {
        return <?= $objTable->ClassName ?>::QuerySingle(
            QQ::AndCondition(
<?php foreach ($objColumnArray as $objColumn) { ?>
                QQ::Equal(QQN::<?= $objTable->ClassName ?>()-><?= $objColumn->PropertyName ?>, $<?= $objColumn->VariableName ?>),
<?php } ?><?php GO_BACK(2); ?>

            ),
            $objOptionalClauses
        );
    }