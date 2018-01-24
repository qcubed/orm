<?php

use QCubed\Project\Codegen\CodegenBase as Codegen;
use QCubed\QString;

//$__CONFIG_ONLY__ = true;

define('QCUBED_CODE_GENERATING', true);

require_once(__DIR__ . '/qcubed.inc.php');

if (!defined('QCUBED_URL_PREFIX')) {
    echo "Cannot find the configuration file. Make sure your qcubed.inc.php file is installed correctly."; exit;
}
if (QCUBED_URL_PREFIX == '{ url_prefix }') {
    // config file has not been set up correctly
    // what should it be?
    $uri = $_SERVER['REQUEST_URI'];
    $offset = strrpos ($uri, '/vendor');
    echo "Your config file is not set up correcly. In particular, look in the project/includes/configuration/active/config.cfg.php file and change the '{ url_prefix }' to '";
    echo substr($uri, 0, $offset);
    echo "'";
    exit;
}


$strOrmPath = dirname(__DIR__);
$strQCubedPath = dirname($strOrmPath);
$loader = require(dirname($strQCubedPath) . '/autoload.php'); // Add the Composer autoloader if using Composer
$loader->addPsr4('QCubed\\', $strOrmPath . '/../common/src');
$loader->addPsr4('QCubed\\', $strOrmPath . '/src');

\QCubed\Database\Service::initializeDatabaseConnections();

// Load in the Project classes
$loader->addPsr4('QCubed\\Project\\', QCUBED_PROJECT_DIR . '/qcubed'); // make sure user side codegen is included


/////////////////////////////////////////////////////
// Run CodeGen, using the ./codegen_settings.xml file
/////////////////////////////////////////////////////
Codegen::run(QCUBED_CONFIG_DIR . '/codegen_settings.xml');

function displayMonospacedText($strText)
{
    $strText = QString::htmlEntities($strText);
    $strText = str_replace('	', '    ', $strText);
    $strText = str_replace(' ', '&nbsp;', $strText);
    $strText = str_replace("\r", '', $strText);
    $strText = str_replace("\n", '<br/>', $strText);

    echo($strText);
}

$strPageTitle = "QCubed Development Framework - Code Generator";
?>
<h1>Code Generator</h1>
<div class="headerLine"><span><strong>PHP Version:</strong> <?php echo(PHP_VERSION); ?>;&nbsp;&nbsp;<strong>Zend Engine Version:</strong> <?php echo(zend_version()); ?>
        ;&nbsp;&nbsp;<strong>QCubed Version:</strong> <?php echo(QCUBED_VERSION); ?></span></div>

<div class="headerLine"><span><?php if (array_key_exists('OS', $_SERVER)) {
            printf('<strong>Operating System:</strong> %s;&nbsp;&nbsp;', $_SERVER['OS']);
        } ?><strong>Application:</strong> <?php echo($_SERVER['SERVER_SOFTWARE']); ?>
        ;&nbsp;&nbsp;<strong>Server Name:</strong> <?php echo($_SERVER['SERVER_NAME']); ?></span></div>

<div class="headerLine"><span><strong>Code Generated:</strong> <?php echo(date('l, F j Y, g:i:s A')); ?></span></div>

<?php if (Codegen::$TemplatePaths) { ?>
    <div>
        <p><strong>Template Paths</strong></p>
        <pre><code><?php DisplayMonospacedText(implode("\r\n", Codegen::$TemplatePaths)); ?></code></pre>
    </div>
<?php } ?>

<div>
    <?php if ($strErrors = Codegen::$RootErrors) { ?>
        <p><strong>The following root errors were reported:</strong></p>
        <pre><code><?php DisplayMonospacedText($strErrors); ?></code></pre>
    <?php } else { ?>
        <p><strong>CodeGen Settings (as evaluated from <?php echo(Codegen::$SettingsFilePath); ?>):</strong></p>
        <pre><code><?php DisplayMonospacedText(Codegen::getSettingsXml()); ?></code></pre>
    <?php } ?>

    <?php foreach (Codegen::$CodeGenArray as $objCodeGen) { ?>
        <p><strong><?= QString::htmlEntities($objCodeGen->getTitle()); ?></strong></p>
        <pre><code><p class="code_title"><?php QString::htmlEntities($objCodeGen->getReportLabel()); ?></p><?php
                    DisplayMonospacedText($objCodeGen->generateAll());
                ?>
                <?php if ($strErrors = $objCodeGen->Errors) { ?>
                    <p class="code_title">The following errors were reported:</p>
                    <?php DisplayMonospacedText($objCodeGen->Errors); ?>
                <?php } ?>
                <?php if ($strWarnings = $objCodeGen->Warnings) { ?>
                    <p class="code_title">The following warnings were reported:</p>
<?php DisplayMonospacedText($objCodeGen->Warnings); ?>
<?php } ?></code></pre>
    <?php } ?>

    <?php
    if (!$strErrors) {
        foreach (Codegen::generateAggregate() as $strMessage) { ?>
            <p><strong><?php QString::htmlEntities($strMessage); ?></strong></p>
        <?php }
    } ?>
</div>
