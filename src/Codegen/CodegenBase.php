<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Codegen;

use QCubed\Error;
use QCubed\Exception\Caller;
use QCubed\Folder;
use QCubed\ObjectBase;
use QCubed\QString;
use QCubed\Database;
use QCubed\Project\Codegen\CodegenBase as Codegen;
use QCubed\Type;

function qcubedHandleCodeGenParseError($__exc_errno, $__exc_errstr, $__exc_errfile, $__exc_errline)
{
    $strErrorString = str_replace("SimpleXMLElement::__construct() [<a href='function.SimpleXMLElement---construct'>function.SimpleXMLElement---construct</a>]: ",
        '', $__exc_errstr);
    Codegen::$RootErrors .= sprintf("%s\r\n", $strErrorString);
}

// returns true if $str begins with $sub
function beginsWith($str, $sub)
{
    return (substr($str, 0, strlen($sub)) == $sub);
}

// return tru if $str ends with $sub
function endsWith($str, $sub)
{
    return (substr($str, strlen($str) - strlen($sub)) == $sub);
}

// trims off x chars from the front of a string
// or the matching string in $off is trimmed off
function trimOffFront($off, $str)
{
    if (is_numeric($off)) {
        return substr($str, $off);
    } else {
        return substr($str, strlen($off));
    }
}

// trims off x chars from the end of a string
// or the matching string in $off is trimmed off
function trimOffEnd($off, $str)
{
    if (is_numeric($off)) {
        return substr($str, 0, strlen($str) - $off);
    } else {
        return substr($str, 0, strlen($str) - strlen($off));
    }
}

/**
 * This is the CodeGen class which performs the code generation
 * for both the Object-Relational Model (e.g. Data Objects) as well as
 * the draft Forms, which make up simple HTML/PHP scripts to perform
 * basic CRUD functionality on each object.
 * @package Codegen
 * @property string $Errors List of errors
 * @property string $Warnings List of warnings
 */
abstract class CodegenBase extends ObjectBase
{
    // Class Name Suffix/Prefix
    /** @var string Class Prefix, as specified in the codegen_settings.xml file */
    protected $strClassPrefix;
    /** @var string Class suffix, as specified in the codegen_settings.xml file */
    protected $strClassSuffix;

    /** @var string Errors and Warnings collected during the process of codegen * */
    protected $strErrors;

    /** @var string Warnings collected during the codegen process. */
    protected $strWarnings;

    /**
     * PHP Reserved Words.  They make up:
     * Invalid Type names -- these are reserved words which cannot be Type names in any user type table
     * Invalid Table names -- these are reserved words which cannot be used as any table name
     * Please refer to : http://php.net/manual/en/reserved.php
     */
    const PHP_RESERVED_WORDS = 'new, null, break, return, switch, self, case, const, clone, continue, declare, default, echo, else, elseif, empty, exit, eval, if, try, throw, catch, public, private, protected, function, extends, foreach, for, while, do, var, class, static, abstract, isset, unset, implements, interface, instanceof, include, include_once, require, require_once, abstract, and, or, xor, array, list, false, true, global, parent, print, exception, namespace, goto, final, endif, endswitch, enddeclare, endwhile, use, as, endfor, endforeach, this';

    /**
     * @var array The list of template base paths to search, in order, when looking for a particular template. Set this
     * to insert new template paths. If not set, the default will be the project template path, following by the qcubed core path.
     */
    public static $TemplatePaths;

    /**
     * DebugMode -- for Template Developers
     * This will output the current evaluated template/statement to the screen
     * On "eval" errors, you can click on the "View Rendered Page" to see what currently
     * is being evaluated, which should hopefully aid in template debugging.
     */
    const DEBUG_MODE = false;

    /**
     * This static array contains an array of active and executed codegen objects, based
     * on the XML Configuration passed in to Run()
     *
     * @var Codegen[] array of active/executed codegen objects
     */
    public static $CodeGenArray;

    /**
     * This is the array representation of the parsed SettingsXml
     * for reportback purposes.
     *
     * @var string[] array of config settings
     */
    protected static $SettingsXmlArray;

    /**
     * This is the SimpleXML representation of the Settings XML file
     *
     * @var \SimpleXmlElement the XML representation
     */
    protected static $SettingsXml;

    public static $SettingsFilePath;

    /**
     * Application Name (from CodeGen Settings)
     *
     * @var string $ApplicationName
     */
    public static $ApplicationName;

    /**
     * Preferred Render Method (from CodeGen Settings)
     *
     * @var string $PreferredRenderMethod
     */
    public static $PreferredRenderMethod;

    /**
     * Create Method (from CodeGen Settings)
     *
     * @var string $CreateMethod
     */
    public static $CreateMethod;

    /**
     * Default Button Class (from CodeGen Settings)
     *
     * @var string $DefaultButtonClass
     */
    public static $DefaultButtonClass;

    public static $RootErrors = '';

    /**
     * @var string[] array of directories to be excluded in codegen (lower cased)
     * @access protected
     */
    protected static $DirectoriesToExcludeArray = array('.', '..', '.svn', 'svn', 'cvs', '.git');

    /**
     * Returns prefix for variable according to variable type
     *
     * @param string $strType The type of variable for which the prefix is needed
     *
     * @return string The variable prefix
     *
     * @was QString::PrefixFromType
     */
    public static function prefixFromType($strType)
    {
        switch ($strType) {
            case Type::ARRAY_TYPE:
                return "obj";
            case Type::BOOLEAN:
                return "bln";
            case Type::DATE_TIME:
                return "dtt";
            case Type::FLOAT:
                return "flt";
            case Type::INTEGER:
                return "int";
            case Type::OBJECT:
                return "obj";
            case Type::STRING:
                return "str";
        }
        // Suppressing the IDE warning about no value being return
        return "";
    }

