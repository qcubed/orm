/////////////////////////////////////
// ADDITIONAL CLASSES for QCubed QUERY
/////////////////////////////////////

<?php foreach ($objTable->ManyToManyReferenceArray as $objReference) { ?>
/**
 * @property-read Node\Column $<?= $objReference->OppositePropertyName ?>

 * @property-read Node<?= $objReference->VariableType ?> $<?= $objReference->VariableType ?>

 * @property-read Node<?= $objReference->VariableType ?> $_ChildTableNode
 **/
class Node<?= $objTable->ClassName ?><?= $objReference->ObjectDescription ?> extends Node\Association
{
    protected $strType = \QCubed\Type::ASSOCIATION;
    protected $strName = '<?= strtolower($objReference->ObjectDescription); ?>';

    protected $strTableName = '<?= $objReference->Table ?>';
    protected $strPrimaryKey = '<?= $objReference->Column ?>';
    protected $strClassName = '<?= $objReference->VariableType ?>';
    protected $strPropertyName = '<?= $objReference->ObjectDescription ?>';
    protected $strAlias = '<?= strtolower($objReference->ObjectDescription); ?>';

    /**
    * __get Magic Method
    *
    * @param string $strName
    * @throws Caller
    */
    public function __get($strName) {
        switch ($strName) {
            case '<?= $objReference->OppositePropertyName ?>':
                return new Node\Column('<?= $objReference->OppositeColumn ?>', '<?= $objReference->OppositePropertyName ?>', '<?= $objReference->OppositeDbType ?>', $this);
            case '<?= $objReference->VariableType ?>':
                return new Node<?= $objReference->VariableType ?>('<?= $objReference->OppositeColumn ?>', '<?= $objReference->OppositePropertyName ?>', '<?= $objReference->OppositeDbType ?>', $this);
            case '_ChildTableNode':
                return new Node<?= $objReference->VariableType ?>('<?= $objReference->OppositeColumn ?>', '<?= $objReference->OppositePropertyName ?>', '<?= $objReference->OppositeDbType ?>', $this);
            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }
}

<?php } ?>
/**
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
 * @property-read Node\Column $<?= $objColumn->PropertyName ?>

<?php if ($objColumn->Reference) { ?>
 * @property-read Node<?= $objColumn->Reference->VariableType; ?> $<?= $objColumn->Reference->PropertyName ?>

<?php } ?>
<?php } ?>
<?php foreach ($objTable->ManyToManyReferenceArray as $objReference) { ?>
 * @property-read Node<?= $objTable->ClassName ?><?= $objReference->ObjectDescription ?> $<?= $objReference->ObjectDescription ?>

<?php } ?>
<?php foreach ($objTable->ReverseReferenceArray as $objReference) { ?>
 * @property-read ReverseReferenceNode<?= $objReference->VariableType ?> $<?= $objReference->ObjectDescription ?>

<?php } ?>
<?php $objPkColumn = $objTable->PrimaryKeyColumnArray[0]; ?>
 * @property-read Node\Column<?php if (($objPkColumn->Reference) && (!$objPkColumn->Reference->IsType)) print $objPkColumn->Reference->VariableType; ?> $_PrimaryKeyNode
 **/
class Node<?= $objTable->ClassName ?> extends Node\Table {
    protected $strTableName = '<?= $objTable->Name ?>';
    protected $strPrimaryKey = '<?= $objTable->PrimaryKeyColumnArray[0]->Name ?>';
    protected $strClassName = '<?= $objTable->ClassName ?>';

    /**
    * @return array
    */
    public function fields() {
        return [
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
            "<?= $objColumn->Name ?>",
<?php } ?>
        ];
    }

    /**
    * @return array
    */
    public function primaryKeyFields() {
        return [
<?php foreach ($objTable->PrimaryKeyColumnArray as $objColumn) { ?>
            "<?= $objColumn->Name ?>",
<?php } ?>
        ];
    }

   /**
    * @return AbstractDatabase
    */
    protected function database() {
        return \QCubed\Database\Service::getDatabase(<?= $objCodeGen->DatabaseIndex; ?>);
    }


    /**
    * __get Magic Method
    *
    * @param string $strName
    * @throws Caller
    */
    public function __get($strName) {
        switch ($strName) {
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
            case '<?= $objColumn->PropertyName ?>':
                return new Node\Column('<?= $objColumn->Name ?>', '<?= $objColumn->PropertyName ?>', '<?= $objColumn->DbType ?>', $this);
<?php if ($objColumn->Reference) { ?>
            case '<?= $objColumn->Reference->PropertyName ?>':
                return new Node<?= $objColumn->Reference->VariableType; ?>('<?= $objColumn->Name ?>', '<?= $objColumn->Reference->PropertyName ?>', '<?= $objColumn->DbType ?>', $this);
<?php } ?>
<?php } ?>
<?php foreach ($objTable->ManyToManyReferenceArray as $objReference) { ?>
            case '<?= $objReference->ObjectDescription ?>':
                return new Node<?= $objTable->ClassName ?><?= $objReference->ObjectDescription ?>($this);
<?php } ?><?php foreach ($objTable->ReverseReferenceArray as $objReference) { ?>
            case '<?= $objReference->ObjectDescription ?>':
                return new ReverseReferenceNode<?= $objReference->VariableType ?>($this, '<?= strtolower($objReference->ObjectDescription); ?>', \QCubed\Type::REVERSE_REFERENCE, '<?= $objReference->Column ?>', '<?= $objReference->ObjectDescription ?>');
<?php } ?><?php $objPkColumn = $objTable->PrimaryKeyColumnArray[0]; ?>

            case '_PrimaryKeyNode':
<?php if (($objPkColumn->Reference) && (!$objPkColumn->Reference->IsType)) {?>
                return new Node<?= $objPkColumn->Reference->VariableType; ?>('<?= $objPkColumn->Name ?>', '<?= $objPkColumn->PropertyName ?>', '<?= $objPkColumn->DbType ?>', $this);
<?php } else { ?>
                return new Node\Column('<?= $objPkColumn->Name ?>', '<?= $objPkColumn->PropertyName ?>', '<?= $objPkColumn->DbType ?>', $this);
<?php } ?>
            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }
}

