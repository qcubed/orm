<?php $objReverseReferenceTable = $objCodeGen->TableArray[strtolower($objReverseReference->Table)]; ?>
<?php $objReverseReferenceColumn = $objReverseReferenceTable->ColumnArray[strtolower($objReverseReference->Column)]; ?>


    // Related Objects' Methods for <?= $objReverseReference->ObjectDescription ?>

    //-------------------------------------------------------------------

    /**
     * Gets all associated <?= $objReverseReference->ObjectDescriptionPlural ?> as an array of <?= $objReverseReference->VariableType ?> objects
     * @param iClause[] $objOptionalClauses additional optional iClause objects for this query
     * @return <?= $objReverseReference->VariableType ?>[]
     * @throws Caller
     */
    public function get<?= $objReverseReference->ObjectDescription ?>Array($objOptionalClauses = null)
    {
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            return array();

        try {
            return <?= $objReverseReference->VariableType ?>::LoadArrayBy<?= $objReverseReferenceColumn->PropertyName ?>(<?= $objCodeGen->ImplodeObjectArray(', ', '$this->', '', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>, $objOptionalClauses);
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

    /**
     * Counts all associated <?= $objReverseReference->ObjectDescriptionPlural ?>

     * @return int
    */
    public function count<?= $objReverseReference->ObjectDescriptionPlural ?>()
    {
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            return 0;

        return <?= $objReverseReference->VariableType ?>::CountBy<?= $objReverseReferenceColumn->PropertyName ?>(<?= $objCodeGen->ImplodeObjectArray(', ', '$this->', '', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>);
    }

    /**
     * Associates a <?= $objReverseReference->ObjectDescription ?>

     * @param <?= $objReverseReference->VariableType ?> $<?= $objReverseReference->VariableName ?>

     * @throws \QCubed\Database\Exception\UndefinedPrimaryKey
     * @return void
    */
    public function associate<?= $objReverseReference->ObjectDescription ?>(<?= $objReverseReference->VariableType ?> $<?= $objReverseReference->VariableName ?>)
    {
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Associate<?= $objReverseReference->ObjectDescription ?> on this unsaved <?= $objTable->ClassName ?>.');
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($' . $objReverseReference->VariableName . '->', '))', 'PropertyName', $objReverseReferenceTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Associate<?= $objReverseReference->ObjectDescription ?> on this <?= $objTable->ClassName ?> with an unsaved <?= $objReverseReferenceTable->ClassName ?>.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::GetDatabase();

        // Perform the SQL Query
        $objDatabase->NonQuery('
            UPDATE
                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Table ?><?= $strEscapeIdentifierEnd ?>

            SET
                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Column ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->SqlVariable($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>) . '
            WHERE
<?php foreach ($objReverseReferenceTable->ColumnArray as $objColumn) { ?>
<?php if ($objColumn->PrimaryKey) { ?>
                <?= $strEscapeIdentifierBegin ?><?= $objColumn->Name ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->SqlVariable($<?= $objReverseReference->VariableName ?>-><?= $objColumn->PropertyName ?>) . ' AND
<?php } ?><?php } ?><?php GO_BACK(5); ?>

        ');
    }

    /**
     * Unassociates a <?= $objReverseReference->ObjectDescription ?>

     * @param <?= $objReverseReference->VariableType ?> $<?= $objReverseReference->VariableName ?>

     * @throws \QCubed\Database\Exception\UndefinedPrimaryKey
     * @return void
    */
    public function unassociate<?= $objReverseReference->ObjectDescription ?>(<?= $objReverseReference->VariableType ?> $<?= $objReverseReference->VariableName ?>)
    {
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Unassociate<?= $objReverseReference->ObjectDescription ?> on this unsaved <?= $objTable->ClassName ?>.');
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($' . $objReverseReference->VariableName . '->', '))', 'PropertyName', $objReverseReferenceTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Unassociate<?= $objReverseReference->ObjectDescription ?> on this <?= $objTable->ClassName ?> with an unsaved <?= $objReverseReferenceTable->ClassName ?>.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::GetDatabase();

        // Perform the SQL Query
        $objDatabase->NonQuery('
            UPDATE
                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Table ?><?= $strEscapeIdentifierEnd ?>

            SET
                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Column ?><?= $strEscapeIdentifierEnd ?> = null
            WHERE
<?php foreach ($objReverseReferenceTable->ColumnArray as $objColumn) { ?>
<?php if ($objColumn->PrimaryKey) { ?>
                <?= $strEscapeIdentifierBegin ?><?= $objColumn->Name ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->SqlVariable($<?= $objReverseReference->VariableName ?>-><?= $objColumn->PropertyName ?>) . ' AND
<?php } ?><?php } ?><?php GO_BACK(1); ?>

                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Column ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->SqlVariable($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>) . '
        ');
    }

    /**
     * Unassociates all <?= $objReverseReference->ObjectDescriptionPlural ?>

     * @return void
    */
    public function unassociateAll<?= $objReverseReference->ObjectDescriptionPlural ?>()
    {
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Unassociate<?= $objReverseReference->ObjectDescription ?> on this unsaved <?= $objTable->ClassName ?>.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::GetDatabase();

        // Perform the SQL Query
        $objDatabase->NonQuery('
            UPDATE
                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Table ?><?= $strEscapeIdentifierEnd ?>

            SET
                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Column ?><?= $strEscapeIdentifierEnd ?> = null
            WHERE
                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Column ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->SqlVariable($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>) . '
        ');
    }

    /**
     * Deletes an associated <?= $objReverseReference->ObjectDescription ?>

     * @param <?= $objReverseReference->VariableType ?> $<?= $objReverseReference->VariableName ?>

     * @return void
    */
    public function deleteAssociated<?= $objReverseReference->ObjectDescription ?>(<?= $objReverseReference->VariableType ?> $<?= $objReverseReference->VariableName ?>)
    {
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Unassociate<?= $objReverseReference->ObjectDescription ?> on this unsaved <?= $objTable->ClassName ?>.');
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($' . $objReverseReference->VariableName . '->', '))', 'PropertyName', $objReverseReferenceTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Unassociate<?= $objReverseReference->ObjectDescription ?> on this <?= $objTable->ClassName ?> with an unsaved <?= $objReverseReferenceTable->ClassName ?>.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::GetDatabase();

        // Perform the SQL Query
        $objDatabase->NonQuery('
            DELETE FROM
                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Table ?><?= $strEscapeIdentifierEnd ?>

            WHERE
<?php foreach ($objReverseReferenceTable->ColumnArray as $objColumn) { ?>
<?php if ($objColumn->PrimaryKey) { ?>
                <?= $strEscapeIdentifierBegin ?><?= $objColumn->Name ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->SqlVariable($<?= $objReverseReference->VariableName ?>-><?= $objColumn->PropertyName ?>) . ' AND
<?php } ?><?php } ?><?php GO_BACK(1); ?>

                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Column ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->SqlVariable($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>) . '
        ');
    }

    /**
     * Deletes all associated <?= $objReverseReference->ObjectDescriptionPlural ?>

     * @return void
    */
    public function deleteAll<?= $objReverseReference->ObjectDescriptionPlural ?>()
    {
        if (<?= $objCodeGen->ImplodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Unassociate<?= $objReverseReference->ObjectDescription ?> on this unsaved <?= $objTable->ClassName ?>.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::GetDatabase();

        // Perform the SQL Query
        $objDatabase->NonQuery('
            DELETE FROM
                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Table ?><?= $strEscapeIdentifierEnd ?>

            WHERE
                <?= $strEscapeIdentifierBegin ?><?= $objReverseReference->Column ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->SqlVariable($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>) . '
        ');
    }