    /**
     * Return an array of paths to template files. This base class versions searches a config directory for pointers
     * to template files to use. This allows qcubed repos to inject templates into the codegen process.
     *
     * This process is similar to how the control registry works.
     */
    public function getInstalledTemplatePaths()
    {
        $dir = QCUBED_CONFIG_DIR . '/templates';

        $paths = [];

        if ($dir !== false) {    // does the active directory exist?
            foreach (scandir($dir) as $strFileName) {
                if (substr($strFileName, -8) == '.inc.php') {
                    $paths2 = include($dir . '/' . $strFileName);
                    if ($paths2 && is_array($paths2)) {
                        $paths = array_merge($paths, $paths2);
                    }
                }
            }
        }

        return $paths;
    }


    /**
     * Gets the settings in codegen_settings.xml file and returns its text without comments
     * @return string
     */
    public static function getSettingsXml()
    {
        $strCrLf = "\r\n";

        $strToReturn = sprintf('<codegen>%s', $strCrLf);
        $strToReturn .= sprintf('	<name application="%s"/>%s', Codegen::$ApplicationName, $strCrLf);
        $strToReturn .= sprintf('	<render preferredRenderMethod="%s"/>%s', Codegen::$PreferredRenderMethod,
            $strCrLf);
        $strToReturn .= sprintf('	<dataSources>%s', $strCrLf);
        foreach (Codegen::$CodeGenArray as $objCodeGen) {
            $strToReturn .= $strCrLf . $objCodeGen->getConfigXml();
        }
        $strToReturn .= sprintf('%s	</dataSources>%s', $strCrLf, $strCrLf);
        $strToReturn .= '</codegen>';

        return $strToReturn;
    }

    /**
     * The function which actually performs the steps for code generation
     * Code generation begins here.
     * @param string $strSettingsXmlFilePath Path to the settings file
     */
    public static function run($strSettingsXmlFilePath)
    {
        if (!defined('QCUBED_CODE_GENERATING')) {
            define('QCUBED_CODE_GENERATING', true);
        }

        Codegen::$CodeGenArray = array();
        Codegen::$SettingsFilePath = $strSettingsXmlFilePath;

        if (!file_exists($strSettingsXmlFilePath)) {
            Codegen::$RootErrors = 'FATAL ERROR: CodeGen Settings XML File (' . $strSettingsXmlFilePath . ') was not found.';
            return;
        }

        if (!is_file($strSettingsXmlFilePath)) {
            Codegen::$RootErrors = 'FATAL ERROR: CodeGen Settings XML File (' . $strSettingsXmlFilePath . ') was not found.';
            return;
        }

        // Try Parsing the Xml Settings File
        try {
            $errorHandler = new Error\Handler('\\QCubed\\Codegen\\QcubedHandleCodeGenParseError', E_ALL);
            Codegen::$SettingsXml = new \SimpleXMLElement(file_get_contents($strSettingsXmlFilePath));
            $errorHandler->restore();
        } catch (\Exception $objExc) {
            Codegen::$RootErrors .= 'FATAL ERROR: Unable to parse CodeGenSettings XML File: ' . $strSettingsXmlFilePath;
            Codegen::$RootErrors .= "\r\n";
            Codegen::$RootErrors .= $objExc->getMessage();
            return;
        }

        // Application Name
        Codegen::$ApplicationName = Codegen::lookupSetting(Codegen::$SettingsXml, 'name', 'application');

        // Codegen Defaults
        Codegen::$PreferredRenderMethod = Codegen::lookupSetting(Codegen::$SettingsXml, 'formgen',
            'preferredRenderMethod');
        Codegen::$CreateMethod = Codegen::lookupSetting(Codegen::$SettingsXml, 'formgen', 'createMethod');
        Codegen::$DefaultButtonClass = Codegen::lookupSetting(Codegen::$SettingsXml, 'formgen', 'buttonClass');

        if (!Codegen::$DefaultButtonClass) {
            Codegen::$RootErrors .= "CodeGen Settings XML Fatal Error: buttonClass was not defined\r\n";
            return;
        }

        // Iterate Through DataSources
        if (Codegen::$SettingsXml->dataSources->asXML()) {
            foreach (Codegen::$SettingsXml->dataSources->children() as $objChildNode) {
                switch (dom_import_simplexml($objChildNode)->nodeName) {
                    case 'database':
                        Codegen::$CodeGenArray[] = new DatabaseCodeGen($objChildNode);
                        break;
                    case 'restService':
                        Codegen::$CodeGenArray[] = new RestServiceCodeGen($objChildNode);
                        break;
                    default:
                        Codegen::$RootErrors .= sprintf("Invalid Data Source Type in CodeGen Settings XML File (%s): %s\r\n",
                            $strSettingsXmlFilePath, dom_import_simplexml($objChildNode)->nodeName);
                        break;
                }
            }
        }
    }

    /**
     * This will lookup either the node value (if no attributename is passed in) or the attribute value
     * for a given Tag.  Node Searches only apply from the root level of the configuration XML being passed in
     * (e.g. it will not be able to lookup the tag name of a grandchild of the root node)
     *
     * If No Tag Name is passed in, then attribute/value lookup is based on the root node, itself.
     *
     * @param \SimpleXmlElement $objNode
     * @param string $strTagName
     * @param string $strAttributeName
     * @param string $strType
     * @return mixed the return type depends on the Type you pass in to $strType
     */
    static public function lookupSetting($objNode, $strTagName, $strAttributeName = null, $strType = Type::STRING)
    {
        if ($strTagName) {
            $objNode = $objNode->$strTagName;
        }

        if ($strAttributeName) {
            switch ($strType) {
                case Type::INTEGER:
                    try {
                        $intToReturn = Type::cast($objNode[$strAttributeName], Type::INTEGER);
                        return $intToReturn;
                    } catch (\Exception $objExc) {
                        return null;
                    }
                case Type::BOOLEAN:
                    try {
                        $blnToReturn = Type::cast($objNode[$strAttributeName], Type::BOOLEAN);
                        return $blnToReturn;
                    } catch (\Exception $objExc) {
                        return null;
                    }
                default:
                    $strToReturn = trim(Type::cast($objNode[$strAttributeName], Type::STRING));
                    return $strToReturn;
            }
        } else {
            $strToReturn = trim(Type::cast($objNode, Type::STRING));
            return $strToReturn;
        }
    }

