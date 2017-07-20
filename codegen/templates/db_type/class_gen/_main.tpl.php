<?php
	/** @var \QCubed\Codegen\TypeTable $objTypeTable */
	/** @var \QCubed\Codegen\DatabaseCodeGen $objCodeGen */
	global $_TEMPLATE_SETTINGS;
	$_TEMPLATE_SETTINGS = array(
		'OverwriteFlag' => true,
		'DirectorySuffix' => '',
		'TargetDirectory' => QCUBED_PROJECT_MODEL_GEN_DIR,
		'TargetFileName' => $objTypeTable->ClassName . 'Gen.php'
	);
?>
<?php print("<?php\n"); ?>
/**
 * <?= $objTypeTable->ClassName ?> file
 */

use QCubed\Query\Node;
use QCubed\Exception\Caller;

/**
 * Class <?= $objTypeTable->ClassName ?>
 *
 * The <?= $objTypeTable->ClassName ?> class defined here contains
 * code for the <?= $objTypeTable->ClassName ?> enumerated type.  It represents
 * the enumerated values found in the "<?= $objTypeTable->Name ?>" table
 * in the database.
 *
 * To use, you should use the <?= $objTypeTable->ClassName ?> subclass which
 * extends this <?= $objTypeTable->ClassName ?>Gen class.
 *
 * Because subsequent re-code generations will overwrite any changes to this
 * file, you should leave this file unaltered to prevent yourself from losing
 * any information or code changes.  All customizations should be done by
 * overriding existing or implementing new methods, properties and variables
 * in the <?= $objTypeTable->ClassName ?> class.
 *
 * @package <?= \QCubed\Project\Codegen\CodegenBase::$ApplicationName; ?>

 * @subpackage Model
 */
abstract class <?= $objTypeTable->ClassName ?>Gen extends \QCubed\ObjectBase {
<?= ($intKey = 0) == 1; ?><?php foreach ($objTypeTable->TokenArray as $intKey=>$strValue) { ?>
    const <?= $strValue ?> = <?= $intKey ?>;
<?php } ?>

    const MAX_ID = <?= $intKey ?>;

    public static function nameArray() {
        return [
<?php if (count($objTypeTable->NameArray)) { ?>
<?php   foreach ($objTypeTable->NameArray as $intKey=>$strValue) { ?>
			<?= $intKey ?> => t('<?= $strValue ?>'),
<?php   } ?><?php GO_BACK(2); ?>
<?php }?>

        ];
    }

    public static function tokenArray() {
        return [
<?php if (count($objTypeTable->TokenArray)) { ?>
<?php   foreach ($objTypeTable->TokenArray as $intKey=>$strValue) { ?>
			<?= $intKey ?> => '<?= $strValue ?>',
<?php   } ?><?php GO_BACK(2); ?>
<?php }?>

        ];
    }

<?php if (count($objTypeTable->ExtraFieldsArray)) { ?>
    public static function extraColumnNamesArray() {
        return [
<?php foreach ($objTypeTable->ExtraFieldsArray as $colData) { ?>
            '<?= $colData['name'] ?>',
<?php } ?><?php GO_BACK(2); ?>

        ];
    }

    public static function extraColumnValuesArray() {
        return array(
<?php foreach ($objTypeTable->ExtraPropertyArray as $intKey=>$arrColumns) { ?>
            <?= $intKey ?> => array (
<?php 	foreach ($arrColumns as $strColName=>$mixColValue) { ?>
                '<?= $strColName ?>' => <?= \QCubed\Codegen\TypeTable::literal($mixColValue) ?>,
<?php 	} ?><?php GO_BACK(2); ?>

            ),
<?php } ?><?php GO_BACK(2); ?>

        );
    }


<?php if (count($objTypeTable->ExtraFieldsArray)) { ?>
<?php   foreach ($objTypeTable->ExtraFieldsArray as $colData) { ?>
    public static function <?= lcfirst($colData['name']) ?>Array() {
        return array(
<?php       foreach ($objTypeTable->ExtraPropertyArray as $intKey=>$arrColumns) { ?>
            '<?= $intKey ?>' => <?= \QCubed\Codegen\TypeTable::literal($arrColumns[$colData['name']]) ?>,
<?php       }     ?><?php GO_BACK(2); ?>

        );
    }

<?php   } ?>
<?php } ?>


<?php }?>
    /**
     * Returns the string corresponding to the given id.
     *
     * @param integer $int<?= $objTypeTable->ClassName ?>Id
     * @throws Caller
     * @returns string
     */
    public static function toString($int<?= $objTypeTable->ClassName ?>Id) {
        switch ($int<?= $objTypeTable->ClassName ?>Id) {
<?php foreach ($objTypeTable->NameArray as $intKey=>$strValue) { ?>
            case <?= $intKey ?>: return t('<?= $strValue ?>');
<?php } ?>
            default:
                throw new Caller(sprintf('Invalid int<?= $objTypeTable->ClassName ?>Id: %s', $int<?= $objTypeTable->ClassName ?>Id));
        }
    }

