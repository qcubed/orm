<?php
/**
 * Primitives to broadcast changes. These are stubs to be implemented by other templates.
 */

if (count($objTable->PrimaryKeyColumnArray) == 1) {
	$pkType = $objTable->PrimaryKeyColumnArray[0]->VariableType;
} else {
	$pkType = 'string';	// combined pk
}

?>

   /**
	* The current record has just been inserted into the table. Let everyone know.
    *
	* @param <?= $pkType ?>	$pk Primary key of record just inserted.
	*/
	protected static function broadcastInsert($pk)
    {
	}

   /**
	* The current record has just been updated. Let everyone know. $this->__blnDirty has the fields
    * that were just updated.
    *
	* @param <?= $pkType ?>	$pk Primary key of record just updated.
	* @param string[] $fields array of field names that were modified.
	*/
	protected static function broadcastUpdate($pk, $fields)
    {
	}

   /**
	* The current record has just been deleted. Let everyone know.
    *
	* @param <?= $pkType ?>	$pk Primary key of record just deleted.
	*/
	protected static function broadcastDelete($pk)
    {
	}

   /**
	* All records have just been deleted. Let everyone know.
	*/
	protected static function broadcastDeleteAll()
    {
	}

   /**
    * An association table entry has just been added. Let everyone know.
    *
    * @params string $strTableName
    * @param <?= $pkType ?>	$pk1
    * @param mixed	$pk2
    */
    protected static function broadcastAssociationAdded($strTableName, $pk1, $pk2)
    {
    }

   /**
    * An association table entry has just been removed. Let everyone know.
    *
    * @params string $strTableName
    * @param <?= $pkType ?>|null$pk1    Null if we are removing all associations
    * @param mixed|null	$pk2            Null if we are removing all associations
    */
    protected static function broadcastAssociationRemoved($strTableName, $pk1 = null, $pk2 = null)
    {
    }