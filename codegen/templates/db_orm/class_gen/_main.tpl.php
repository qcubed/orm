<?php
	/** @var QSqlTable $objTable */
	/** @var \QCubed\Codegen\DatabaseCodegen $objCodegen */
	global $_TEMPLATE_SETTINGS;
	$_TEMPLATE_SETTINGS = array(
		'OverwriteFlag' => true,
		'DirectorySuffix' => '',
		'TargetDirectory' => QCUBED_PROJECT_MODEL_GEN_DIR,
		'TargetFileName' => $objTable->ClassName . 'Gen.php'
	);
?>
<?php print("<?php\n"); ?>
/**
 * Generated <?= $objTable->ClassName ?> base class file
 */

use QCubed\Query\QQ;
use QCubed\Query\Condition\ConditionInterface as iCondition;
use QCubed\Query\Clause\ClauseInterface as iClause;
use QCubed\Query\Node;
use QCubed\Exception\Caller;
use QCubed\Type;
use QCubed\QDateTime;
use QCubed\Query\ModelTrait;

/**
 * Class <?= $objTable->ClassName ?>Gen
 *
 * The abstract <?= $objTable->ClassName ?>Gen class defined here is
 * code-generated and contains all the basic CRUD-type functionality as well as
 * basic methods to handle relationships and index-based loading.
 *
 * To use, you should use the <?= $objTable->ClassName ?> subclass which
 * extends this <?= $objTable->ClassName ?>Gen class.
 *
 * Because subsequent re-code generations will overwrite any changes to this
 * file, you should leave this file unaltered to prevent yourself from losing
 * any information or code changes.  All customizations should be done by
 * overriding existing or implementing new methods, properties and variables
 * in the <?= $objTable->ClassName ?> class.
 *
 * @package <?= \QCubed\Project\Codegen\CodegenBase::$ApplicationName; ?>

 * @subpackage ModelGen
<?php include("property_comments.tpl.php"); ?>

 */
abstract class <?= $objTable->ClassName ?>Gen extends \QCubed\ObjectBase implements IteratorAggregate, JsonSerializable {

    use ModelTrait;

    /** @var boolean Set to false in superclass to save a little time if this db object should not be watched for changes. */
    public static $blnWatchChanges = true;

<?php if ($objTable->PrimaryKeyColumnArray)  { ?>
    /** @var <?= $objTable->ClassName ?>[] Short term cached <?= $objTable->ClassName ?> objects */
    protected static $objCacheArray = array();
<?php } ?>

<?php include("protected_member_variables.tpl.php"); ?>

<?php include("protected_member_objects.tpl.php"); ?>

<?php include("class_initialize.tpl.php"); ?>

<?php include("pk_support.tpl.php"); ?>

<?php include("class_load_and_count_methods.tpl.php"); ?>

<?php include("qcubed_query_methods.tpl.php"); ?>


<?php include("instantiation_methods.tpl.php"); ?>


<?php include("index_load_methods.tpl.php"); ?>




    //////////////////////////
    // SAVE, DELETE AND RELOAD
    //////////////////////////
    <?php include("object_save.tpl.php"); ?>

    <?php include("object_delete.tpl.php"); ?>


    <?php include("object_reload.tpl.php"); ?>

    ////////////////////
    // UTILITIES
    ////////////////////
    <?php include("array_indexers.tpl.php"); ?>

    <?php include("property_getters_setters.tpl.php"); ?>
    <?php include("copy.tpl.php"); ?>
    <?php include("broadcast_changes.tpl.php"); ?>


    ////////////////////
    // PUBLIC OVERRIDERS
    ////////////////////

    <?php include("property_get.tpl.php"); ?>


    <?php include("property_set.tpl.php"); ?>

    <?php include("virtual_attribute.tpl.php"); ?>

    <?php include("associated_objects_methods.tpl.php"); ?>


    <?php include("class_info.tpl.php"); ?>


    <?php include("soap_methods.tpl.php"); ?>


    <?php include("json_methods.tpl.php"); ?>


    <?php include("custom_funcs.tpl.php"); // Stub file. Default is empty. Create one in your project/includes/codegen/templates/db_orm/class_gen directory to add your custom functions.?>


}



<?php include("qcubed_query_classes.tpl.php"); ?>