/**
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
 * @property-read Node\Column $<?= $objColumn->PropertyName ?>

<?php if (($objColumn->Reference) && (!$objColumn->Reference->IsType)) { ?>
 * @property-read Node<?= $objColumn->Reference->VariableType; ?> $<?= $objColumn->Reference->PropertyName ?>

<?php } ?>
<?php } ?>
<?php foreach ($objTable->ManyToManyReferenceArray as $objReference) { ?>
 * @property-read Node<?= $objTable->ClassName ?><?= $objReference->ObjectDescription ?> $<?= $objReference->ObjectDescription ?>

<?php } ?>
<?php foreach ($objTable->ReverseReferenceArray as $objReference) { ?>
 * @property-read ReverseReferenceNode<?= $objReference->VariableType ?> $<?= $objReference->ObjectDescription ?>

<?php } ?>
<?php $objPkColumn = $objTable->PrimaryKeyColumnArray[0]; ?>

 * @property-read Node\Column<?php if (($objPkColumn->Reference) && (!$objPkColumn->Reference->IsType)) print $objPkColumn->Reference->VariableType; ?> $_PrimaryKeyNode
 **/
class ReverseReferenceNode<?= $objTable->ClassName ?> extends Node\ReverseReference {
    protected $strTableName = '<?= $objTable->Name ?>';
    protected $strPrimaryKey = '<?= $objTable->PrimaryKeyColumnArray[0]->Name ?>';
    protected $strClassName = '<?= $objTable->ClassName ?>';

    /**
    * @return array
    */
    public function fields() {
        return [
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
            "<?= $objColumn->Name ?>",
<?php } ?>
        ];
    }

    /**
    * @return array
    */
    public function primaryKeyFields() {
        return [
<?php foreach ($objTable->PrimaryKeyColumnArray as $objColumn) { ?>
            "<?= $objColumn->Name ?>",
<?php } ?>
        ];
    }

    /**
    * __get Magic Method
    *
    * @param string $strName
    * @throws Caller
    */
    public function __get($strName) {
        switch ($strName) {
<?php foreach ($objTable->ColumnArray as $objColumn) { ?>
            case '<?= $objColumn->PropertyName ?>':
                return new Node\Column('<?= $objColumn->Name ?>', '<?= $objColumn->PropertyName ?>', '<?= $objColumn->DbType ?>', $this);
<?php if (($objColumn->Reference) && (!$objColumn->Reference->IsType)) { ?>
            case '<?= $objColumn->Reference->PropertyName ?>':
                return new Node<?= $objColumn->Reference->VariableType; ?>('<?= $objColumn->Name ?>', '<?= $objColumn->Reference->PropertyName ?>', '<?= $objColumn->DbType ?>', $this);
<?php } ?>
<?php } ?>
<?php foreach ($objTable->ManyToManyReferenceArray as $objReference) { ?>
            case '<?= $objReference->ObjectDescription ?>':
                return new Node<?= $objTable->ClassName ?><?= $objReference->ObjectDescription ?>($this);
<?php } ?><?php foreach ($objTable->ReverseReferenceArray as $objReference) { ?>
            case '<?= $objReference->ObjectDescription ?>':
                return new ReverseReferenceNode<?= $objReference->VariableType ?>($this, '<?= strtolower($objReference->ObjectDescription); ?>', \QCubed\Type::REVERSE_REFERENCE, '<?= $objReference->Column ?>', '<?= $objReference->ObjectDescription ?>');
<?php } ?><?php $objPkColumn = $objTable->PrimaryKeyColumnArray[0]; ?>

            case '_PrimaryKeyNode':
<?php if (($objPkColumn->Reference) && (!$objPkColumn->Reference->IsType)) {?>
                return new Node<?= $objPkColumn->Reference->VariableType; ?>('<?= $objPkColumn->Name ?>', '<?= $objPkColumn->PropertyName ?>', '<?= $objPkColumn->DbType ?>', $this);
<?php } else { ?>
                return new Node\Column('<?= $objPkColumn->Name ?>', '<?= $objPkColumn->PropertyName ?>', '<?= $objPkColumn->DbType ?>', $this);
<?php } ?>
            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }
}