    /**
     * Returns the constant name corresponding to the given id.
     *
     * @param integer $int<?= $objTypeTable->ClassName ?>Id
     * @throws Caller
     * @returns string
     */
    public static function toToken($int<?= $objTypeTable->ClassName ?>Id) {
        switch ($int<?= $objTypeTable->ClassName ?>Id) {
<?php foreach ($objTypeTable->TokenArray as $intKey=>$strValue) { ?>
            case <?= $intKey ?>: return '<?= $strValue ?>';
<?php } ?>
            default:
                throw new Caller(sprintf('Invalid int<?= $objTypeTable->ClassName ?>Id: %s', $int<?= $objTypeTable->ClassName ?>Id));
        }
    }

<?php foreach ($objTypeTable->ExtraFieldsArray as $colData) { ?>
    /**
     * Get the associated <?php echo $colData['name']  ?> value by id.
     *
     * @param integer $int<?= $objTypeTable->ClassName ?>Id
     * @throws Caller
     * @returns <?= $colData['type'] ?><?php if ($colData['nullAllowed']) echo (' | null'); ?>

     */
    public static function to<?php echo $colData['name']  ?>($int<?php echo $objTypeTable->ClassName  ?>Id) {
        switch ($int<?php echo $objTypeTable->ClassName  ?>Id) {
<?php foreach ($objTypeTable->ExtraPropertyArray as $intKey=>$arrColumns) { ?>
            case <?php echo $intKey  ?>: return <?= \QCubed\Codegen\TypeTable::literal($arrColumns[$colData['name']]) ?>;
<?php } ?>
            default:
                throw new Caller(sprintf('Invalid int<?php echo $objTypeTable->ClassName  ?>Id: %s', $int<?php echo $objTypeTable->ClassName  ?>Id));
        }
    }

<?php } ?>


    ///////////////////////////////
    // INSTANTIATION-RELATED METHODS
    ///////////////////////////////

    /**
     * Instantiate a <?= $objTypeTable->ClassName ?> from a Database Row.
     * Simply returns the integer id corresponding to this item.
     * Takes in an optional strAliasPrefix, used in case another Object::InstantiateDbRow
     * is calling this <?= $objTypeTable->ClassName ?>::InstantiateDbRow in order to perform
     * early binding on referenced objects.
     * @param \QCubed\Database\RowBase $objDbRow
     * @param string|null $strAliasPrefix
     * @param string|null $strExpandAsArrayNodes
     * @param array|null $arrPreviousItems
     * @param string[] $strColumnAliasArray
     * @return <?= $objTypeTable->ClassName ?>

    */
    public static function instantiateDbRow($objDbRow, $strAliasPrefix = null, $strExpandAsArrayNodes = null, $arrPreviousItems = null, $strColumnAliasArray = array()) {
        // If blank row, return null
        if (!$objDbRow) {
            return null;
        }
        $strAlias = $strAliasPrefix . 'id';
        $strAliasName = array_key_exists($strAlias, $strColumnAliasArray) ? $strColumnAliasArray[$strAlias] : $strAlias;
        $intId = $objDbRow->GetColumn($strAliasName, \QCubed\Database\FieldType::INTEGER);
        return $intId;
    }
}


/**
 * @property-read Node\Column $Id
 * @property-read Node\Column $_PrimaryKeyNode
 **/
class Node<?= $objTypeTable->ClassName ?> extends Node\Table {
    protected $strTableName = '<?= $objTypeTable->Name ?>';
    protected $strPrimaryKey = 'id';
    protected $strClassName = '<?= $objTypeTable->ClassName ?>';
    protected $blnIsType = true;

   /**
    * Returns the names of the available fields to query against this node.
    * @returns string[]
    */
    public function fields() {
        return ["id", "name"];
    }

   /**
    * Returns the field names of the primary key field(s)
    * @returns string[]
    */
    public function primaryKeyFields() {
        return ["id"];
    }

    /**
     * @param string $strName
     * @return mixed
     * @throws Exception
     */
    public function __get($strName) {
        switch ($strName) {
            case 'Id':
                return new Node\Column('id', 'Id', 'Integer', $this);
            case '_PrimaryKeyNode':
                return new Node\Column('id', 'Id', 'Integer', $this);
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
