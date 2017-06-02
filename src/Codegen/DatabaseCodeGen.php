<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Codegen;

use QCubed\Database;
use QCubed\Exception\Caller;
use QCubed\Type;
use QCubed\Project\Codegen\CodegenBase as QCodegen;

//require_once QCUBED_PROJECT_DIR . '/qcubed/codegen/CodegenBase.php';

/**
 * Class DatabaseCodeGen
 * @package QCubed\Codegen
 * @was QDatabaseCodeGen
 */
class DatabaseCodeGen extends QCodegen
{
    public $objSettingsXml;    // Make public so templates can use it directly.

    // Objects
    /** @var array|SqlTable[] Array of tables in the database */
    protected $objTableArray;
    protected $strExcludedTableArray;
    protected $objTypeTableArray;
    protected $strAssociationTableNameArray;
    /** @var Database\DatabaseBase The database we are dealing with */
    protected $objDb;

    protected $intDatabaseIndex;
    /** @var string The delimiter to be used for parsing comments on the DB tables for being used as the name of ModelConnector's Label */
    protected $strCommentConnectorLabelDelimiter;

    // Table Suffixes
    protected $strTypeTableSuffixArray;
    protected $intTypeTableSuffixLengthArray;
    protected $strAssociationTableSuffix;
    protected $intAssociationTableSuffixLength;

    // Table Prefix
    protected $strStripTablePrefix;
    protected $intStripTablePrefixLength;

    // Exclude Patterns & Lists
    protected $strExcludePattern;
    protected $strExcludeListArray;

    // Include Patterns & Lists
    protected $strIncludePattern;
    protected $strIncludeListArray;

    // Uniquely Associated Objects
    protected $strAssociatedObjectPrefix;
    protected $strAssociatedObjectSuffix;

    // Relationship Scripts
    protected $strRelationships;
    protected $blnRelationshipsIgnoreCase;

    protected $strRelationshipsScriptPath;
    protected $strRelationshipsScriptFormat;
    protected $blnRelationshipsScriptIgnoreCase;

    protected $strRelationshipLinesQcubed = array();
    protected $strRelationshipLinesSql = array();

    // Type Table Items, Table Name and Column Name RegExp Patterns
    protected $strPatternTableName = '[[:alpha:]_][[:alnum:]_]*';
    protected $strPatternColumnName = '[[:alpha:]_][[:alnum:]_]*';
    protected $strPatternKeyName = '[[:alpha:]_][[:alnum:]_]*';

    protected $blnGenerateControlId;
    protected $objModelConnectorOptions;
    protected $blnAutoInitialize;
    protected $blnPrivateColumnVars;

    /**
     * @param $strTableName
     * @return SqlTable|TypeTable
     * @throws Caller
     */
    public function getTable($strTableName)
    {
        $strTableName = strtolower($strTableName);
        if (array_key_exists($strTableName, $this->objTableArray)) {
            return $this->objTableArray[$strTableName];
        }
        if (array_key_exists($strTableName, $this->objTypeTableArray)) {
            return $this->objTypeTableArray[$strTableName];
        };    // deal with table special
        throw new Caller(sprintf('Table does not exist or could not be processed: %s. %s', $strTableName,
            $this->strErrors));
    }

