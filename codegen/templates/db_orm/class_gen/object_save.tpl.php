<?php
$idsAsSql = [];
$idsAsParams = [];
foreach ($objTable->PrimaryKeyColumnArray as $objPkColumn) {
    $strLocal = '$this->' . ($objPkColumn->Identity ? '' : '__') . $objPkColumn->VariableName;
    $strCol = $strEscapeIdentifierBegin . $objPkColumn->Name . $strEscapeIdentifierEnd;
    $strValue = '$objDatabase->SqlVariable(' . $strLocal . ')';
    $idsAsSql[] = $strCol . ' = \' . ' . $strValue;
    $idsAsParams[] = $strLocal;
}

$strIds = implode(" . ' AND \n", $idsAsSql);
$strIdsAsParams = implode(", ", $idsAsParams);

foreach ($objTable->ColumnArray as $objColumn) {
    if ($objColumn->Timestamp) {
        $timestampColumn = $objColumn;
    }
    if ($objColumn->Identity) {
        $identityColumn = $objColumn;
    }
}

$blnHasUniqueReverseReference = false;
foreach ($objTable->ReverseReferenceArray as $objReverseReference) {
    if ($objReverseReference->Unique) {
        $blnHasUniqueReverseReference = true;
        break;
    }
}
?>


    /**
    * Save this <?= $objTable->ClassName ?>

    * @param bool $blnForceInsert
    * @param bool $blnForceUpdate
    * @throws Caller
<?php
$returnType = 'void';
foreach ($objArray = $objTable->ColumnArray as $objColumn) {
    if ($objColumn->Identity) {
        $returnType = 'int';
        break;
    }
}
print '    * @return ' . $returnType;

$strCols = '';
$strValues = '';
$strColUpdates = '';
foreach ($objTable->ColumnArray as $objColumn) {
    if ((!$objColumn->Identity) &&
        !($objColumn->Timestamp && !$objColumn->AutoUpdate)
    ) { // If the timestamp column is updated by the sql database, then don't do an insert on that column (AutoUpdate here actually means we manually update it in PHP)
        if ($strCols) {
            $strCols .= ",\n";
        }
        if ($strValues) {
            $strValues .= ",\n";
        }
        if ($strColUpdates) {
            $strColUpdates .= ",\n";
        }
        $strCol = '							' . $strEscapeIdentifierBegin . $objColumn->Name . $strEscapeIdentifierEnd;
        $strCols .= $strCol;
        $strValue = '\' . $objDatabase->SqlVariable($this->' . $objColumn->VariableName . ') . \'';
        $strValues .= '							' . $strValue;
        $strColUpdates .= $strCol . ' = ' . $strValue;
    }
}
if ($strValues) {
    $strCols = " (\n" . $strCols . "\n						)";
    $strValues = " VALUES (\n" . $strValues . "\n						)\n";
} else {
    $strValues = " DEFAULT VALUES";
}


?>

    */
    public function save($blnForceInsert = false, $blnForceUpdate = false)
    {
        $mixToReturn = null;
        try {
            if ((!$this->__blnRestored && !$blnForceUpdate) || ($blnForceInsert)) {
                $mixToReturn = $this->Insert();
            } else {
                $this->Update($blnForceUpdate);
            }
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
        // Update __blnRestored and any Non-Identity PK Columns (if applicable)
        $this->__blnRestored = true;
<?php foreach ($objTable->PrimaryKeyColumnArray as $objColumn) { ?>
<?php   if ((!$objColumn->Identity) && ($objColumn->PrimaryKey)) { ?>
        $this->__<?= $objColumn->VariableName ?> = $this-><?= $objColumn->VariableName ?>;
<?php   } ?>
<?php } ?>

        $this->deleteFromCache();

        $this->__blnDirty = null; // reset dirty values

        return $mixToReturn;
    }

    /**
     * Insert into <?= $objTable->ClassName ?>

     */
    protected function insert()
    {
        $mixToReturn = null;
        $objDatabase = <?= $objTable->ClassName ?>::GetDatabase();
<?php if (isset($timestampColumn) && $timestampColumn->AutoUpdate) { // We are manually updating a timestamp column here?>
        $this-><?= $timestampColumn->VariableName ?> = QDateTime::nowToString(QDateTime::FormatIso);
        $this->__blnValid[self::<?= strtoupper($timestampColumn->Name) ?>_FIELD] = true;
<?php  } ?>

        $objDatabase->NonQuery('
            INSERT INTO <?= $strEscapeIdentifierBegin ?><?= $objTable->Name ?><?= $strEscapeIdentifierEnd ?><?= $strCols . $strValues; ?>
        ');
<?php if (isset($identityColumn)) { ?>
        // Update Identity column and return its value
        $mixToReturn = $this-><?= $identityColumn->VariableName ?> = $objDatabase->InsertId('<?= $objTable->Name ?>', '<?= $identityColumn->Name ?>');
        $this->__blnValid[self::<?= strtoupper($identityColumn->Name) ?>_FIELD] = true;
<?php  } ?>

<?php if (isset($timestampColumn) && !$timestampColumn->AutoUpdate) { ?>
        // Update Timestamp value that was set by database
        $objResult = $objDatabase->Query('
        SELECT
        <?= $strEscapeIdentifierBegin ?><?= $timestampColumn->Name ?><?= $strEscapeIdentifierEnd ?>

        FROM
        <?= $strEscapeIdentifierBegin ?><?= $objTable->Name ?><?= $strEscapeIdentifierEnd ?>

        WHERE
        <?= _indent_($strIds, 4); ?>

        );

        $objRow = $objResult->FetchArray();
        $this-><?= $timestampColumn->VariableName ?> = $objRow[0];
        $this->__blnValid[self::<?= strtoupper($timestampColumn->Name) ?>_FIELD] = true;

<?php } ?>

        static::broadcastInsert($this->PrimaryKey());

        return $mixToReturn;
    }

   /**
    * Update this <?= $objTable->ClassName ?>

    * @param bool $blnForceUpdate
    */
    protected function update($blnForceUpdate = false)
    {
        $objDatabase = static::getDatabase();

        if (empty($this->__blnDirty)) {
            return; // nothing has changed
        }

        $strValues = $this->GetValueClause();

        $strSql = '
        UPDATE
            <?= $strEscapeIdentifierBegin ?><?= $objTable->Name ?><?= $strEscapeIdentifierEnd ?>

        SET
        ' . $strValues . '

        WHERE
<?= _indent_($strIds, 4) ?>;
<?php
    $blnNeedsTransaction = false;
    if ($blnHasUniqueReverseReference || isset($timestampColumn)) {
        $blnNeedsTransaction = true;
    }
    if (!$blnNeedsTransaction) { ?>
        $objDatabase->NonQuery($strSql);
<?php  } else { ?>
        $objDatabase->TransactionBegin();
        try {
<?php   if (isset($timestampColumn)) { ?>
            if (!$blnForceUpdate) {
                $this->OptimisticLockingCheck();
            }
<?php       if ($timestampColumn->AutoUpdate) { // manually udpate the timestamp value before saving?>
                $this-><?= $timestampColumn->VariableName ?> = QDateTime::NowToString(QDateTime::FormatIso);
                $this->__blnValid[self::<?= strtoupper($timestampColumn->Name) ?>_FIELD] = true;
<?php       } ?>
<?php   } ?>

       $objDatabase->NonQuery($strSql);

<?php foreach ($objTable->ReverseReferenceArray as $objReverseReference) { ?>
<?php     if ($objReverseReference->Unique) { ?>
<?php       $objReverseReferenceTable = $objCodeGen->TableArray[strtolower($objReverseReference->Table)]; ?>
<?php       $objReverseReferenceColumn = $objReverseReferenceTable->ColumnArray[strtolower($objReverseReference->Column)]; ?>


        // Update the foreign key in the <?= $objReverseReference->ObjectDescription ?> object (if applicable)
        if ($this->blnDirty<?= $objReverseReference->ObjectPropertyName ?>) {
            // Unassociate the old one (if applicable)
            if ($objAssociated = <?= $objReverseReference->VariableType ?>::LoadBy<?= $objReverseReferenceColumn->PropertyName ?>(<?= $objCodeGen->ImplodeObjectArray(', ', '$this->', '', 'VariableName', $objTable->PrimaryKeyColumnArray) ?>)) {
                // TODO: Select and update only the foreign key rather than the whole record
                $objAssociated-><?= $objReverseReferenceColumn->PropertyName ?> = null;
                $objAssociated->save();
            }

            // Associate the new one (if applicable)
            if ($this-><?= $objReverseReference->ObjectMemberVariable ?>) {
                $this-><?= $objReverseReference->ObjectMemberVariable ?>-><?= $objReverseReferenceColumn->PropertyName ?> = $this-><?= $objTable->PrimaryKeyColumnArray[0]->VariableName ?>;
                $this-><?= $objReverseReference->ObjectMemberVariable ?>->save();
            }

            // Reset the "Dirty" flag
            $this->blnDirty<?= $objReverseReference->ObjectPropertyName ?> = false;
        }
<?php       } ?>
<?php  } ?>

<?php if (isset($timestampColumn) && !($timestampColumn->AutoUpdate)) { ?>
			// Update Local Timestamp
			$objResult = $objDatabase->query('
				SELECT
					<?= $strEscapeIdentifierBegin ?><?= $timestampColumn->Name ?><?= $strEscapeIdentifierEnd ?>

				FROM
					<?= $strEscapeIdentifierBegin ?><?= $objTable->Name ?><?= $strEscapeIdentifierEnd ?>

				WHERE
<?= _indent_($strIds, 5); ?>

			);

			$objRow = $objResult->fetchArray();
			$this-><?= $timestampColumn->VariableName ?> = $objRow[0];
            $this->__blnValid[self::<?= strtoupper($timestampColumn->Name) ?>_FIELD] = true;

<?php } ?>

            $objDatabase->transactionCommit();
        }
        catch (Exception $e) {
            $objDatabase->transactionRollback();
            throw($e);
        }
<?php } ?>
		static::broadcastUpdate($this->PrimaryKey(), array_keys($this->__blnDirty));
	}

   /**
	* Creates a value clause for the currently changed fields.
	*
	* @return string
	*/
	protected function getValueClause()
    {
		$values = [];
		$objDatabase = static::getDatabase();

<?php
foreach ($objTable->ColumnArray as $objColumn) {
	if ((!$objColumn->Identity) && !($objColumn->Timestamp && !$objColumn->AutoUpdate)) {
?>
		if (isset($this->__blnDirty[self::<?= strtoupper($objColumn->Name) ?>_FIELD])) {
			$strCol = '<?= $strEscapeIdentifierBegin ?><?= $objColumn->Name ?><?= $strEscapeIdentifierEnd ?>';
			$strValue = $objDatabase->sqlVariable($this-><?= $objColumn->VariableName ?>);
			$values[] = $strCol . ' = ' . $strValue;
		}
<?php
	}
}
?>
		if ($values) {
			return implode(",\n", $values);
		}
		else {
			return "";
		}
	}


<?php if (isset($timestampColumn)) { ?>
	protected function optimisticLockingCheck()
    {
        if (empty($this->__blnValid[self::<?= strtoupper($timestampColumn->Name) ?>_FIELD])) {
            throw new \QCubed\Exception\Caller("To be able to update table '<?= $objTable->Name ?>' you must have previously selected the <?= $timestampColumn->Name ?> column because its used to detect optimistic locking collisions.");
        }

        $objDatabase = static::getDatabase();
		$objResult = $objDatabase->query('
SELECT
	<?= $strEscapeIdentifierBegin ?><?= $timestampColumn->Name ?><?= $strEscapeIdentifierEnd ?>

FROM
	<?= $strEscapeIdentifierBegin ?><?= $objTable->Name ?><?= $strEscapeIdentifierEnd ?>

WHERE
<?= $strIds; ?>
		);

		$objRow = $objResult->fetchArray();
		if ($objRow[0] != $this-><?= $timestampColumn->VariableName ?>) {
			// Row was updated since we got the row, now check to see if we actually changed fields that were previously changed.
			$changed = false;
			$obj<?= $objTable->ClassName ?> = <?= $objTable->ClassName ?>::Load(<?= $strIdsAsParams ?>);
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
			$changed = $changed || (isset($this->__blnDirty[self::<?= strtoupper($objColumn->Name) ?>_FIELD]) && ($this-><?= $objColumn->VariableName ?> !== $obj<?= $objTable->ClassName ?>-><?= $objColumn->VariableName ?>));
<?php } ?>
			if ($changed) {
				throw new \QCubed\Database\Exception\OptimisticLocking('<?= $objTable->ClassName ?>');
			}
		}
	}
<?php } ?>
