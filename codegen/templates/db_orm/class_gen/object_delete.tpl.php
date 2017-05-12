/**
     * Delete this <?= $objTable->ClassName ?>

     * @throws \QCubed\Database\Exception\UndefinedPrimaryKey
     * @return void
     */
    public function delete()
    {
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Cannot delete this <?= $objTable->ClassName ?> with an unset primary key.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::GetDatabase();

<?php foreach ($objTable->ReverseReferenceArray as $objReverseReference) { ?>
<?php if ($objReverseReference->Unique) { ?>
<?php if (!$objReverseReference->NotNull) { ?>
<?php $objReverseReferenceTable = $objCodeGen->TableArray[strtolower($objReverseReference->Table)]; ?>
<?php $objReverseReferenceColumn = $objReverseReferenceTable->ColumnArray[strtolower($objReverseReference->Column)]; ?>


        // Update the adjoined <?= $objReverseReference->ObjectDescription ?> object (if applicable) and perform the unassociation

        // Optional -- if you **KNOW** that you do not want to EVER run any level of business logic on the disassociation,
        // you *could* override Delete() so that this step can be a single hard coded query to optimize performance.
        if ($objAssociated = <?= $objReverseReference->VariableType ?>::LoadBy<?= $objReverseReferenceColumn->PropertyName ?>(<?= $objCodeGen->ImplodeObjectArray(', ', '$this->', '', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)) {
            $objAssociated-><?= $objReverseReferenceColumn->PropertyName ?> = null;
            $objAssociated->Save();
        }
<?php } ?><?php if ($objReverseReference->NotNull) { ?>
<?php $objReverseReferenceTable = $objCodeGen->TableArray[strtolower($objReverseReference->Table)]; ?>
<?php $objReverseReferenceColumn = $objReverseReferenceTable->ColumnArray[strtolower($objReverseReference->Column)]; ?>


        // Update the adjoined <?= $objReverseReference->ObjectDescription ?> object (if applicable) and perform a delete

        // Optional -- if you **KNOW** that you do not want to EVER run any level of business logic on the disassociation,
        // you *could* override Delete() so that this step can be a single hard coded query to optimize performance.
        if ($objAssociated = <?= $objReverseReference->VariableType ?>::LoadBy<?= $objReverseReferenceColumn->PropertyName ?>(<?= $objCodeGen->ImplodeObjectArray(', ', '$this->', '', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)) {
            $objAssociated->Delete();
        }
<?php } ?>
<?php } ?>
<?php } ?>

        // Perform the SQL Query
        $objDatabase->NonQuery('
            DELETE FROM
                <?= $strEscapeIdentifierBegin ?><?= $objTable->Name ?><?= $strEscapeIdentifierEnd ?>

            WHERE
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
<?php if ($objColumn->PrimaryKey) { ?>
                <?= $strEscapeIdentifierBegin ?><?= $objColumn->Name ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->SqlVariable($this-><?= $objColumn->VariableName ?>) . ' AND
<?php } ?>
<?php } ?><?php GO_BACK(5); ?>');

        $this->DeleteFromCache();
        static::BroadcastDelete($this->PrimaryKey());
    }

    /**
     * Delete all <?= $objTable->ClassNamePlural ?>

     * @return void
     */
    public static function deleteAll()
    {
        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::GetDatabase();

        // Perform the Query
        $objDatabase->NonQuery('
            DELETE FROM
                <?= $strEscapeIdentifierBegin ?><?= $objTable->Name ?><?= $strEscapeIdentifierEnd ?>');

        static::ClearCache();
        static::BroadcastDeleteAll();
    }

    /**
     * Truncate <?= $objTable->Name ?> table
     * @return void
     */
    public static function truncate()
    {
        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::GetDatabase();

        // Perform the Query
        $objDatabase->NonQuery('
            TRUNCATE <?= $strEscapeIdentifierBegin ?><?= $objTable->Name ?><?= $strEscapeIdentifierEnd ?>');

        static::ClearCache();
        static::BroadcastDeleteAll();
    }