    /**
     *
     * @return array
     */
    public static function generateAggregate()
    {
        $objDbOrmCodeGen = array();
        $objRestServiceCodeGen = array();

        foreach (Codegen::$CodeGenArray as $objCodeGen) {
            if ($objCodeGen instanceof DatabaseCodeGen) {
                array_push($objDbOrmCodeGen, $objCodeGen);
            }
            if ($objCodeGen instanceof RestServiceCodeGen) {
                array_push($objRestServiceCodeGen, $objCodeGen);
            }
        }

        $strToReturn = array();
        array_merge($strToReturn, DatabaseCodeGen::generateAggregateHelper($objDbOrmCodeGen));
//			array_push($strToReturn, QRestServiceCodeGen::generateAggregateHelper($objRestServiceCodeGen));

        return $strToReturn;
    }

    /**
     * Given a template prefix (e.g. db_orm_, db_type_, rest_, soap_, etc.), pull
     * all the _*.tpl templates from any subfolders of the template prefix
     * in Codegen::TemplatesPath and Codegen::TemplatesPathCustom,
     * and call GenerateFile() on each one.  If there are any template files that reside
     * in BOTH TemplatesPath AND TemplatesPathCustom, then only use the TemplatesPathCustom one (which
     * in essence overrides the one in TemplatesPath)
     *
     * @param string $strTemplatePrefix the prefix of the templates you want to generate against
     * @param mixed[] $mixArgumentArray array of arguments to send to EvaluateTemplate
     *
     * @throws \Exception
     * @throws Caller
     * @return boolean success/failure on whether or not all the files generated successfully
     */
    public function generateFiles($strTemplatePrefix, $mixArgumentArray)
    {
        // If you are editing core templates, and getting EOF errors only on the travis build, this may be your problem. Scan your files and remove short tags.
        if (Codegen::DEBUG_MODE && ini_get('short_open_tag')) {
            _p("Warning: PHP directive short_open_tag is on. Using short tags will cause unexpected EOF on travis build.\n",
                false);
        }

        // validate the template paths
        foreach (static::$TemplatePaths as $strPath) {
            if (!is_dir($strPath)) {
                throw new \Exception(sprintf("Template path: %s does not appear to be a valid directory.", $strPath));
            }
        }

        // Create an array of arrays of standard templates and custom (override) templates to process
        // Index by [module_name][filename] => true/false where
        // module name (e.g. "class_gen", "form_delegates) is name of folder within the prefix (e.g. "db_orm")
        // filename is the template filename itself (in a _*.tpl format)
        // true = override (use custom) and false = do not override (use standard)
        $strTemplateArray = array();

        // Go through standard templates first, then override in order
        foreach (static::$TemplatePaths as $strPath) {
            $this->buildTemplateArray($strPath . $strTemplatePrefix, $strTemplateArray);
        }

        // Finally, iterate through all the TemplateFiles and call GenerateFile to Evaluate/Generate/Save them
        $blnSuccess = true;
        foreach ($strTemplateArray as $strModuleName => $strFileArray) {
            foreach ($strFileArray as $strFilename => $strPath) {
                if (!$this->generateFile($strTemplatePrefix . '/' . $strModuleName, $strPath, $mixArgumentArray)) {
                    $blnSuccess = false;
                }
            }
        }

        return $blnSuccess;
    }