    public function getColumn($strTableName, $strColumnName)
    {
        try {
            $objTable = $this->getTable($strTableName);
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
        $strColumnName = strtolower($strColumnName);
        if (array_key_exists($strColumnName, $objTable->ColumnArray)) {
            return $objTable->ColumnArray[$strColumnName];
        }
        throw new Caller(sprintf('Column does not exist in %s: %s', $strTableName, $strColumnName));
    }

    /**
     * Given a CASE INSENSITIVE table and column name, it will return TRUE if the Table/Column
     * exists ANYWHERE in the already analyzed database
     *
     * @param string $strTableName
     * @param string $strColumnName
     * @return boolean true if it is found/validated
     */
    public function validateTableColumn($strTableName, $strColumnName)
    {
        $strTableName = trim(strtolower($strTableName));
        $strColumnName = trim(strtolower($strColumnName));

        if (array_key_exists($strTableName, $this->objTableArray)) {
            $strTableName = $this->objTableArray[$strTableName]->Name;
        } else {
            if (array_key_exists($strTableName, $this->objTypeTableArray)) {
                $strTableName = $this->objTypeTableArray[$strTableName]->Name;
            } else {
                if (array_key_exists($strTableName, $this->strAssociationTableNameArray)) {
                    $strTableName = $this->strAssociationTableNameArray[$strTableName];
                } else {
                    return false;
                }
            }
        }

        $objFieldArray = $this->objDb->getFieldsForTable($strTableName);

        foreach ($objFieldArray as $objField) {
            if (trim(strtolower($objField->Name)) == $strColumnName) {
                return true;
            }
        }

        return false;
    }

    public function getTitle()
    {
        if (!Database\Service::isInitialized()) {
            return '';
        }

        $objDatabase = Database\Service::getDatabase($this->intDatabaseIndex);

        if ($objDatabase) {
            return sprintf('Database Index #%s (%s / %s / %s)', $this->intDatabaseIndex, $objDatabase->Adapter,
                $objDatabase->Server, $objDatabase->Database);
        } else {
            return sprintf('Database Index #%s (N/A)', $this->intDatabaseIndex);
        }
    }

    public function getConfigXml()
    {
        $strCrLf = "\r\n";
        $strToReturn = sprintf('		<database index="%s">%s', $this->intDatabaseIndex, $strCrLf);
        $strToReturn .= sprintf('			<className prefix="%s" suffix="%s"/>%s', $this->strClassPrefix,
            $this->strClassSuffix, $strCrLf);
        $strToReturn .= sprintf('			<associatedObjectName prefix="%s" suffix="%s"/>%s',
            $this->strAssociatedObjectPrefix, $this->strAssociatedObjectSuffix, $strCrLf);
        $strToReturn .= sprintf('			<typeTableIdentifier suffix="%s"/>%s',
            implode(',', $this->strTypeTableSuffixArray), $strCrLf);
        $strToReturn .= sprintf('			<associationTableIdentifier suffix="%s"/>%s',
            $this->strAssociationTableSuffix, $strCrLf);
        $strToReturn .= sprintf('			<stripFromTableName prefix="%s"/>%s', $this->strStripTablePrefix,
            $strCrLf);
        $strToReturn .= sprintf('			<excludeTables pattern="%s" list="%s"/>%s', $this->strExcludePattern,
            implode(',', $this->strExcludeListArray), $strCrLf);
        $strToReturn .= sprintf('			<includeTables pattern="%s" list="%s"/>%s', $this->strIncludePattern,
            implode(',', $this->strIncludeListArray), $strCrLf);
        $strToReturn .= sprintf('			<relationships>%s', $strCrLf);
        if ($this->strRelationships) {
            $strToReturn .= sprintf('			%s%s', $this->strRelationships, $strCrLf);
        }
        $strToReturn .= sprintf('			</relationships>%s', $strCrLf);
        $strToReturn .= sprintf('			<relationshipsScript filepath="%s" format="%s"/>%s',
            $this->strRelationshipsScriptPath, $this->strRelationshipsScriptFormat, $strCrLf);
        $strToReturn .= sprintf('		</database>%s', $strCrLf);
        return $strToReturn;
    }

    public function getReportLabel()
    {
        // Setup Report Label
        $intTotalTableCount = count($this->objTableArray) + count($this->objTypeTableArray);
        if ($intTotalTableCount == 0) {
            $strReportLabel = 'There were no tables available to attempt code generation.';
        } else {
            if ($intTotalTableCount == 1) {
                $strReportLabel = 'There was 1 table available to attempt code generation:';
            } else {
                $strReportLabel = 'There were ' . $intTotalTableCount . ' tables available to attempt code generation:';
            }
        }

        return $strReportLabel;
    }

    public function generateAll()
    {
        $strReport = '';

        require_once(__DIR__ . '/template_utils.php');

        // Iterate through all the tables, generating one class at a time
        if ($this->objTableArray) {
            foreach ($this->objTableArray as $objTable) {
                if ($this->generateTable($objTable)) {
                    $intCount = $objTable->ReferenceCount;
                    if ($intCount == 0) {
                        $strCount = '(with no relationships)';
                    } else {
                        if ($intCount == 1) {
                            $strCount = '(with 1 relationship)';
                        } else {
                            $strCount = sprintf('(with %s relationships)', $intCount);
                        }
                    }
                    $strReport .= sprintf("Successfully generated DB ORM Class:   %s %s\r\n", $objTable->ClassName,
                        $strCount);
                } else {
                    $strReport .= sprintf("FAILED to generate DB ORM Class:       %s\r\n", $objTable->ClassName);
                }
            }
        }

        // Iterate through all the TYPE tables, generating one TYPE class at a time
        if ($this->objTypeTableArray) {
            foreach ($this->objTypeTableArray as $objTypeTable) {
                if ($this->generateTypeTable($objTypeTable)) {
                    $strReport .= sprintf("Successfully generated DB Type Class:  %s\n", $objTypeTable->ClassName);
                } else {
                    $strReport .= sprintf("FAILED to generate DB Type class:      %s\n", $objTypeTable->ClassName);
                }
            }
        }

        return $strReport;
    }

    /**
     * @param DatabaseCodeGen[] $objCodeGenArray
     * @return array
     */
    public static function generateAggregateHelper(array $objCodeGenArray)
    {
        $strToReturn = array();

        if (count($objCodeGenArray)) {
            // Standard ORM Tables
            $objTableArray = array();
            foreach ($objCodeGenArray as $objCodeGen) {
                $objCurrentTableArray = $objCodeGen->TableArray;
                foreach ($objCurrentTableArray as $objTable) {
                    $objTableArray[$objTable->ClassName] = $objTable;
                }
            }

            $mixArgumentArray = array('objTableArray' => $objTableArray);
            if ($objCodeGenArray[0]->generateFiles('aggregate_db_orm', $mixArgumentArray)) {
                $strToReturn[] = 'Successfully generated Aggregate DB ORM file(s)';
            } else {
                $strToReturn[] = 'FAILED to generate Aggregate DB ORM file(s)';
            }

            // Type Tables
            $objTableArray = array();
            foreach ($objCodeGenArray as $objCodeGen) {
                $objCurrentTableArray = $objCodeGen->TypeTableArray;
                foreach ($objCurrentTableArray as $objTable) {
                    $objTableArray[$objTable->ClassName] = $objTable;
                }
            }

            $mixArgumentArray = array('objTableArray' => $objTableArray);
            if ($objCodeGenArray[0]->generateFiles('aggregate_db_type', $mixArgumentArray)) {
                $strToReturn[] = 'Successfully generated Aggregate DB Type file(s)';
            } else {
                $strToReturn[] = 'FAILED to generate Aggregate DB Type file(s)';
            }
        }

        return $strToReturn;
    }

    public function __construct($objSettingsXml)
    {
        parent::__construct($objSettingsXml);
        // Make settings file accessible to templates
        //$this->objSettingsXml = $objSettingsXml;

        // Setup Local Arrays
        $this->strAssociationTableNameArray = array();
        $this->objTableArray = array();
        $this->objTypeTableArray = array();
        $this->strExcludedTableArray = array();

        // Set the DatabaseIndex
        $this->intDatabaseIndex = static::lookupSetting($objSettingsXml, null, 'index', Type::INTEGER);

        // Append Suffix/Prefixes
        $this->strClassPrefix = static::lookupSetting($objSettingsXml, 'className', 'prefix');
        $this->strClassSuffix = static::lookupSetting($objSettingsXml, 'className', 'suffix');
        $this->strAssociatedObjectPrefix = static::lookupSetting($objSettingsXml, 'associatedObjectName', 'prefix');
        $this->strAssociatedObjectSuffix = static::lookupSetting($objSettingsXml, 'associatedObjectName', 'suffix');

        // Table Type Identifiers
        $strTypeTableSuffixList = static::lookupSetting($objSettingsXml, 'typeTableIdentifier', 'suffix');
        $strTypeTableSuffixArray = explode(',', $strTypeTableSuffixList);
        foreach ($strTypeTableSuffixArray as $strTypeTableSuffix) {
            $this->strTypeTableSuffixArray[] = trim($strTypeTableSuffix);
            $this->intTypeTableSuffixLengthArray[] = strlen(trim($strTypeTableSuffix));
        }
        $this->strAssociationTableSuffix = static::lookupSetting($objSettingsXml, 'associationTableIdentifier',
            'suffix');
        $this->intAssociationTableSuffixLength = strlen($this->strAssociationTableSuffix);

        // Stripping TablePrefixes
        $this->strStripTablePrefix = static::lookupSetting($objSettingsXml, 'stripFromTableName', 'prefix');
        $this->intStripTablePrefixLength = strlen($this->strStripTablePrefix);

        // Exclude/Include Tables
        $this->strExcludePattern = static::lookupSetting($objSettingsXml, 'excludeTables', 'pattern');
        $strExcludeList = static::lookupSetting($objSettingsXml, 'excludeTables', 'list');
        $this->strExcludeListArray = explode(',', $strExcludeList);
        array_walk($this->strExcludeListArray, 'QCubed\Codegen\array_trim');

        // Include Patterns
        $this->strIncludePattern = static::lookupSetting($objSettingsXml, 'includeTables', 'pattern');
        $strIncludeList = static::lookupSetting($objSettingsXml, 'includeTables', 'list');
        $this->strIncludeListArray = explode(',', $strIncludeList);
        array_walk($this->strIncludeListArray, 'QCubed\Codegen\array_trim');

        // Relationship Scripts
        $this->strRelationships = static::lookupSetting($objSettingsXml, 'relationships');
        $this->strRelationshipsScriptPath = static::lookupSetting($objSettingsXml, 'relationshipsScript', 'filepath');
        $this->strRelationshipsScriptFormat = static::lookupSetting($objSettingsXml, 'relationshipsScript', 'format');

        // Column Comment for ModelConnectorLabel setting.
        $this->strCommentConnectorLabelDelimiter = static::lookupSetting($objSettingsXml,
            'columnCommentForModelConnector', 'delimiter');

        // Check to make sure things that are required are there
        if (!$this->intDatabaseIndex) {
            $this->strErrors .= "CodeGen Settings XML Fatal Error: databaseIndex was invalid or not set\r\n";
        }

        // Aggregate RelationshipLinesQcubed and RelationshipLinesSql arrays
        if ($this->strRelationships) {
            $strLines = explode("\n", strtolower($this->strRelationships));
            if ($strLines) {
                foreach ($strLines as $strLine) {
                    $strLine = trim($strLine);

                    if (($strLine) &&
                        (strlen($strLine) > 2) &&
                        (substr($strLine, 0, 2) != '//') &&
                        (substr($strLine, 0, 2) != '--') &&
                        (substr($strLine, 0, 1) != '#')
                    ) {
                        $this->strRelationshipLinesQcubed[$strLine] = $strLine;
                    }
                }
            }
        }

        if ($this->strRelationshipsScriptPath) {
            if (!file_exists($this->strRelationshipsScriptPath)) {
                $this->strErrors .= sprintf("CodeGen Settings XML Fatal Error: relationshipsScript filepath \"%s\" does not exist\r\n",
                    $this->strRelationshipsScriptPath);
            } else {
                $strScript = strtolower(trim(file_get_contents($this->strRelationshipsScriptPath)));
                switch (strtolower($this->strRelationshipsScriptFormat)) {
                    case 'qcubed':
                        $strLines = explode("\n", $strScript);
                        if ($strLines) {
                            foreach ($strLines as $strLine) {
                                $strLine = trim($strLine);

                                if (($strLine) &&
                                    (strlen($strLine) > 2) &&
                                    (substr($strLine, 0, 2) != '//') &&
                                    (substr($strLine, 0, 2) != '--') &&
                                    (substr($strLine, 0, 1) != '#')
                                ) {
                                    $this->strRelationshipLinesQcubed[$strLine] = $strLine;
                                }
                            }
                        }
                        break;

                    case 'sql':
                        // Separate all commands in the script (separated by ";")
                        $strCommands = explode(';', $strScript);
                        if ($strCommands) {
                            foreach ($strCommands as $strCommand) {
                                $strCommand = trim($strCommand);

                                if ($strCommand) {
                                    // Take out all comment lines in the script
                                    $strLines = explode("\n", $strCommand);
                                    $strCommand = '';
                                    foreach ($strLines as $strLine) {
                                        $strLine = trim($strLine);
                                        if (($strLine) &&
                                            (substr($strLine, 0, 2) != '//') &&
                                            (substr($strLine, 0, 2) != '--') &&
                                            (substr($strLine, 0, 1) != '#')
                                        ) {
                                            $strLine = str_replace('	', ' ', $strLine);
                                            $strLine = str_replace('        ', ' ', $strLine);
                                            $strLine = str_replace('       ', ' ', $strLine);
                                            $strLine = str_replace('      ', ' ', $strLine);
                                            $strLine = str_replace('     ', ' ', $strLine);
                                            $strLine = str_replace('    ', ' ', $strLine);
                                            $strLine = str_replace('   ', ' ', $strLine);
                                            $strLine = str_replace('  ', ' ', $strLine);
                                            $strLine = str_replace('  ', ' ', $strLine);
                                            $strLine = str_replace('  ', ' ', $strLine);
                                            $strLine = str_replace('  ', ' ', $strLine);
                                            $strLine = str_replace('  ', ' ', $strLine);

                                            $strCommand .= $strLine . ' ';
                                        }
                                    }

                                    $strCommand = trim($strCommand);
                                    if ((strpos($strCommand, 'alter table') === 0) &&
                                        (strpos($strCommand, 'foreign key') !== false)
                                    ) {
                                        $this->strRelationshipLinesSql[$strCommand] = $strCommand;
                                    }
                                }
                            }
                        }
                        break;

                    default:
                        $this->strErrors .= sprintf("CodeGen Settings XML Fatal Error: relationshipsScript format \"%s\" is invalid (must be either \"qcubed\" or \"sql\")\r\n",
                            $this->strRelationshipsScriptFormat);
                        break;
                }
            }
        }

        $this->blnGenerateControlId = static::lookupSetting($objSettingsXml, 'generateControlId', 'support',
            Type::BOOLEAN);
        $this->objModelConnectorOptions = new OptionFile();

        $this->blnAutoInitialize = static::lookupSetting($objSettingsXml, 'createOptions', 'autoInitialize',
            Type::BOOLEAN);
        $this->blnPrivateColumnVars = static::lookupSetting($objSettingsXml, 'createOptions', 'privateColumnVars',
            Type::BOOLEAN);

        if ($this->strErrors) {
            return;
        }

        $this->analyzeDatabase();
    }

    protected function analyzeDatabase()
    {
        if (!Database\Service::count()) {
            $this->strErrors = 'FATAL ERROR: No databases are listed in the configuration file. Edit the /project/includes/configuration/active/databases.cfg.php file';
            return;
        }

        // Set aside the Database object
        $this->objDb = Database\Service::getDatabase($this->intDatabaseIndex);

        // Ensure the DB Exists
        if (!$this->objDb) {
            $this->strErrors = 'FATAL ERROR: No database configured at index ' . $this->intDatabaseIndex . '. Check your configuration file.';
            return;
        }

        // Ensure DB Profiling is DISABLED on this DB
        if ($this->objDb->EnableProfiling) {
            $this->strErrors = 'FATAL ERROR: Code generator cannot analyze the database at index ' . $this->intDatabaseIndex . ' while DB Profiling is enabled.';
            return;
        }

        // Get the list of Tables as a string[]
        $strTableArray = $this->objDb->getTables();


        // ITERATION 1: Simply create the Table and TypeTable Arrays
        if ($strTableArray) {
            foreach ($strTableArray as $strTableName) {

                // Do we Exclude this Table Name? (given includeTables and excludeTables)
                // First check the lists of Excludes and the Exclude Patterns
                if (in_array($strTableName, $this->strExcludeListArray) ||
                    (strlen($this->strExcludePattern) > 0 && preg_match(":" . $this->strExcludePattern . ":i",
                            $strTableName))
                ) {

                    // So we THINK we may be excluding this table
                    // But check against the explicit INCLUDE list and patterns
                    if (in_array($strTableName, $this->strIncludeListArray) ||
                        (strlen($this->strIncludePattern) > 0 && preg_match(":" . $this->strIncludePattern . ":i",
                                $strTableName))
                    ) {
                        // If we're here, we explicitly want to include this table
                        // Therefore, do nothing
                    } else {
                        // If we're here, then we want to exclude this table
                        $this->strExcludedTableArray[strtolower($strTableName)] = true;

                        // Exit this iteration of the foreach loop
                        continue;
                    }
                }

                // Check to see if this table name exists anywhere else yet, and warn if it is
                foreach (static::$CodeGenArray as $objCodeGen) {
                    if ($objCodeGen instanceof DatabaseCodeGen) {
                        foreach ($objCodeGen->objTableArray as $objPossibleDuplicate) {
                            if (strtolower($objPossibleDuplicate->Name) == strtolower($strTableName)) {
                                $this->strErrors .= 'Duplicate Table Name Used: ' . $strTableName . "\r\n";
                            }
                        }
                    }
                }

                // Perform different tasks based on whether it's an Association table,
                // a Type table, or just a regular table
                $blnIsTypeTable = false;
                foreach ($this->intTypeTableSuffixLengthArray as $intIndex => $intTypeTableSuffixLength) {
                    if (($intTypeTableSuffixLength) &&
                        (strlen($strTableName) > $intTypeTableSuffixLength) &&
                        (substr($strTableName,
                                strlen($strTableName) - $intTypeTableSuffixLength) == $this->strTypeTableSuffixArray[$intIndex])
                    ) {
                        // Let's mark, that we have type table
                        $blnIsTypeTable = true;
                        // Create a TYPE Table and add it to the array
                        $objTypeTable = new TypeTable($strTableName);
                        $this->objTypeTableArray[strtolower($strTableName)] = $objTypeTable;
                        // If we found type table, there is no point of iterating for other type table suffixes
                        break;
//						_p("TYPE Table: $strTableName<br />", false);
                    }
                }
                if (!$blnIsTypeTable) {
                    // If current table wasn't type table, let's look for other table types
                    if (($this->intAssociationTableSuffixLength) &&
                        (strlen($strTableName) > $this->intAssociationTableSuffixLength) &&
                        (substr($strTableName,
                                strlen($strTableName) - $this->intAssociationTableSuffixLength) == $this->strAssociationTableSuffix)
                    ) {
                        // Add this ASSOCIATION Table Name to the array
                        $this->strAssociationTableNameArray[strtolower($strTableName)] = $strTableName;
//						_p("ASSN Table: $strTableName<br />", false);

                    } else {
                        // Create a Regular Table and add it to the array
                        $objTable = new SqlTable($strTableName);
                        $this->objTableArray[strtolower($strTableName)] = $objTable;
//						_p("Table: $strTableName<br />", false);
                    }
                }
            }
        }


        // Analyze All the Type Tables
        if ($this->objTypeTableArray) {
            foreach ($this->objTypeTableArray as $objTypeTable) {
                $this->analyzeTypeTable($objTypeTable);
            }
        }

        // Analyze All the Regular Tables
        if ($this->objTableArray) {
            foreach ($this->objTableArray as $objTable) {
                $this->analyzeTable($objTable);
            }
        }

        // Analyze All the Association Tables
        if ($this->strAssociationTableNameArray) {
            foreach ($this->strAssociationTableNameArray as $strAssociationTableName) {
                $this->analyzeAssociationTable($strAssociationTableName);
            }
        }

        // Finally, for each Relationship in all Tables, Warn on Non Single Column PK based FK:
        if ($this->objTableArray) {
            foreach ($this->objTableArray as $objTable) {
                if ($objTable->ColumnArray) {
                    foreach ($objTable->ColumnArray as $objColumn) {
                        if ($objColumn->Reference && !$objColumn->Reference->IsType) {
                            $objReference = $objColumn->Reference;
//							$objReferencedTable = $this->objTableArray[strtolower($objReference->Table)];
                            $objReferencedTable = $this->getTable($objReference->Table);
                            $objReferencedColumn = $objReferencedTable->ColumnArray[strtolower($objReference->Column)];


                            if (!$objReferencedColumn->PrimaryKey) {
                                $this->strErrors .= sprintf("Warning: Invalid Relationship created in %s class (for foreign key \"%s\") -- column \"%s\" is not the single-column primary key for the referenced \"%s\" table\r\n",
                                    $objReferencedTable->ClassName, $objReference->KeyName, $objReferencedColumn->Name,
                                    $objReferencedTable->Name);
                            } else {
                                if (count($objReferencedTable->PrimaryKeyColumnArray) != 1) {
                                    $this->strErrors .= sprintf("Warning: Invalid Relationship created in %s class (for foreign key \"%s\") -- column \"%s\" is not the single-column primary key for the referenced \"%s\" table\r\n",
                                        $objReferencedTable->ClassName, $objReference->KeyName,
                                        $objReferencedColumn->Name, $objReferencedTable->Name);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected function listOfColumnsFromTable(SqlTable $objTable)
    {
        $strArray = array();
        $objColumnArray = $objTable->ColumnArray;
        if ($objColumnArray) {
            foreach ($objColumnArray as $objColumn) {
                array_push($strArray, $objColumn->Name);
            }
        }
        return implode(', ', $strArray);
    }

    protected function getColumnArray(SqlTable $objTable, $strColumnNameArray)
    {
        $objToReturn = array();

        if ($strColumnNameArray) {
            foreach ($strColumnNameArray as $strColumnName) {
                array_push($objToReturn, $objTable->ColumnArray[strtolower($strColumnName)]);
            }
        }

        return $objToReturn;
    }

    public function generateTable(SqlTable $objTable)
    {
        // Create Argument Array
        $mixArgumentArray = array('objTable' => $objTable);
        return $this->generateFiles('db_orm', $mixArgumentArray);
    }

    public function generateTypeTable(TypeTable $objTypeTable)
    {
        // Create Argument Array
        $mixArgumentArray = array('objTypeTable' => $objTypeTable);
        return $this->generateFiles('db_type', $mixArgumentArray);
    }

    protected function analyzeAssociationTable($strTableName)
    {
        $objFieldArray = $this->objDb->getFieldsForTable($strTableName);

        // Association tables must have 2 fields
        if (count($objFieldArray) != 2) {
            $this->strErrors .= sprintf("AssociationTable %s does not have exactly 2 columns.\n",
                $strTableName);
            return;
        }

        if ((!$objFieldArray[0]->NotNull) ||
            (!$objFieldArray[1]->NotNull)
        ) {
            $this->strErrors .= sprintf("AssociationTable %s's two columns must both be not null",
                $strTableName);
            return;
        }

        if (((!$objFieldArray[0]->PrimaryKey) &&
                ($objFieldArray[1]->PrimaryKey)) ||
            (($objFieldArray[0]->PrimaryKey) &&
                (!$objFieldArray[1]->PrimaryKey))
        ) {
            $this->strErrors .= sprintf("AssociationTable %s only support two-column composite Primary Keys.\n",
                $strTableName);
            return;
        }

        $objForeignKeyArray = $this->objDb->getForeignKeysForTable($strTableName);

        // Add to it, the list of Foreign Keys from any Relationships Script
        $objForeignKeyArray = $this->getForeignKeysFromRelationshipsScript($strTableName, $objForeignKeyArray);

        if (count($objForeignKeyArray) != 2) {
            $this->strErrors .= sprintf("AssociationTable %s does not have exactly 2 foreign keys.  Code Gen analysis found %s.\n",
                $strTableName, count($objForeignKeyArray));
            return;
        }

        // Setup two new ManyToManyReference objects
        $objManyToManyReferenceArray[0] = new ManyToManyReference();
        $objManyToManyReferenceArray[1] = new ManyToManyReference();

        // Ensure that the linked tables are both not excluded
        if (array_key_exists($objForeignKeyArray[0]->ReferenceTableName, $this->strExcludedTableArray) ||
            array_key_exists($objForeignKeyArray[1]->ReferenceTableName, $this->strExcludedTableArray)
        ) {
            return;
        }

        // Setup GraphPrefixArray (if applicable)
        if ($objForeignKeyArray[0]->ReferenceTableName == $objForeignKeyArray[1]->ReferenceTableName) {
            // We are analyzing a graph association
            $strGraphPrefixArray = $this->calculateGraphPrefixArray($objForeignKeyArray);
        } else {
            $strGraphPrefixArray = array('', '');
        }

        // Go through each FK and setup each ManyToManyReference object
        for ($intIndex = 0; $intIndex < 2; $intIndex++) {
            $objManyToManyReference = $objManyToManyReferenceArray[$intIndex];

            $objForeignKey = $objForeignKeyArray[$intIndex];
            $objOppositeForeignKey = $objForeignKeyArray[($intIndex == 0) ? 1 : 0];

            // Make sure the FK is a single-column FK
            if (count($objForeignKey->ColumnNameArray) != 1) {
                $this->strErrors .= sprintf("AssoiationTable %s has multi-column foreign keys.\n",
                    $strTableName);
                return;
            }

            $objManyToManyReference->KeyName = $objForeignKey->KeyName;
            $objManyToManyReference->Table = $strTableName;
            $objManyToManyReference->Column = $objForeignKey->ColumnNameArray[0];
            $objManyToManyReference->PropertyName = $this->modelColumnPropertyName($objManyToManyReference->Column);
            $objManyToManyReference->OppositeColumn = $objOppositeForeignKey->ColumnNameArray[0];
            $objManyToManyReference->AssociatedTable = $objOppositeForeignKey->ReferenceTableName;

            // Calculate OppositeColumnVariableName
            // Do this by first making a fake column which is the PK column of the AssociatedTable,
            // but who's column name is ManyToManyReference->Column
//				$objOppositeColumn = clone($this->objTableArray[strtolower($objManyToManyReference->AssociatedTable)]->PrimaryKeyColumnArray[0]);

            $objTable = $this->getTable($objManyToManyReference->AssociatedTable);
            $objOppositeColumn = clone($objTable->PrimaryKeyColumnArray[0]);
            $objOppositeColumn->Name = $objManyToManyReference->OppositeColumn;
            $objManyToManyReference->OppositeVariableName = $this->modelColumnVariableName($objOppositeColumn);
            $objManyToManyReference->OppositePropertyName = $this->modelColumnPropertyName($objOppositeColumn->Name);
            $objManyToManyReference->OppositeVariableType = $objOppositeColumn->VariableType;
            $objManyToManyReference->OppositeDbType = $objOppositeColumn->DbType;

            $objManyToManyReference->VariableName = $this->modelReverseReferenceVariableName($objOppositeForeignKey->ReferenceTableName);
            $objManyToManyReference->VariableType = $this->modelReverseReferenceVariableType($objOppositeForeignKey->ReferenceTableName);

            $objManyToManyReference->ObjectDescription = $strGraphPrefixArray[$intIndex] . $this->calculateObjectDescriptionForAssociation($strTableName,
                    $objForeignKey->ReferenceTableName, $objOppositeForeignKey->ReferenceTableName, false);
            $objManyToManyReference->ObjectDescriptionPlural = $strGraphPrefixArray[$intIndex] . $this->calculateObjectDescriptionForAssociation($strTableName,
                    $objForeignKey->ReferenceTableName, $objOppositeForeignKey->ReferenceTableName, true);

            $objManyToManyReference->OppositeObjectDescription = $strGraphPrefixArray[($intIndex == 0) ? 1 : 0] . $this->calculateObjectDescriptionForAssociation($strTableName,
                    $objOppositeForeignKey->ReferenceTableName, $objForeignKey->ReferenceTableName, false);
            $objManyToManyReference->IsTypeAssociation = ($objTable instanceof TypeTable);
            $objManyToManyReference->Options = $this->objModelConnectorOptions->getOptions($this->modelClassName($objForeignKey->ReferenceTableName),
                $objManyToManyReference->ObjectDescription);

        }


        // Iterate through the list of Columns to create objColumnArray
        $objColumnArray = array();
        foreach ($objFieldArray as $objField) {
            if (($objField->Name != $objManyToManyReferenceArray[0]->Column) &&
                ($objField->Name != $objManyToManyReferenceArray[1]->Column)
            ) {
                $objColumn = $this->analyzeTableColumn($objField, null);
                if ($objColumn) {
                    $objColumnArray[strtolower($objColumn->Name)] = $objColumn;
                }
            }
        }

        // Make sure lone primary key columns are marked as unique
        $objKeyColumn = null;
        foreach ($objColumnArray as $objColumn) {
            if ($objColumn->PrimaryKey) {
                if ($objKeyColumn === null) {
                    $objKeyColumn = $objColumn;
                } else {
                    $objKeyColumn = false; // multiple key columns
                }
            }
        }
        if ($objKeyColumn) {
            $objKeyColumn->Unique = true;
        }

        $objManyToManyReferenceArray[0]->ColumnArray = $objColumnArray;
        $objManyToManyReferenceArray[1]->ColumnArray = $objColumnArray;

        // Push the ManyToManyReference Objects to the tables
        for ($intIndex = 0; $intIndex < 2; $intIndex++) {
            $objManyToManyReference = $objManyToManyReferenceArray[$intIndex];
            $strTableWithReference = $objManyToManyReferenceArray[($intIndex == 0) ? 1 : 0]->AssociatedTable;

            $objTable = $this->getTable($strTableWithReference);
            $objArray = $objTable->ManyToManyReferenceArray;
            array_push($objArray, $objManyToManyReference);
            $objTable->ManyToManyReferenceArray = $objArray;
        }

    }

    protected function analyzeTypeTable(TypeTable $objTypeTable)
    {
        // Setup the Array of Reserved Words
        $strReservedWords = explode(',', static::PHP_RESERVED_WORDS);
        for ($intIndex = 0; $intIndex < count($strReservedWords); $intIndex++) {
            $strReservedWords[$intIndex] = strtolower(trim($strReservedWords[$intIndex]));
        }

        // Setup the Type Table Object
        $strTableName = $objTypeTable->Name;
        $objTypeTable->ClassName = $this->modelClassName($strTableName);

        // Ensure that there are only 2 fields, an integer PK field (can be named anything) and a unique varchar field
        $objFieldArray = $this->objDb->getFieldsForTable($strTableName);

        if (($objFieldArray[0]->Type != Database\FieldType::INTEGER) ||
            (!$objFieldArray[0]->PrimaryKey)
        ) {
            $this->strErrors .= sprintf("TypeTable %s's first column is not a PK integer.\n",
                $strTableName);
            return;
        }

        if (($objFieldArray[1]->Type != Database\FieldType::VAR_CHAR) ||
            (!$objFieldArray[1]->Unique)
        ) {
            $this->strErrors .= sprintf("TypeTable %s's second column is not a unique VARCHAR.\n",
                $strTableName);
            return;
        }

        // Get the rows
        $objResult = $this->objDb->query(sprintf('SELECT * FROM %s', $strTableName));
        $strNameArray = array();
        $strTokenArray = array();
        $strExtraPropertyArray = array();
        $extraFields = array();
        $intRowWidth = count($objFieldArray);
        while ($objDbRow = $objResult->getNextRow()) {
            $strRowArray = $objDbRow->getColumnNameArray();
            $id = $strRowArray[0];
            $name = $strRowArray[1];

            $strNameArray[$id] = str_replace("'", "\\'", str_replace('\\', '\\\\', $name));
            $strTokenArray[$id] = $this->typeTokenFromTypeName($name);
            if ($intRowWidth > 2) { // there are extra columns to process
                $strExtraPropertyArray[$id] = array();
                for ($i = 2; $i < $intRowWidth; $i++) {
                    $strFieldName = static::typeColumnPropertyName($objFieldArray[$i]->Name);
                    $extraFields[$i - 2]['name'] = $strFieldName;
                    $extraFields[$i - 2]['type'] = $this->variableTypeFromDbType($objFieldArray[$i]->Type);
                    $extraFields[$i - 2]['nullAllowed'] = !$objFieldArray[$i]->NotNull;

                    // Get and resolve type based value
                    $value = $objDbRow->getColumn($objFieldArray[$i]->Name, $objFieldArray[$i]->Type);
                    $strExtraPropertyArray[$id][$strFieldName] = $value;
                }
            }

            foreach ($strReservedWords as $strReservedWord) {
                if (trim(strtolower($strTokenArray[$id])) == $strReservedWord) {
                    $this->strErrors .= sprintf("Warning: TypeTable %s contains a type name which is a reserved word: %s.  Appended _ to the beginning of it.\r\n",
                        $strTableName, $strReservedWord);
                    $strTokenArray[$id] = '_' . $strTokenArray[$id];
                }
            }
            if (strlen($strTokenArray[$id]) == 0) {
                $this->strErrors .= sprintf("Warning: TypeTable %s contains an invalid type name: %s\r\n",
                    $strTableName, stripslashes($strNameArray[$id]));
                return;
            }
        }

        ksort($strNameArray);
        ksort($strTokenArray);

        $objTypeTable->NameArray = $strNameArray;
        $objTypeTable->TokenArray = $strTokenArray;
        $objTypeTable->ExtraFieldsArray = $extraFields;
        $objTypeTable->ExtraPropertyArray = $strExtraPropertyArray;
        $objColumn = $this->analyzeTableColumn($objFieldArray[0], $objTypeTable);
        $objColumn->Unique = true;
        $objTypeTable->KeyColumn = $objColumn;
    }

    protected function analyzeTable(SqlTable $objTable)
    {
        // Setup the Table Object
        $objTable->OwnerDbIndex = $this->intDatabaseIndex;
        $strTableName = $objTable->Name;
        $objTable->ClassName = $this->modelClassName($strTableName);
        $objTable->ClassNamePlural = $this->pluralize($objTable->ClassName);

        $objTable->Options = $this->objModelConnectorOptions->getOptions($objTable->ClassName,
            OptionFile::TABLE_OPTIONS_FIELD_NAME);

        // Get the List of Columns
        $objFieldArray = $this->objDb->getFieldsForTable($strTableName);

        // Iterate through the list of Columns to create objColumnArray
        $objColumnArray = array();
        if ($objFieldArray) {
            foreach ($objFieldArray as $objField) {
                $objColumn = $this->analyzeTableColumn($objField, $objTable);
                if ($objColumn) {
                    $objColumnArray[strtolower($objColumn->Name)] = $objColumn;
                }
            }
        }
        $objTable->ColumnArray = $objColumnArray;

        // Make sure lone primary key columns are marked as unique
        $objKeyColumn = null;
        foreach ($objColumnArray as $objColumn) {
            if ($objColumn->PrimaryKey) {
                if ($objKeyColumn === null) {
                    $objKeyColumn = $objColumn;
                } else {
                    $objKeyColumn = false; // multiple key columns
                }
            }
        }
        if ($objKeyColumn) {
            $objKeyColumn->Unique = true;
        }


        // Get the List of Indexes
        $objTable->IndexArray = $this->objDb->getIndexesForTable($objTable->Name);

        // Create an Index array
        $objIndexArray = array();
        // Create our Index for Primary Key (if applicable)
        $strPrimaryKeyArray = array();
        foreach ($objColumnArray as $objColumn) {
            if ($objColumn->PrimaryKey) {
                $objPkColumn = $objColumn;
                array_push($strPrimaryKeyArray, $objColumn->Name);
            }
        }
        if (!empty($objPkColumn)) {
            $objIndex = new Index();
            $objIndex->KeyName = 'pk_' . $strTableName;
            $objIndex->PrimaryKey = true;
            $objIndex->Unique = true;
            $objIndex->ColumnNameArray = $strPrimaryKeyArray;
            array_push($objIndexArray, $objIndex);

            if (count($strPrimaryKeyArray) == 1) {
                $objPkColumn->Unique = true;
                $objPkColumn->Indexed = true;
            }
        }

        // Iterate though each Index that exists in this table, set any Columns's "Index" property
        // to TRUE if they are a single-column index
        if ($objTable->IndexArray) {
            foreach ($objArray = $objTable->IndexArray as $objDatabaseIndex) {
                // Make sure the columns are defined
                if (count($objDatabaseIndex->ColumnNameArray) == 0) {
                    $this->strErrors .= sprintf("Index %s in table %s indexes on no columns.\n",
                        $objDatabaseIndex->KeyName, $strTableName);
                } else {
                    // Ensure every column exist in the DbIndex's ColumnNameArray
                    $blnFailed = false;
                    foreach ($objArray = $objDatabaseIndex->ColumnNameArray as $strColumnName) {
                        if (array_key_exists(strtolower($strColumnName), $objTable->ColumnArray) &&
                            ($objTable->ColumnArray[strtolower($strColumnName)])
                        ) {
                            // It exists -- do nothing
                        } else {
                            // Otherwise, add a warning
                            $this->strErrors .= sprintf("Index %s in table %s indexes on the column %s, which does not appear to exist.\n",
                                $objDatabaseIndex->KeyName, $strTableName, $strColumnName);
                            $blnFailed = true;
                        }
                    }

                    if (!$blnFailed) {
                        // Let's make sure if this is a single-column index, we haven't already created a single-column index for this column
                        $blnAlreadyCreated = false;
                        foreach ($objIndexArray as $objIndex) {
                            if (count($objIndex->ColumnNameArray) == count($objDatabaseIndex->ColumnNameArray)) {
                                if (implode(',', $objIndex->ColumnNameArray) == implode(',',
                                        $objDatabaseIndex->ColumnNameArray)
                                ) {
                                    $blnAlreadyCreated = true;
                                }
                            }
                        }

                        if (!$blnAlreadyCreated) {
                            // Create the Index Object
                            $objIndex = new Index();
                            $objIndex->KeyName = $objDatabaseIndex->KeyName;
                            $objIndex->PrimaryKey = $objDatabaseIndex->PrimaryKey;
                            $objIndex->Unique = $objDatabaseIndex->Unique;
                            if ($objDatabaseIndex->PrimaryKey) {
                                $objIndex->Unique = true;
                            }
                            $objIndex->ColumnNameArray = $objDatabaseIndex->ColumnNameArray;

                            // Add the new index object to the index array
                            array_push($objIndexArray, $objIndex);

                            // Lastly, if it's a single-column index, update the Column in the table to reflect this
                            if (count($objDatabaseIndex->ColumnNameArray) == 1) {
                                $strColumnName = $objDatabaseIndex->ColumnNameArray[0];
                                $objColumn = $objTable->ColumnArray[strtolower($strColumnName)];
                                $objColumn->Indexed = true;

                                if ($objIndex->Unique) {
                                    $objColumn->Unique = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Add the IndexArray to the table
        $objTable->IndexArray = $objIndexArray;


        // Get the List of Foreign Keys from the database
        $objForeignKeys = $this->objDb->getForeignKeysForTable($objTable->Name);

        // Add to it, the list of Foreign Keys from any Relationships Script
        $objForeignKeys = $this->getForeignKeysFromRelationshipsScript($strTableName, $objForeignKeys);

        // Iterate through each foreign key that exists in this table
        if ($objForeignKeys) {
            foreach ($objForeignKeys as $objForeignKey) {

                // Make sure it's a single-column FK
                if (count($objForeignKey->ColumnNameArray) != 1) {
                    $this->strErrors .= sprintf("Foreign Key %s in table %s keys on multiple columns.  Multiple-columned FKs are not supported by the code generator.\n",
                        $objForeignKey->KeyName, $strTableName);
                } else {
                    // Make sure the column in the FK definition actually exists in this table
                    $strColumnName = $objForeignKey->ColumnNameArray[0];

                    if (array_key_exists(strtolower($strColumnName), $objTable->ColumnArray) &&
                        ($objColumn = $objTable->ColumnArray[strtolower($strColumnName)])
                    ) {

                        // Now, we make sure there is a single-column index for this FK that exists
                        $blnFound = false;
                        if ($objIndexArray = $objTable->IndexArray) {
                            foreach ($objIndexArray as $objIndex) {
                                if ((count($objIndex->ColumnNameArray) == 1) &&
                                    (strtolower($objIndex->ColumnNameArray[0]) == strtolower($strColumnName))
                                ) {
                                    $blnFound = true;
                                }
                            }
                        }

                        if (!$blnFound) {
                            // Single Column Index for this FK does not exist.  Let's create a virtual one and warn
                            $objIndex = new Index();
                            $objIndex->KeyName = sprintf('virtualix_%s_%s', $objTable->Name, $objColumn->Name);
                            $objIndex->Unique = $objColumn->Unique;
                            $objIndex->ColumnNameArray = array($objColumn->Name);

                            $objIndexArray = $objTable->IndexArray;
                            $objIndexArray[] = $objIndex;
                            $objTable->IndexArray = $objIndexArray;

                            if ($objIndex->Unique) {
                                $this->strWarnings .= sprintf("Notice: It is recommended that you add a single-column UNIQUE index on \"%s.%s\" for the Foreign Key %s\r\n",
                                    $strTableName, $strColumnName, $objForeignKey->KeyName);
                            } else {
                                $this->strWarnings .= sprintf("Notice: It is recommended that you add a single-column index on \"%s.%s\" for the Foreign Key %s\r\n",
                                    $strTableName, $strColumnName, $objForeignKey->KeyName);
                            }
                        }

                        // Make sure the table being referenced actually exists
                        if ((array_key_exists(strtolower($objForeignKey->ReferenceTableName), $this->objTableArray)) ||
                            (array_key_exists(strtolower($objForeignKey->ReferenceTableName), $this->objTypeTableArray))
                        ) {

                            // STEP 1: Create the New Reference
                            $objReference = new Reference();

                            // Retrieve the Column object
                            $objColumn = $objTable->ColumnArray[strtolower($strColumnName)];

                            // Setup Key Name
                            $objReference->KeyName = $objForeignKey->KeyName;

                            $strReferencedTableName = $objForeignKey->ReferenceTableName;

                            // Setup IsType flag
                            if (array_key_exists(strtolower($strReferencedTableName), $this->objTypeTableArray)) {
                                $objReference->IsType = true;
                            } else {
                                $objReference->IsType = false;
                            }

                            // Setup Table and Column names
                            $objReference->Table = $strReferencedTableName;
                            $objReference->Column = $objForeignKey->ReferenceColumnNameArray[0];

                            // Setup VariableType
                            $objReference->VariableType = $this->modelClassName($strReferencedTableName);

                            // Setup PropertyName and VariableName
                            $objReference->PropertyName = $this->modelReferencePropertyName($objColumn->Name);
                            $objReference->VariableName = $this->modelReferenceVariableName($objColumn->Name);
                            $objReference->Name = $this->modelReferenceColumnName($objColumn->Name);

                            // Add this reference to the column
                            $objColumn->Reference = $objReference;

                            // References will not have been correctly read earlier, so try again with the reference name
                            $objColumn->Options = $this->objModelConnectorOptions->getOptions($objTable->ClassName,
                                    $objReference->PropertyName) + $objColumn->Options;


                            // STEP 2: Setup the REVERSE Reference for Non Type-based References
                            if (!$objReference->IsType) {
                                // Retrieve the ReferencedTable object
//								$objReferencedTable = $this->objTableArray[strtolower($objReference->Table)];
                                $objReferencedTable = $this->getTable($objReference->Table);
                                $objReverseReference = new ReverseReference();
                                $objReverseReference->Reference = $objReference;
                                $objReverseReference->KeyName = $objReference->KeyName;
                                $objReverseReference->Table = $strTableName;
                                $objReverseReference->Column = $strColumnName;
                                $objReverseReference->NotNull = $objColumn->NotNull;
                                $objReverseReference->Unique = $objColumn->Unique;
                                $objReverseReference->PropertyName = $this->modelColumnPropertyName($strColumnName);

                                $objReverseReference->ObjectDescription = $this->calculateObjectDescription($strTableName,
                                    $strColumnName, $strReferencedTableName, false);
                                $objReverseReference->ObjectDescriptionPlural = $this->calculateObjectDescription($strTableName,
                                    $strColumnName, $strReferencedTableName, true);
                                $objReverseReference->VariableName = $this->modelReverseReferenceVariableName($objTable->Name);
                                $objReverseReference->VariableType = $this->modelReverseReferenceVariableType($objTable->Name);

                                // For Special Case ReverseReferences, calculate Associated MemberVariableName and PropertyName...

                                // See if ReverseReference is due to an ORM-based Class Inheritence Chain
                                if ((count($objTable->PrimaryKeyColumnArray) == 1) && ($objColumn->PrimaryKey)) {
                                    $objReverseReference->ObjectMemberVariable = static::prefixFromType(Type::OBJECT) . $objReverseReference->VariableType;
                                    $objReverseReference->ObjectPropertyName = $objReverseReference->VariableType;
                                    $objReverseReference->ObjectDescription = $objReverseReference->VariableType;
                                    $objReverseReference->ObjectDescriptionPlural = $this->pluralize($objReverseReference->VariableType);
                                    $objReverseReference->Options = $this->objModelConnectorOptions->getOptions($objReference->VariableType,
                                        $objReverseReference->ObjectDescription);

                                    // Otherwise, see if it's just plain ol' unique
                                } else {
                                    if ($objColumn->Unique) {
                                        $objReverseReference->ObjectMemberVariable = $this->calculateObjectMemberVariable($strTableName,
                                            $strColumnName, $strReferencedTableName);
                                        $objReverseReference->ObjectPropertyName = $this->calculateObjectPropertyName($strTableName,
                                            $strColumnName, $strReferencedTableName);
                                        // get override options for codegen
                                        $objReverseReference->Options = $this->objModelConnectorOptions->getOptions($objReference->VariableType,
                                            $objReverseReference->ObjectDescription);
                                    }
                                }

                                $objReference->ReverseReference = $objReverseReference;     // Let forward reference also see things from the other side looking back

                                // Add this ReverseReference to the referenced table's ReverseReferenceArray
                                $objArray = $objReferencedTable->ReverseReferenceArray;
                                array_push($objArray, $objReverseReference);
                                $objReferencedTable->ReverseReferenceArray = $objArray;
                            }
                        } else {
                            $this->strErrors .= sprintf("Foreign Key %s in table %s references a table %s that does not appear to exist.\n",
                                $objForeignKey->KeyName, $strTableName, $objForeignKey->ReferenceTableName);
                        }
                    } else {
                        $this->strErrors .= sprintf("Foreign Key %s in table %s indexes on a column that does not appear to exist.\n",
                            $objForeignKey->KeyName, $strTableName);
                    }
                }
            }
        }

        // Verify: Table Name is valid (alphanumeric + "_" characters only, must not start with a number)
        // and NOT a PHP Reserved Word
        $strMatches = array();
        preg_match('/' . $this->strPatternTableName . '/', $strTableName, $strMatches);
        if (count($strMatches) && ($strMatches[0] == $strTableName) && ($strTableName != '_')) {
            // Setup Reserved Words
            $strReservedWords = explode(',', static::PHP_RESERVED_WORDS);
            for ($intIndex = 0; $intIndex < count($strReservedWords); $intIndex++) {
                $strReservedWords[$intIndex] = strtolower(trim($strReservedWords[$intIndex]));
            }

            $strTableNameToTest = trim(strtolower($strTableName));
            foreach ($strReservedWords as $strReservedWord) {
                if ($strTableNameToTest == $strReservedWord) {
                    $this->strErrors .= sprintf("Table '%s' has a table name which is a PHP reserved word.\r\n",
                        $strTableName);
                    unset($this->objTableArray[strtolower($strTableName)]);
                    return;
                }
            }
        } else {
            $this->strErrors .= sprintf("Table '%s' can only contain characters that are alphanumeric or _, and must not begin with a number.\r\n",
                $strTableName);
            unset($this->objTableArray[strtolower($strTableName)]);
            return;
        }

        // Verify: Column Names are all valid names
        $objColumnArray = $objTable->ColumnArray;
        foreach ($objColumnArray as $objColumn) {
            $strColumnName = $objColumn->Name;
            $strMatches = array();
            preg_match('/' . $this->strPatternColumnName . '/', $strColumnName, $strMatches);
            if (count($strMatches) && ($strMatches[0] == $strColumnName) && ($strColumnName != '_')) {
            } else {
                $this->strErrors .= sprintf("Table '%s' has an invalid column name: '%s'\r\n", $strTableName,
                    $strColumnName);
                unset($this->objTableArray[strtolower($strTableName)]);
                return;
            }
        }

        // Verify: Table has at least one PK
        $blnFoundPk = false;
        $objColumnArray = $objTable->ColumnArray;
        foreach ($objColumnArray as $objColumn) {
            if ($objColumn->PrimaryKey) {
                $blnFoundPk = true;
            }
        }
        if (!$blnFoundPk) {
            $this->strErrors .= sprintf("Table %s does not have any defined primary keys.\n", $strTableName);
            unset($this->objTableArray[strtolower($strTableName)]);
            return;
        }
    }

    protected function analyzeTableColumn(Database\FieldBase $objField, $objTable)
    {
        $objColumn = new SqlColumn();
        $objColumn->Name = $objField->Name;
        $objColumn->OwnerTable = $objTable;
        if (substr_count($objField->Name, "-")) {
            $tableName = $objTable ? " in table " . $objTable->Name : "";
            $this->strErrors .= "Invalid column name" . $tableName . ": " . $objField->Name . ". Dashes are not allowed.";
            return null;
        }

        $objColumn->DbType = $objField->Type;

        $objColumn->VariableType = $this->variableTypeFromDbType($objColumn->DbType);
        $objColumn->VariableTypeAsConstant = Type::constant($objColumn->VariableType);

        $objColumn->Length = $objField->MaxLength;
        $objColumn->Default = $objField->Default;

        $objColumn->PrimaryKey = $objField->PrimaryKey;
        $objColumn->NotNull = $objField->NotNull;
        $objColumn->Identity = $objField->Identity;
        $objColumn->Unique = $objField->Unique;


        $objColumn->Timestamp = $objField->Timestamp;

        $objColumn->VariableName = $this->modelColumnVariableName($objColumn);
        $objColumn->PropertyName = $this->modelColumnPropertyName($objColumn->Name);

        // separate overrides embedded in the comment

        // extract options embedded in the comment field
        if (($strComment = $objField->Comment) &&
            ($pos1 = strpos($strComment, '{')) !== false &&
            ($pos2 = strrpos($strComment, '}', $pos1))
        ) {

            $strJson = substr($strComment, $pos1, $pos2 - $pos1 + 1);
            $a = json_decode($strJson, true);

            if ($a) {
                $objColumn->Options = $a;
                $objColumn->Comment = substr($strComment, 0, $pos1) . substr($strComment,
                        $pos2 + 1); // return comment without options
                if (!empty ($a['Timestamp'])) {
                    $objColumn->Timestamp = true;    // alternate way to specify that a column is a self-updating timestamp
                }
                if ($objColumn->Timestamp && !empty($a['AutoUpdate'])) {
                    $objColumn->AutoUpdate = true;
                }
            } else {
                $objColumn->Comment = $strComment;
            }
        }

        // merge with options found in the design editor, letting editor take precedence
        $objColumn->Options = $this->objModelConnectorOptions->getOptions($objTable->ClassName,
                $objColumn->PropertyName) + $objColumn->Options;

        return $objColumn;
    }

    protected function stripPrefixFromTable($strTableName)
    {
        // If applicable, strip any StripTablePrefix from the table name
        if ($this->intStripTablePrefixLength &&
            (strlen($strTableName) > $this->intStripTablePrefixLength) &&
            (substr($strTableName, 0,
                    $this->intStripTablePrefixLength - strlen($strTableName)) == $this->strStripTablePrefix)
        ) {
            return substr($strTableName, $this->intStripTablePrefixLength);
        }

        return $strTableName;
    }

    protected function getForeignKeyForQcubedRelationshipDefinition($strTableName, $strLine)
    {
        $strTokens = explode('=>', $strLine);
        if (count($strTokens) != 2) {
            $this->strErrors .= sprintf("Could not parse Relationships Script reference: %s (Incorrect Format)\r\n",
                $strLine);
            $this->strRelationshipLinesQcubed[$strLine] = null;
            return null;
        }

        $strSourceTokens = explode('.', $strTokens[0]);
        $strDestinationTokens = explode('.', $strTokens[1]);

        if ((count($strSourceTokens) != 2) ||
            (count($strDestinationTokens) != 2)
        ) {
            $this->strErrors .= sprintf("Could not parse Relationships Script reference: %s (Incorrect Table.Column Format)\r\n",
                $strLine);
            $this->strRelationshipLinesQcubed[$strLine] = null;
            return null;
        }

        $strColumnName = trim($strSourceTokens[1]);
        $strReferenceTableName = trim($strDestinationTokens[0]);
        $strReferenceColumnName = trim($strDestinationTokens[1]);
        $strFkName = sprintf('virtualfk_%s_%s', $strTableName, $strColumnName);

        if (strtolower($strTableName) == trim($strSourceTokens[0])) {
            $this->strRelationshipLinesQcubed[$strLine] = null;
            return $this->getForeignKeyHelper($strLine, $strFkName, $strTableName, $strColumnName,
                $strReferenceTableName, $strReferenceColumnName);
        }

        return null;
    }

    protected function getForeignKeyForSqlRelationshipDefinition($strTableName, $strLine)
    {
        $strMatches = array();

        // Start
        $strPattern = '/alter[\s]+table[\s]+';
        // Table Name
        $strPattern .= '[\[\`\'\"]?(' . $this->strPatternTableName . ')[\]\`\'\"]?[\s]+';

        // Add Constraint
        $strPattern .= '(add[\s]+)?(constraint[\s]+';
        $strPattern .= '[\[\`\'\"]?(' . $this->strPatternKeyName . ')[\]\`\'\"]?[\s]+)?[\s]*';
        // Foreign Key
        $strPattern .= 'foreign[\s]+key[\s]*(' . $this->strPatternKeyName . ')[\s]*\(';
        $strPattern .= '([^)]+)\)[\s]*';
        // References
        $strPattern .= 'references[\s]+';
        $strPattern .= '[\[\`\'\"]?(' . $this->strPatternTableName . ')[\]\`\'\"]?[\s]*\(';
        $strPattern .= '([^)]+)\)[\s]*';
        // End
        $strPattern .= '/';

        // Perform the RegExp
        preg_match($strPattern, $strLine, $strMatches);

        if (count($strMatches) == 9) {
            $strColumnName = trim($strMatches[6]);
            $strReferenceTableName = trim($strMatches[7]);
            $strReferenceColumnName = trim($strMatches[8]);
            $strFkName = $strMatches[5];
            if (!$strFkName) {
                $strFkName = sprintf('virtualfk_%s_%s', $strTableName, $strColumnName);
            }

            if ((strpos($strColumnName, ',') !== false) ||
                (strpos($strReferenceColumnName, ',') !== false)
            ) {
                $this->strErrors .= sprintf("Relationships Script has a foreign key definition with multiple columns: %s (Multiple-columned FKs are not supported by the code generator)\r\n",
                    $strLine);
                $this->strRelationshipLinesSql[$strLine] = null;
                return null;
            }

            // Cleanup strColumnName nad strreferenceColumnName
            $strColumnName = str_replace("'", '', $strColumnName);
            $strColumnName = str_replace('"', '', $strColumnName);
            $strColumnName = str_replace('[', '', $strColumnName);
            $strColumnName = str_replace(']', '', $strColumnName);
            $strColumnName = str_replace('`', '', $strColumnName);
            $strColumnName = str_replace('	', '', $strColumnName);
            $strColumnName = str_replace(' ', '', $strColumnName);
            $strColumnName = str_replace("\r", '', $strColumnName);
            $strColumnName = str_replace("\n", '', $strColumnName);
            $strReferenceColumnName = str_replace("'", '', $strReferenceColumnName);
            $strReferenceColumnName = str_replace('"', '', $strReferenceColumnName);
            $strReferenceColumnName = str_replace('[', '', $strReferenceColumnName);
            $strReferenceColumnName = str_replace(']', '', $strReferenceColumnName);
            $strReferenceColumnName = str_replace('`', '', $strReferenceColumnName);
            $strReferenceColumnName = str_replace('	', '', $strReferenceColumnName);
            $strReferenceColumnName = str_replace(' ', '', $strReferenceColumnName);
            $strReferenceColumnName = str_replace("\r", '', $strReferenceColumnName);
            $strReferenceColumnName = str_replace("\n", '', $strReferenceColumnName);

            if (strtolower($strTableName) == trim($strMatches[1])) {
                $this->strRelationshipLinesSql[$strLine] = null;
                return $this->getForeignKeyHelper($strLine, $strFkName, $strTableName, $strColumnName,
                    $strReferenceTableName, $strReferenceColumnName);
            }

            return null;
        } else {
            $this->strErrors .= sprintf("Could not parse Relationships Script reference: %s (Not in ANSI SQL Format)\r\n",
                $strLine);
            $this->strRelationshipLinesSql[$strLine] = null;
            return null;
        }
    }

    protected function getForeignKeyHelper(
        $strLine,
        $strFkName,
        $strTableName,
        $strColumnName,
        $strReferencedTable,
        $strReferencedColumn
    ) {
        // Make Sure Tables/Columns Exist, or display error otherwise
        if (!$this->validateTableColumn($strTableName, $strColumnName)) {
            $this->strErrors .= sprintf("Could not parse Relationships Script reference: \"%s\" (\"%s.%s\" does not exist)\r\n",
                $strLine, $strTableName, $strColumnName);
            return null;
        }

        if (!$this->validateTableColumn($strReferencedTable, $strReferencedColumn)) {
            $this->strErrors .= sprintf("Could not parse Relationships Script reference: \"%s\" (\"%s.%s\" does not exist)\r\n",
                $strLine, $strReferencedTable, $strReferencedColumn);
            return null;
        }

        return new Database\ForeignKey($strFkName, array($strColumnName), $strReferencedTable,
            array($strReferencedColumn));
    }

    /**
     * This will go through the various Relationships Script lines (if applicable) as setup during
     * the __constructor() through the <relationships> and <relationshipsScript> tags in the
     * configuration settings.
     *
     * If no Relationships are defined, this method will simply exit making no changes.
     *
     * @param string $strTableName Name of the table to pull foreign keys for
     * @param Database\ForeignKey[] Array of currently found DB FK objects which will be appended to
     * @return Database\ForeignKey[] Array of DB FK objects that were parsed out
     */
    protected function getForeignKeysFromRelationshipsScript($strTableName, $objForeignKeyArray)
    {
        foreach ($this->strRelationshipLinesQcubed as $strLine) {
            if ($strLine) {
                $objForeignKey = $this->getForeignKeyForQcubedRelationshipDefinition($strTableName, $strLine);

                if ($objForeignKey) {
                    array_push($objForeignKeyArray, $objForeignKey);
                    $this->strRelationshipLinesQcubed[$strLine] = null;
                }
            }
        }

        foreach ($this->strRelationshipLinesSql as $strLine) {
            if ($strLine) {
                $objForeignKey = $this->getForeignKeyForSqlRelationshipDefinition($strTableName, $strLine);

                if ($objForeignKey) {
                    array_push($objForeignKeyArray, $objForeignKey);
                    $this->strRelationshipLinesSql[$strLine] = null;
                }
            }
        }

        return $objForeignKeyArray;
    }

    public function generateControlId($objTable, $objColumn)
    {
        $strControlId = null;
        if (isset($objColumn->Options['ControlId'])) {
            $strControlId = $objColumn->Options['ControlId'];
        } elseif ($this->blnGenerateControlId) {
            //$strObjectName = $this->modelVariableName($objTable->Name);
            $strClassName = $objTable->ClassName;
            $strControlVarName = $this->modelConnectorVariableName($objColumn);
            //$strLabelName = static::modelConnectorControlName($objColumn);

            $strControlId = $strControlVarName . $strClassName;

        }
        return $strControlId;
    }


    /**
     * Returns a string that will cast a variable coming from the database into a php type.
     * Doing this in the template saves significant amounts of time over using Type::cast() or GetColumn.
     * @param SqlColumn $objColumn
     * @return string
     * @throws \Exception
     */
    public function getCastString(SqlColumn $objColumn)
    {
        switch ($objColumn->DbType) {
            case Database\FieldType::BIT:
                return ('$mixVal = (bool)$mixVal;');

            case Database\FieldType::BLOB:
            case Database\FieldType::CHAR:
            case Database\FieldType::VAR_CHAR:
            case Database\FieldType::JSON:
                return ''; // no need to cast, since its already a string or a null

            case Database\FieldType::DATE:
                return ('$mixVal = new \QCubed\QDateTime($mixVal, null, \QCubed\QDateTime::DATE_ONLY_TYPE);');

            case Database\FieldType::DATE_TIME:
                return ('$mixVal = new \QCubed\QDateTime($mixVal);');

            case Database\FieldType::TIME:
                return ('$mixVal = new \QCubed\QDateTime($mixVal, null, \QCubed\QDateTime::TIME_ONLY_TYPE);');

            case Database\FieldType::FLOAT:
            case Database\FieldType::INTEGER:
                return ('$mixVal = (' . $objColumn->VariableType . ')$mixVal;');

            default:
                throw new \Exception ('Invalid database field type');
        }
    }



    ////////////////////
    // Public Overriders
    ////////////////////

    /**
     * Override method to perform a property "Get"
     * This will get the value of $strName
     *
     * @param string $strName
     * @return array|mixed|SqlTable[]|string
     * @throws Caller
     */
    public function __get($strName)
    {
        switch ($strName) {
            case 'TableArray':
                return $this->objTableArray;
            case 'TypeTableArray':
                return $this->objTypeTableArray;
            case 'DatabaseIndex':
                return $this->intDatabaseIndex;
            case 'CommentConnectorLabelDelimiter':
                return $this->strCommentConnectorLabelDelimiter;
            case 'AutoInitialize':
                return $this->blnAutoInitialize;
            case 'PrivateColumnVars':
                return $this->blnPrivateColumnVars;
            case 'objSettingsXml':
                throw new Caller('The field objSettingsXml is deprecated');
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
     * @param string $strName
     * @param string $mixValue
     * @return void
     */
    public function __set($strName, $mixValue)
    {
        try {
            switch ($strName) {
                default:
                    parent::__set($strName, $mixValue);
            }
        } catch (Caller $objExc) {
            $objExc->incrementOffset();
        }
    }
}

function array_trim(&$strValue)
{
    $strValue = trim($strValue);
}