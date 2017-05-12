    /**
     * Load an array of <?= $objManyToManyReference->VariableType ?> objects for a given <?= $objManyToManyReference->ObjectDescription ?>

     * via the <?= $objManyToManyReference->Table ?> table
     * @param <?= $objManyToManyReference->OppositeVariableType ?> $<?= $objManyToManyReference->OppositeVariableName ?>

     * @param iClause[] $objClauses additional optional iClause objects for this query
     * @throws Caller
     * @return <?= $objTable->ClassName ?>[]
    */
    public static function loadArrayBy<?= $objManyToManyReference->ObjectDescription ?>($<?= $objManyToManyReference->OppositeVariableName ?>, $objClauses = null)
    {
        // Call <?= $objTable->ClassName ?>::QueryArray to perform the LoadArrayBy<?= $objManyToManyReference->ObjectDescription ?> query
        try {
            return <?= $objTable->ClassName; ?>::QueryArray(
                QQ::Equal(QQN::<?= $objTable->ClassName ?>()-><?= $objManyToManyReference->ObjectDescription ?>-><?= $objManyToManyReference->OppositePropertyName ?>, $<?= $objManyToManyReference->OppositeVariableName ?>),
                $objClauses
            );
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

    /**
     * Count <?= $objTable->ClassNamePlural ?> for a given <?= $objManyToManyReference->ObjectDescription ?>

     * via the <?= $objManyToManyReference->Table ?> table
     * @param <?= $objManyToManyReference->OppositeVariableType ?> $<?= $objManyToManyReference->OppositeVariableName ?>

     * @return int
    */
    public static function countBy<?= $objManyToManyReference->ObjectDescription ?>($<?= $objManyToManyReference->OppositeVariableName ?>)
    {
        return <?= $objTable->ClassName ?>::QueryCount(
            QQ::Equal(QQN::<?= $objTable->ClassName ?>()-><?= $objManyToManyReference->ObjectDescription ?>-><?= $objManyToManyReference->OppositePropertyName ?>, $<?= $objManyToManyReference->OppositeVariableName ?>)
        );
    }