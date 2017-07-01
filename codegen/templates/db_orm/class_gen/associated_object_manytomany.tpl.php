<?php $objManyToManyReferenceTable = $objCodeGen->TableArray[strtolower($objManyToManyReference->AssociatedTable)]; ?>


    // Related Many-to-Many Objects' Methods for <?= $objManyToManyReference->ObjectDescription ?>

    //-------------------------------------------------------------------

    /**
     * Gets all many-to-many associated <?= $objManyToManyReference->ObjectDescriptionPlural ?> as an array of <?= $objManyToManyReference->VariableType ?> objects
     * @param iClause[] $objClauses additional optional iClause objects for this query
     * @return <?= $objManyToManyReference->VariableType ?>[]
     * @throws Caller
     */
    public function get<?= $objManyToManyReference->ObjectDescription ?>Array($objClauses = null) {
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            return array();

        try {
            return <?= $objManyToManyReference->VariableType ?>::LoadArrayBy<?= $objManyToManyReference->OppositeObjectDescription ?>($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>, $objClauses);
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
    }

    /**
     * Gets all many-to-many associated <?= $objManyToManyReference->ObjectDescriptionPlural ?> as an array of <?= $objManyToManyReference->VariableType ?> objects
     * @param iClause[] $objClauses additional optional iClause objects for this query
     * @return <?= $objManyToManyReference->VariableType ?>[]
     * @throws Caller
     */
    public function get<?= $objManyToManyReference->ObjectDescription ?>Keys()
    {
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Get<?= $objManyToManyReference->ObjectDescription ?>Ids on this unsaved <?= $objTable->ClassName ?>.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::getDatabase();

        // Perform the SQL Query
        $objResult = $objDatabase->query('
            SELECT <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->OppositeColumn ?><?= $strEscapeIdentifierEnd ?>

            FROM <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->Table ?><?= $strEscapeIdentifierEnd ?>

            WHERE <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->Column ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->sqlVariable($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>) . '
        ');

        $keys = array();
        while ($row = $objResult->fetchRow()) {
            $keys[] = $row[0];
        }
        return $keys;
    }


    /**
     * Counts all many-to-many associated <?= $objManyToManyReference->ObjectDescriptionPlural ?>

     * @return int
    */
    public function count<?= $objManyToManyReference->ObjectDescriptionPlural ?>()
    {
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            return 0;

        return <?= $objManyToManyReference->VariableType ?>::CountBy<?= $objManyToManyReference->OppositeObjectDescription ?>($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>);
    }

    /**
     * Checks to see if an association exists with a specific <?= $objManyToManyReference->ObjectDescription ?>

     * @param <?= $objManyToManyReference->VariableType ?> $<?= $objManyToManyReference->VariableName ?>

     * @return bool
    */
    public function is<?= $objManyToManyReference->ObjectDescription ?>Associated(<?= $objManyToManyReference->VariableType ?> $<?= $objManyToManyReference->VariableName ?>)
    {
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Is<?= $objManyToManyReference->ObjectDescription ?>Associated on this unsaved <?= $objTable->ClassName ?>.');
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($' . $objManyToManyReference->VariableName . '->', '))', 'PropertyName', $objManyToManyReferenceTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Is<?= $objManyToManyReference->ObjectDescription ?>Associated on this <?= $objTable->ClassName ?> with an unsaved <?= $objManyToManyReferenceTable->ClassName ?>.');

        $intRowCount = <?= $objTable->ClassName ?>::queryCount(
            QQ::andCondition(
                QQ::equal(QQN::<?= $objTable->ClassName ?>()-><?= $objTable->PrimaryKeyColumnArray[0]->PropertyName ?>, $this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>),
                QQ::equal(QQN::<?= $objTable->ClassName ?>()-><?= $objManyToManyReference->ObjectDescription ?>-><?= $objManyToManyReference->OppositePropertyName ?>, $<?= $objManyToManyReference->VariableName ?>-><?= $objManyToManyReferenceTable->PrimaryKeyColumnArray[0]->PropertyName ?>)
            )
        );

        return ($intRowCount > 0);
    }

    /**
     * Checks to see if an association exists with a specific <?= $objManyToManyReference->ObjectDescription ?> by key

     * @param <?= $objTable->PrimaryKeyColumnArray[0]->VariableType ?> $key

     * @return bool
    */
    public function is<?= $objManyToManyReference->ObjectDescription ?>AssociatedByKey($key)
    {
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Is<?= $objManyToManyReference->ObjectDescription ?>AssociatedByKey on this unsaved <?= $objTable->ClassName ?>.');

        $intRowCount = <?= $objTable->ClassName ?>::queryCount(
            QQ::andCondition(
                QQ::equal(QQN::<?= $objTable->ClassName ?>()-><?= $objTable->PrimaryKeyColumnArray[0]->PropertyName ?>, $this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>),
                QQ::equal(QQN::<?= $objTable->ClassName ?>()-><?= $objManyToManyReference->ObjectDescription ?>-><?= $objManyToManyReference->OppositePropertyName ?>, $key)
            )
        );

        return ($intRowCount > 0);
    }

    /**
     * Associates a <?= $objManyToManyReference->ObjectDescription ?>

     * @param <?= $objManyToManyReference->VariableType ?> $<?= $objManyToManyReference->VariableName ?>

     * @return void
    */
    public function associate<?= $objManyToManyReference->ObjectDescription ?>(<?= $objManyToManyReference->VariableType ?> $<?= $objManyToManyReference->VariableName ?>) {
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Associate<?= $objManyToManyReference->ObjectDescription ?> on this unsaved <?= $objTable->ClassName ?>.');
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($' . $objManyToManyReference->VariableName . '->', '))', 'PropertyName', $objManyToManyReferenceTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Associate<?= $objManyToManyReference->ObjectDescription ?> on this <?= $objTable->ClassName ?> with an unsaved <?= $objManyToManyReferenceTable->ClassName ?>.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::getDatabase();

        // Perform the SQL Query
        $objDatabase->nonQuery('
            INSERT INTO <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->Table ?><?= $strEscapeIdentifierEnd ?> (
                <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->Column ?><?= $strEscapeIdentifierEnd ?>,
                <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->OppositeColumn ?><?= $strEscapeIdentifierEnd ?>

            ) VALUES (
                ' . $objDatabase->sqlVariable($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>) . ',
                ' . $objDatabase->sqlVariable($<?= $objManyToManyReference->VariableName ?>-><?= $objManyToManyReferenceTable->PrimaryKeyColumnArray[0]->PropertyName ?>) . '
            )
        ');

        // Notify
        static::broadcastAssociationAdded("<?= $objManyToManyReference->Table ?>", $this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>, $<?= $objManyToManyReference->VariableName ?>-><?= $objManyToManyReferenceTable->PrimaryKeyColumnArray[0]->PropertyName ?>);
    }

    /**
     * Associates a <?= $objManyToManyReference->ObjectDescription ?> by its primary key.

     * @param <?= $objManyToManyReference->OppositeVariableType ?> $<?= $objManyToManyReference->OppositeVariableName ?>

     * @return void
    */
    public function associate<?= $objManyToManyReference->ObjectDescription ?>ByKey($<?= $objManyToManyReference->OppositeVariableName ?>)
    {
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Associate<?= $objManyToManyReference->ObjectDescription ?> on this unsaved <?= $objTable->ClassName ?>.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::getDatabase();

        // Perform the SQL Query
        $objDatabase->nonQuery('
            INSERT INTO <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->Table ?><?= $strEscapeIdentifierEnd ?> (
                <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->Column ?><?= $strEscapeIdentifierEnd ?>,
                <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->OppositeColumn ?><?= $strEscapeIdentifierEnd ?>

            ) VALUES (
                ' . $objDatabase->sqlVariable($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>) . ',
                ' . $objDatabase->sqlVariable($<?= $objManyToManyReference->OppositeVariableName ?>) . '
            )
        ');

         // Notify
        static::broadcastAssociationAdded("<?= $objManyToManyReference->Table ?>", $this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>, $<?= $objManyToManyReference->OppositeVariableName ?>);
   }


    /**
     * Unassociates a <?= $objManyToManyReference->ObjectDescription ?>

     * @param <?= $objManyToManyReference->VariableType ?> $<?= $objManyToManyReference->VariableName ?>

     * @return void
    */
    public function unassociate<?= $objManyToManyReference->ObjectDescription ?>(<?= $objManyToManyReference->VariableType ?> $<?= $objManyToManyReference->VariableName ?>)
    {
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Unassociate<?= $objManyToManyReference->ObjectDescription ?> on this unsaved <?= $objTable->ClassName ?>.');
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($' . $objManyToManyReference->VariableName . '->', '))', 'PropertyName', $objManyToManyReferenceTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call Unassociate<?= $objManyToManyReference->ObjectDescription ?> on this <?= $objTable->ClassName ?> with an unsaved <?= $objManyToManyReferenceTable->ClassName ?>.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::getDatabase();

        // Perform the SQL Query
        $objDatabase->nonQuery('
            DELETE FROM
                <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->Table ?><?= $strEscapeIdentifierEnd ?>

            WHERE
                <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->Column ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->sqlVariable($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>) . ' AND
                <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->OppositeColumn ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->sqlVariable($<?= $objManyToManyReference->VariableName ?>-><?= $objManyToManyReferenceTable->PrimaryKeyColumnArray[0]->PropertyName ?>) . '
        ');

        // Notify
        static::broadcastAssociationRemoved("<?= $objManyToManyReference->Table ?>", $this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>, $<?= $objManyToManyReference->VariableName ?>-><?= $objManyToManyReferenceTable->PrimaryKeyColumnArray[0]->PropertyName ?>);

    }

    /**
     * Unassociates all <?= $objManyToManyReference->ObjectDescriptionPlural ?>

     * @return void
    */
    public function unassociateAll<?= $objManyToManyReference->ObjectDescriptionPlural ?>()
    {
        if (<?= $objCodeGen->implodeObjectArray(' || ', '(is_null($this->', '))', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)
            throw new \QCubed\Database\Exception\UndefinedPrimaryKey('Unable to call UnassociateAll<?= $objManyToManyReference->ObjectDescription ?>Array on this unsaved <?= $objTable->ClassName ?>.');

        // Get the Database Object for this Class
        $objDatabase = <?= $objTable->ClassName ?>::getDatabase();

        // Perform the SQL Query
        $objDatabase->nonQuery('
            DELETE FROM
                <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->Table ?><?= $strEscapeIdentifierEnd ?>

            WHERE
                <?= $strEscapeIdentifierBegin ?><?= $objManyToManyReference->Column ?><?= $strEscapeIdentifierEnd ?> = ' . $objDatabase->sqlVariable($this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>) . '
        ');

        static::broadcastAssociationRemoved("<?= $objManyToManyReference->Table ?>");

    }