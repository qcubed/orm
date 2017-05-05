<?php

use QCubed\Project\CodeGen\CodegenBase as QCodegen;
use QCubed\QString;

$__CONFIG_ONLY__ = true;


require_once('qcubed.inc.php');

$strOrmPath = dirname(__DIR__);
$strQCubedPath = dirname($strOrmPath);
$loader = require(dirname($strQCubedPath) . '/autoload.php'); // Add the Composer autoloader if using Composer

$loader->addPsr4('QCubed\\', $strOrmPath . '/../common/src');
$loader->addPsr4('QCubed\\Database\\', $strOrmPath . '/src/database');
$loader->addPsr4('QCubed\\Query\\', $strOrmPath . '/src/query');
$loader->addPsr4('QCubed\\Codegen\\', $strOrmPath . '/src/codegen');

\QCubed\Database\Service::InitializeDatabaseConnections();

// Load in the Project classes
$loader->addPsr4('QCubed\\Project\\', __PROJECT__ . '/qcubed'); // make sure user side codegen is included


/////////////////////////////////////////////////////
// Run CodeGen, using the ./codegen_settings.xml file
/////////////////////////////////////////////////////
QCodegen::Run(__CONFIGURATION__ . '/codegen_settings.xml');

function DisplayMonospacedText($strText)
{
    $strText = QString::HtmlEntities($strText);
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

<?php if (QCodegen::$TemplatePaths) { ?>
    <div>
        <p><strong>Template Paths</strong></p>
        <pre><code><?php DisplayMonospacedText(implode("\r\n", QCodegen::$TemplatePaths)); ?></code></pre>
    </div>
<?php } ?>

<div>
    <?php if ($strErrors = QCodegen::$RootErrors) { ?>
        <p><strong>The following root errors were reported:</strong></p>
        <pre><code><?php DisplayMonospacedText($strErrors); ?></code></pre>
    <?php } else { ?>
        <p><strong>CodeGen Settings (as evaluated from <?php echo(QCodegen::$SettingsFilePath); ?>):</strong></p>
        <pre><code><?php DisplayMonospacedText(QCodegen::GetSettingsXml()); ?></code></pre>
    <?php } ?>

    <?php foreach (QCodegen::$CodeGenArray as $objCodeGen) { ?>
        <p><strong><?= QString::HtmlEntities($objCodeGen->GetTitle()); ?></strong></p>
        <pre><code><p class="code_title"><?php QString::HtmlEntities($objCodeGen->GetReportLabel()); ?></p><?php
                if (QCodegen::DEBUG_MODE) {
                    DisplayMonospacedText($objCodeGen->GenerateAll());
                } else {
                    @DisplayMonospacedText($objCodeGen->GenerateAll());
                }
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
        foreach (QCodegen::GenerateAggregate() as $strMessage) { ?>
            <p><strong><?php QString::HtmlEntities($strMessage); ?></strong></p>
        <?php }
    } ?>
</div>