    protected function buildTemplateArray($strTemplateFilePath, &$strTemplateArray)
    {
        if (!$strTemplateFilePath) {
            return;
        }
        if (substr($strTemplateFilePath, -1) != '/') {
            $strTemplateFilePath .= '/';
        }
        if (is_dir($strTemplateFilePath)) {
            $objDirectory = opendir($strTemplateFilePath);
            while ($strModuleName = readdir($objDirectory)) {
                if (!in_array(strtolower($strModuleName), Codegen::$DirectoriesToExcludeArray) &&
                    is_dir($strTemplateFilePath . $strModuleName)
                ) {
                    $objModuleDirectory = opendir($strTemplateFilePath . $strModuleName);
                    while ($strFilename = readdir($objModuleDirectory)) {
                        if ((QString::firstCharacter($strFilename) == '_') &&
                            (substr($strFilename, strlen($strFilename) - 8) == '.tpl.php')
                        ) {
                            $strTemplateArray[$strModuleName][$strFilename] = $strTemplateFilePath . $strModuleName . '/' . $strFilename;
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns the settings of the template file as SimpleXMLElement object
     *
     * @param null|string $strTemplateFilePath Path to the file
     * @param null|string $strTemplate Text of the template (if $strTemplateFilePath is null, this field must be string)
     * @deprecated
     *
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    protected function getTemplateSettings($strTemplateFilePath, &$strTemplate = null)
    {
        if ($strTemplate === null) {
            $strTemplate = file_get_contents($strTemplateFilePath);
        }
        $strError = 'Template\'s first line must be <template OverwriteFlag="boolean" TargetDirectory="string" DirectorySuffix="string" TargetFileName="string"/>: ' . $strTemplateFilePath;
        // Parse out the first line (which contains path and overwriting information)
        $intPosition = strpos($strTemplate, "\n");
        if ($intPosition === false) {
            throw new \Exception($strError);
        }

        $strFirstLine = trim(substr($strTemplate, 0, $intPosition));

        $objTemplateXml = null;
        // Attempt to Parse the First Line as XML
        try {
            @$objTemplateXml = new \SimpleXMLElement($strFirstLine);
        } catch (\Exception $objExc) {
        }

        if (is_null($objTemplateXml) || (!($objTemplateXml instanceof \SimpleXMLElement))) {
            throw new \Exception($strError);
        }
        $strTemplate = substr($strTemplate, $intPosition + 1);
        return $objTemplateXml;
    }

    /**
     * Generates a php code using a template file
     *
     * @param string $strModuleSubPath
     * @param string $strTemplateFilePath Path to the template file
     * @param mixed[] $mixArgumentArray
     * @param boolean $blnSave whether or not to actually perform the save
     *
     * @throws Caller
     * @throws \Exception
     * @return mixed returns the evaluated template or boolean save success.
     */
    public function generateFile($strModuleSubPath, $strTemplateFilePath, $mixArgumentArray, $blnSave = true)
    {
        // Setup Debug/Exception Message
        if (Codegen::DEBUG_MODE) {
            echo("Evaluating $strTemplateFilePath<br/>");
        }

        // Check to see if the template file exists, and if it does, Load It
        if (!file_exists($strTemplateFilePath)) {
            throw new Caller('Template File Not Found: ' . $strTemplateFilePath);
        }

        // Evaluate the Template
        // make sure paths are set up to pick up included files from the various directories.
        // Must be the reverse of the buildTemplateArray order
        $a = array();
        foreach (static::$TemplatePaths as $strTemplatePath) {
            array_unshift($a, $strTemplatePath . $strModuleSubPath);
        }
        $strSearchPath = implode(PATH_SEPARATOR, $a) . PATH_SEPARATOR . get_include_path();
        $strOldIncludePath = set_include_path($strSearchPath);
        if ($strSearchPath != get_include_path()) {
            throw new Caller ('Can\'t override include path. Make sure your apache or server settings allow include paths to be overridden. ');
        }

        $strTemplate = $this->evaluatePHP($strTemplateFilePath, $mixArgumentArray, $templateSettings);
        set_include_path($strOldIncludePath);

        $blnOverwriteFlag = Type::cast($templateSettings['OverwriteFlag'], Type::BOOLEAN);
        $strTargetDirectory = Type::cast($templateSettings['TargetDirectory'], Type::STRING);
        $strDirectorySuffix = Type::cast($templateSettings['DirectorySuffix'], Type::STRING);
        $strTargetFileName = Type::cast($templateSettings['TargetFileName'], Type::STRING);

        if (is_null($blnOverwriteFlag) || is_null($strTargetFileName) || is_null($strTargetDirectory) || is_null($strDirectorySuffix)) {
            throw new \Exception('the template settings cannot be null');
        }

        if ($blnSave && $strTargetDirectory) {
            // Figure out the REAL target directory
            $strTargetDirectory = $strTargetDirectory . $strDirectorySuffix;

            // Create Directory (if needed)
            if (!is_dir($strTargetDirectory)) {
                if (!Folder::makeDirectory($strTargetDirectory, 0777)) {
                    throw new \Exception('Unable to mkdir ' . $strTargetDirectory);
                }
            }

            // Save to Disk
            $strFilePath = sprintf('%s/%s', $strTargetDirectory, $strTargetFileName);
            if ($blnOverwriteFlag || (!file_exists($strFilePath))) {
                $intBytesSaved = file_put_contents($strFilePath, $strTemplate);

                $this->setGeneratedFilePermissions($strFilePath);
                return ($intBytesSaved == strlen($strTemplate));
            } else // Becuase we are not supposed to overwrite, we should return "true" by default
            {
                return true;
            }
        }

        // Why Did We Not Save?
        if ($blnSave) {
            // We WANT to Save, but QCubed Configuration says that this functionality/feature should no longer be generated
            // By definition, we should return "true"
            return true;
        }
        // Running GenerateFile() specifically asking it not to save -- so return the evaluated template instead
        return $strTemplate;
    }

    /**
     * Sets the file permissions (Linux only) for a file generated by the Code Generator
     * @param string $strFilePath Path of the generated file
     *
     * @throws Caller
     */
    protected function setGeneratedFilePermissions($strFilePath)
    {
        // CHMOD to full read/write permissions (applicable only to nonwindows)
        // Need to ignore error handling for this call just in case
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $e = new Error\Handler();
            chmod($strFilePath, 0666);
        }
    }

    /**
     * Returns the evaluated PHP
     *
     * @param $strFilename
     * @param $mixArgumentArray
     * @param null $templateSettings
     * @return mixed|string
     */
    protected function evaluatePHP($strFilename, $mixArgumentArray, &$templateSettings = null)
    {
        // Get all the arguments and set them locally
        if ($mixArgumentArray) {
            foreach ($mixArgumentArray as $strName => $mixValue) {
                $$strName = $mixValue;
            }
        }
        global $_TEMPLATE_SETTINGS;
        unset($_TEMPLATE_SETTINGS);
        $_TEMPLATE_SETTINGS = null;

        // Of course, we also need to locally allow "objCodeGen"
        $objCodeGen = $this;

        // Get Database Escape Identifiers
        $strEscapeIdentifierBegin = \QCubed\Database\Service::getDatabase($this->intDatabaseIndex)->EscapeIdentifierBegin;
        $strEscapeIdentifierEnd = \QCubed\Database\Service::getDatabase($this->intDatabaseIndex)->EscapeIdentifierEnd;

        // Store the Output Buffer locally
        $strAlreadyRendered = ob_get_contents();

        if (ob_get_level()) {
            ob_clean();
        }
        ob_start();
        include($strFilename);
        $strTemplate = ob_get_contents();
        ob_end_clean();

        $templateSettings = $_TEMPLATE_SETTINGS;
        unset($_TEMPLATE_SETTINGS);

        // Restore the output buffer and return evaluated template
        print($strAlreadyRendered);

        // Remove all \r from the template (for Win/*nix compatibility)
        $strTemplate = str_replace("\r", '', $strTemplate);
        return $strTemplate;
    }

    ///////////////////////
    // COMMONLY OVERRIDDEN CONVERSION FUNCTIONS
    ///////////////////////

    protected function stripPrefixFromTable($strTableName)
    {
    }

    /**
     * Given a table name, returns the name of the class for the corresponding model object.
     *
     * @param string $strTableName
     * @return string
     */
    protected function modelClassName($strTableName)
    {
        $strTableName = $this->stripPrefixFromTable($strTableName);
        return sprintf('%s%s%s',
            $this->strClassPrefix,
            QString::camelCaseFromUnderscore($strTableName),
            $this->strClassSuffix);
    }

    /**
     * Given a table name, returns a variable name that will be used to represent the corresponding model object.
     * @param string $strTableName
     * @return string
     */
    public function modelVariableName($strTableName)
    {
        $strTableName = $this->stripPrefixFromTable($strTableName);
        return Codegen::prefixFromType(Type::OBJECT) .
            QString::camelCaseFromUnderscore($strTableName);
    }

    /**
     * Given a table name, returns the variable name that will be used to refer to the object in a
     * reverse reference context (many-to-one).
     * @param string $strTableName
     * @return string
     */
    protected function modelReverseReferenceVariableName($strTableName)
    {
        $strTableName = $this->stripPrefixFromTable($strTableName);
        return $this->modelVariableName($strTableName);
    }

    /**
     * Given a table name, returns the variable type of the object in a
     * reverse reference context (many-to-one).
     * @param $strTableName
     * @return string
     */
    protected function modelReverseReferenceVariableType($strTableName)
    {
        $strTableName = $this->stripPrefixFromTable($strTableName);
        return $this->modelClassName($strTableName);
    }


    /**
     * Given a column, returns the name of the variable used to represent the column's value inside
     * the model object.
     *
     * @param SqlColumn $objColumn
     * @return string
     */
    protected function modelColumnVariableName(SqlColumn $objColumn)
    {
        return Codegen::prefixFromType($objColumn->VariableType) .
            QString::camelCaseFromUnderscore($objColumn->Name);
    }

    /**
     * Return the name of the property corresponding to the given column name as used in the getter and setter of
     * the model object.
     * @param string $strColumnName
     * @return string
     */
    protected function modelColumnPropertyName($strColumnName)
    {
        return QString::camelCaseFromUnderscore($strColumnName);
    }

    /**
     * Return the name of the property corresponding to the given column name as used in the getter and setter of
     * a Type object.
     * @param string $strColumnName Column name
     * @return string
     */
    protected function typeColumnPropertyName($strColumnName)
    {
        return QString::camelCaseFromUnderscore($strColumnName);
    }

    /**
     * Given the name of a column that is a foreign key to another table, returns a kind of
     * virtual column name that would refer to the object pointed to. This new name is used to refer to the object
     * version of the column by json and other encodings, and derivatives
     * of this name are used to represent a variable and property name that refers to this object that will get stored
     * in the model.
     *
     * @param string $strColumnName
     * @return string
     */
    protected function modelReferenceColumnName($strColumnName)
    {
        $intNameLength = strlen($strColumnName);

        // Does the column name for this reference column end in "_id"?
        if (($intNameLength > 3) && (substr($strColumnName, $intNameLength - 3) == "_id")) {
            // It ends in "_id" but we don't want to include the "Id" suffix
            // in the Variable Name.  So remove it.
            $strColumnName = substr($strColumnName, 0, $intNameLength - 3);
        } else {
            // Otherwise, let's add "_object" so that we don't confuse this variable name
            // from the variable that was mapped from the physical database
            // E.g., if it's a numeric FK, and the column is defined as "person INT",
            // there will end up being two variables, one for the Person id integer, and
            // one for the Person object itself.  We'll add Object to the name of the Person object
            // to make this deliniation.
            $strColumnName = sprintf("%s_object", $strColumnName);
        }

        return $strColumnName;
    }

    /**
     * Given a column name to a foreign key, returns the name of the variable that will represent the foreign object
     * stored in the model.
     *
     * @param string $strColumnName
     * @return string
     */
    protected function modelReferenceVariableName($strColumnName)
    {
        $strColumnName = $this->modelReferenceColumnName($strColumnName);
        return Codegen::prefixFromType(Type::OBJECT) .
            QString::camelCaseFromUnderscore($strColumnName);
    }

    /**
     * Given a column name to a foreign key, returns the name of the property that will be used in the getter and setter
     * to represent the foreign object stored in the model.
     *
     * @param string $strColumnName
     * @return string
     */
    protected function modelReferencePropertyName($strColumnName)
    {
        $strColumnName = $this->modelReferenceColumnName($strColumnName);
        return QString::camelCaseFromUnderscore($strColumnName);
    }

    protected function parameterCleanupFromColumn(SqlColumn $objColumn, $blnIncludeEquality = false)
    {
        if ($blnIncludeEquality) {
            return sprintf('$%s = $objDatabase->sqlVariable($%s, true);',
                $objColumn->VariableName, $objColumn->VariableName);
        } else {
            return sprintf('$%s = $objDatabase->sqlVariable($%s);',
                $objColumn->VariableName, $objColumn->VariableName);
        }
    }

    // To be used to list the columns as input parameters, or as parameters for sprintf
    protected function parameterListFromColumnArray($objColumnArray)
    {
        return $this->implodeObjectArray(', ', '$', '', 'VariableName', $objColumnArray);
    }

    protected function implodeObjectArray($strGlue, $strPrefix, $strSuffix, $strProperty, $objArrayToImplode)
    {
        $strArrayToReturn = array();
        if ($objArrayToImplode) {
            foreach ($objArrayToImplode as $objObject) {
                array_push($strArrayToReturn,
                    sprintf('%s%s%s', $strPrefix, $objObject->$strProperty, $strSuffix));
            }
        }

        return implode($strGlue, $strArrayToReturn);
    }

    protected function typeTokenFromTypeName($strName)
    {
        $strToReturn = '';
        for ($intIndex = 0; $intIndex < strlen($strName); $intIndex++) {
            if (((ord($strName[$intIndex]) >= ord('a')) &&
                    (ord($strName[$intIndex]) <= ord('z'))) ||
                ((ord($strName[$intIndex]) >= ord('A')) &&
                    (ord($strName[$intIndex]) <= ord('Z'))) ||
                ((ord($strName[$intIndex]) >= ord('0')) &&
                    (ord($strName[$intIndex]) <= ord('9'))) ||
                ($strName[$intIndex] == '_')
            ) {
                $strToReturn .= $strName[$intIndex];
            }
        }

        if (is_numeric(QString::firstCharacter($strToReturn))) {
            $strToReturn = '_' . $strToReturn;
        }
        return $strToReturn;
    }

    /**
     * Returns the control label name as used in the ModelConnector corresponding to this column or table.
     *
     * @param ColumnInterface $objColumn
     *
     * @return string
     */
    public static function modelConnectorControlName(ColumnInterface $objColumn)
    {
        if (($o = $objColumn->Options) && isset ($o['Name'])) { // Did developer default?
            return $o['Name'];
        }
        return QString::wordsFromCamelCase(Codegen::modelConnectorPropertyName($objColumn));
    }

    /**
     * The property name used in the ModelConnector for the given column, virtual column or table
     *
     * @param ColumnInterface $objColumn
     *
     * @return string
     * @throws \Exception
     */
    public static function modelConnectorPropertyName(ColumnInterface $objColumn)
    {
        if ($objColumn instanceof SqlColumn) {
            if ($objColumn->Reference) {
                return $objColumn->Reference->PropertyName;
            } else {
                return $objColumn->PropertyName;
            }
        } elseif ($objColumn instanceof ReverseReference) {
            if ($objColumn->Unique) {
                return ($objColumn->ObjectDescription);
            } else {
                return ($objColumn->ObjectDescriptionPlural);
            }
        } elseif ($objColumn instanceof ManyToManyReference) {
            return $objColumn->ObjectDescriptionPlural;
        } else {
            throw new \Exception ('Unknown column type.');
        }
    }

    /**
     * Return a variable name corresponding to the given column, including virtual columns like
     * ReverseReference and QManyToMany references.
     * @param ColumnInterface $objColumn
     * @return string
     */
    public function modelConnectorVariableName(ColumnInterface $objColumn)
    {
        $strPropName = static::modelConnectorPropertyName($objColumn);
        $objControlHelper = $this->getControlCodeGenerator($objColumn);
        return $objControlHelper->varName($strPropName);
    }

    /**
     * Returns a variable name for the "label" version of a control, which would be the read-only version
     * of viewing the data in the column.
     * @param ColumnInterface $objColumn
     * @return string
     */
    public function modelConnectorLabelVariableName(ColumnInterface $objColumn)
    {
        $strPropName = static::modelConnectorPropertyName($objColumn);
        return \QCubed\Codegen\Generator\Label::instance()->varName($strPropName);
    }

    /**
     * Returns the class for the control that will be created to edit the given column,
     * including the 'virtual' columns of reverse references (many to one) and many-to-many references.
     *
     * @param ColumnInterface $objColumn
     *
     * @return string Class name of control which can handle this column's data
     * @throws \Exception
     */
    protected function modelConnectorControlClass(ColumnInterface $objColumn)
    {

        // Is the class specified by the developer?
        if ($o = $objColumn->Options) {
            if (isset ($o['FormGen']) && $o['FormGen'] == \QCubed\ModelConnector\Options::FORMGEN_LABEL_ONLY) {
                return '\\QCubed\\Control\\Label';
            }
            if (isset($o['ControlClass'])) {
                return $o['ControlClass'];
            }
        }

        // otherwise, return the default class based on the column
        if ($objColumn instanceof SqlColumn) {
            if ($objColumn->Identity) {
                return '\\QCubed\\Control\\Label';
            }

            if ($objColumn->Timestamp) {
                return '\\QCubed\\Control\\Label';
            }

            if ($objColumn->Reference) {
                return '\\QCubed\\Project\\Control\\ListBox';
            }

            switch ($objColumn->VariableType) {
                case Type::BOOLEAN:
                    return '\\QCubed\\Project\\Control\\Checkbox';
                case Type::DATE_TIME:
                    return '\\QCubed\\Control\\DateTimePicker';
                case Type::INTEGER:
                    return '\\QCubed\\Control\\IntegerTextBox';
                case Type::FLOAT:
                    return '\\QCubed\\Control\\FloatTextBox';
                default:
                    return '\\QCubed\\Project\\Control\\TextBox';
            }
        } elseif ($objColumn instanceof ReverseReference) {
            if ($objColumn->Unique) {
                return '\\QCubed\\Project\\Control\\ListBox';
            } else {
                return '\\QCubed\\Control\\CheckboxList';    // for multi-selection
            }
        } elseif ($objColumn instanceof ManyToManyReference) {
            return '\\QCubed\\Control\\CheckboxList';    // for multi-selection
        }
        throw new \Exception('Unknown column type.');
    }


    public static function dataListControlClass(SqlTable $objTable)
    {
        // Is the class specified by the developer?
        if ($o = $objTable->Options) {
            if (isset($o['ControlClass'])) {
                return $o['ControlClass'];
            }
        }

        // Otherwise, return a default
        return '\\QCubed\\Project\\Control\\DataGrid';
    }

    /**
     * Returns the control label name as used in the data list panel corresponding to this column.
     *
     * @param SqlTable $objTable
     *
     * @return string
     */
    public static function dataListControlName(SqlTable $objTable)
    {
        if (($o = $objTable->Options) && isset ($o['Name'])) { // Did developer default?
            return $o['Name'];
        }
        return QString::wordsFromCamelCase($objTable->ClassNamePlural);
    }

    /**
     * Returns the name of an item in the data list as will be displayed in the edit panel.
     *
     * @param SqlTable $objTable
     *
     * @return string
     */
    public static function dataListItemName(SqlTable $objTable)
    {
        if (($o = $objTable->Options) && isset ($o['ItemName'])) { // Did developer override?
            return $o['ItemName'];
        }
        return QString::wordsFromCamelCase($objTable->ClassName);
    }

    public function dataListVarName(SqlTable $objTable)
    {
        $strPropName = self::dataListPropertyNamePlural($objTable);
        $objControlHelper = $this->getDataListCodeGenerator($objTable);
        return $objControlHelper->varName($strPropName);
    }

    public static function dataListPropertyName(SqlTable $objTable)
    {
        return $objTable->ClassName;
    }

    public static function dataListPropertyNamePlural(SqlTable $objTable)
    {
        return $objTable->ClassNamePlural;
    }


    /**
     * Returns the class for the control that will be created to edit the given column,
     * including the 'virtual' columns of reverse references (many to one) and many-to-many references.
     *
     * @param ColumnInterface $objColumn
     *
     * @return \QCubed\Codegen\Generator\GeneratorBase helper object
     * @throws \Exception
     */
    public function getControlCodeGenerator($objColumn)
    {
        $strControlClass = $this->modelConnectorControlClass($objColumn);

        if (method_exists($strControlClass, 'getCodeGenerator')) {
            return $strControlClass::getCodeGenerator();
        } else {
            throw new Caller("Class " . $strControlClass . " must implement getCodeGenerator()");
        }
    }

    public function getDataListCodeGenerator($objTable)
    {
        $strControlClass = $this->dataListControlClass($objTable);

        if (method_exists($strControlClass, 'getCodeGenerator')) {
            return $strControlClass::getCodeGenerator();
        } else {
            throw new Caller("Class " . $strControlClass . " must implement getCodeGenerator()");
        }
    }


    protected function calculateObjectMemberVariable($strTableName, $strColumnName, $strReferencedTableName)
    {
        return sprintf('%s%s%s%s',
            Codegen::prefixFromType(Type::OBJECT),
            $this->strAssociatedObjectPrefix,
            $this->calculateObjectDescription($strTableName, $strColumnName, $strReferencedTableName, false),
            $this->strAssociatedObjectSuffix);
    }

    protected function calculateObjectPropertyName($strTableName, $strColumnName, $strReferencedTableName)
    {
        return sprintf('%s%s%s',
            $this->strAssociatedObjectPrefix,
            $this->calculateObjectDescription($strTableName, $strColumnName, $strReferencedTableName, false),
            $this->strAssociatedObjectSuffix);
    }

    // TODO: These functions need to be documented heavily with information from "lexical analysis on fk names.txt"
    protected function calculateObjectDescription($strTableName, $strColumnName, $strReferencedTableName, $blnPluralize)
    {
        // Strip Prefixes (if applicable)
        $strTableName = $this->stripPrefixFromTable($strTableName);
        $strReferencedTableName = $this->stripPrefixFromTable($strReferencedTableName);

        // Starting Point
        $strToReturn = QString::camelCaseFromUnderscore($strTableName);

        if ($blnPluralize) {
            $strToReturn = $this->pluralize($strToReturn);
        }

        if ($strTableName == $strReferencedTableName) {
            // Self-referencing Reference to Describe

            // If Column Name is only the name of the referenced table, or the name of the referenced table with "_id",
            // then the object description is simply based off the table name.
            if (($strColumnName == $strReferencedTableName) ||
                ($strColumnName == $strReferencedTableName . '_id')
            ) {
                return sprintf('Child%s', $strToReturn);
            }

            // Rip out trailing "_id" if applicable
            $intLength = strlen($strColumnName);
            if (($intLength > 3) && (substr($strColumnName, $intLength - 3) == "_id")) {
                $strColumnName = substr($strColumnName, 0, $intLength - 3);
            }

            // Rip out the referenced table name from the column name
            $strColumnName = str_replace($strReferencedTableName, "", $strColumnName);

            // Change any double "_" to single "_"
            $strColumnName = str_replace("__", "_", $strColumnName);
            $strColumnName = str_replace("__", "_", $strColumnName);

            $strColumnName = QString::camelCaseFromUnderscore($strColumnName);

            // Special case for Parent/Child
            if ($strColumnName == 'Parent') {
                return sprintf('Child%s', $strToReturn);
            }

            return sprintf("%sAs%s",
                $strToReturn, $strColumnName);

        } else {
            // If Column Name is only the name of the referenced table, or the name of the referenced table with "_id",
            // then the object description is simply based off the table name.
            if (($strColumnName == $strReferencedTableName) ||
                ($strColumnName == $strReferencedTableName . '_id')
            ) {
                return $strToReturn;
            }

            // Rip out trailing "_id" if applicable
            $intLength = strlen($strColumnName);
            if (($intLength > 3) && (substr($strColumnName, $intLength - 3) == "_id")) {
                $strColumnName = substr($strColumnName, 0, $intLength - 3);
            }

            // Rip out the referenced table name from the column name
            $strColumnName = str_replace($strReferencedTableName, "", $strColumnName);

            // Change any double "_" to single "_"
            $strColumnName = str_replace("__", "_", $strColumnName);
            $strColumnName = str_replace("__", "_", $strColumnName);

            return sprintf("%sAs%s",
                $strToReturn,
                QString::camelCaseFromUnderscore($strColumnName));
        }
    }

    // this is called for ReverseReference Object Descriptions for association tables (many-to-many)
    protected function calculateObjectDescriptionForAssociation(
        $strAssociationTableName,
        $strTableName,
        $strReferencedTableName,
        $blnPluralize
    ) {
        // Strip Prefixes (if applicable)
        $strTableName = $this->stripPrefixFromTable($strTableName);
        $strAssociationTableName = $this->stripPrefixFromTable($strAssociationTableName);
        $strReferencedTableName = $this->stripPrefixFromTable($strReferencedTableName);

        // Starting Point
        $strToReturn = QString::camelCaseFromUnderscore($strReferencedTableName);

        if ($blnPluralize) {
            $strToReturn = $this->pluralize($strToReturn);
        }

        // Let's start with strAssociationTableName

        // Rip out trailing "_assn" if applicable
        $strAssociationTableName = str_replace($this->strAssociationTableSuffix, '', $strAssociationTableName);

        // remove instances of the table names in the association table name
        $strTableName2 = str_replace('_', '', $strTableName); // remove underscores if they are there
        $strReferencedTableName2 = str_replace('_', '',
            $strReferencedTableName); // remove underscores if they are there

        if (beginsWith($strAssociationTableName, $strTableName . '_')) {
            $strAssociationTableName = trimOffFront($strTableName . '_', $strAssociationTableName);
        } elseif (beginsWith($strAssociationTableName, $strTableName2 . '_')) {
            $strAssociationTableName = trimOffFront($strTableName2 . '_', $strAssociationTableName);
        } elseif (beginsWith($strAssociationTableName, $strReferencedTableName . '_')) {
            $strAssociationTableName = trimOffFront($strReferencedTableName . '_', $strAssociationTableName);
        } elseif (beginsWith($strAssociationTableName, $strReferencedTableName2 . '_')) {
            $strAssociationTableName = trimOffFront($strReferencedTableName2 . '_', $strAssociationTableName);
        } elseif ($strAssociationTableName == $strTableName ||
            $strAssociationTableName == $strTableName2 ||
            $strAssociationTableName == $strReferencedTableName ||
            $strAssociationTableName == $strReferencedTableName2
        ) {
            $strAssociationTableName = "";
        }

        if (endsWith($strAssociationTableName, '_' . $strTableName)) {
            $strAssociationTableName = trimOffEnd('_' . $strTableName, $strAssociationTableName);
        } elseif (endsWith($strAssociationTableName, '_' . $strTableName2)) {
            $strAssociationTableName = trimOffEnd('_' . $strTableName2, $strAssociationTableName);
        } elseif (endsWith($strAssociationTableName, '_' . $strReferencedTableName)) {
            $strAssociationTableName = trimOffEnd('_' . $strReferencedTableName, $strAssociationTableName);
        } elseif (endsWith($strAssociationTableName, '_' . $strReferencedTableName2)) {
            $strAssociationTableName = trimOffEnd('_' . $strReferencedTableName2, $strAssociationTableName);
        } elseif ($strAssociationTableName == $strTableName ||
            $strAssociationTableName == $strTableName2 ||
            $strAssociationTableName == $strReferencedTableName ||
            $strAssociationTableName == $strReferencedTableName2
        ) {
            $strAssociationTableName = "";
        }

        // Change any double "__" to single "_"
        $strAssociationTableName = str_replace("__", "_", $strAssociationTableName);
        $strAssociationTableName = str_replace("__", "_", $strAssociationTableName);
        $strAssociationTableName = str_replace("__", "_", $strAssociationTableName);

        // If we have nothing left or just a single "_" in AssociationTableName, return "Starting Point"
        if (($strAssociationTableName == "_") || ($strAssociationTableName == "")) {
            return sprintf("%s%s%s",
                $this->strAssociatedObjectPrefix,
                $strToReturn,
                $this->strAssociatedObjectSuffix);
        }

        // Otherwise, add "As" and the predicate
        return sprintf("%s%sAs%s%s",
            $this->strAssociatedObjectPrefix,
            $strToReturn,
            QString::camelCaseFromUnderscore($strAssociationTableName),
            $this->strAssociatedObjectSuffix);
    }

    // This is called by AnalyzeAssociationTable to calculate the GraphPrefixArray for a self-referencing association table (e.g. directed graph)
    protected function calculateGraphPrefixArray($objForeignKeyArray)
    {
        // Analyze Column Names to determine GraphPrefixArray
        if ((strpos(strtolower($objForeignKeyArray[0]->ColumnNameArray[0]), 'parent') !== false) ||
            (strpos(strtolower($objForeignKeyArray[1]->ColumnNameArray[0]), 'child') !== false)
        ) {
            $strGraphPrefixArray[0] = '';
            $strGraphPrefixArray[1] = 'Parent';
        } else {
            if ((strpos(strtolower($objForeignKeyArray[0]->ColumnNameArray[0]), 'child') !== false) ||
                (strpos(strtolower($objForeignKeyArray[1]->ColumnNameArray[0]), 'parent') !== false)
            ) {
                $strGraphPrefixArray[0] = 'Parent';
                $strGraphPrefixArray[1] = '';
            } else {
                // Use Default Prefixing for Graphs
                $strGraphPrefixArray[0] = 'Parent';
                $strGraphPrefixArray[1] = '';
            }
        }

        return $strGraphPrefixArray;
    }

    /**
     * Returns the variable type corresponding to the database column type
     * @param string $strDbType
     * @return string
     * @throws \Exception
     */
    protected function variableTypeFromDbType($strDbType)
    {
        switch ($strDbType) {
            case Database\FieldType::BIT:
                return Type::BOOLEAN;
            case Database\FieldType::BLOB:
                return Type::STRING;
            case Database\FieldType::CHAR:
                return Type::STRING;
            case Database\FieldType::DATE:
                return Type::DATE_TIME;
            case Database\FieldType::DATE_TIME:
                return Type::DATE_TIME;
            case Database\FieldType::FLOAT:
                return Type::FLOAT;
            case Database\FieldType::INTEGER:
                return Type::INTEGER;
            case Database\FieldType::TIME:
                return Type::DATE_TIME;
            case Database\FieldType::VAR_CHAR:
                return Type::STRING;
            case Database\FieldType::JSON:
                return Type::STRING;
            default:
                throw new \Exception("Invalid Db Type to Convert: $strDbType");
        }
    }

    /**
     * Return the plural of the given name. Override this and return the plural version of particular names
     * if this generic version isn't working for you.
     *
     * @param string $strName
     * @return string
     */
    protected function pluralize($strName)
    {
        // Special Rules go Here
        switch (true) {
            case (strtolower($strName) == 'play'):
                return $strName . 's';
        }

        $intLength = strlen($strName);
        if (substr($strName, $intLength - 1) == "y") {
            return substr($strName, 0, $intLength - 1) . "ies";
        }
        if (substr($strName, $intLength - 1) == "s") {
            return $strName . "es";
        }
        if (substr($strName, $intLength - 1) == "x") {
            return $strName . "es";
        }
        if (substr($strName, $intLength - 1) == "z") {
            return $strName . "zes";
        }
        if (substr($strName, $intLength - 2) == "sh") {
            return $strName . "es";
        }
        if (substr($strName, $intLength - 2) == "ch") {
            return $strName . "es";
        }

        return $strName . "s";
    }

    public function reportError($strError)
    {
        $this->strErrors .= $strError . "\r\n";
    }

    ////////////////////
    // Public Overriders
    ////////////////////

    /**
     * Override method to perform a property "Get"
     * This will get the value of $strName
     *
     * @param string $strName
     *
     * @throws \Exception|Caller
     * @return mixed
     */
    public function __get($strName)
    {
        switch ($strName) {
            case 'Errors':
                return $this->strErrors;
            case 'Warnings':
                return $this->strWarnings;
            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }

    /**
     * PHP magic method to set class properties
     * @param string $strName
     * @param string $mixValue
     *
     * @return mixed|void
     */
    public function __set($strName, $mixValue)
    {
        try {
            switch ($strName) {
                case 'Errors':
                    ($this->strErrors = Type::cast($mixValue, Type::STRING));
                    break;

                case 'Warnings':
                    ($this->strWarnings = Type::cast($mixValue, Type::STRING));
                    break;

                default:
                    parent::__set($strName, $mixValue);
            }
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
        }
    }
}