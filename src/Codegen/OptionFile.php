<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Codegen;

/**
 * Redefine __CODEGEN_OPTION_FILE__ if you want your file to be in a different location
 */
use QCubed\ObjectBase;

if (!defined("__CODEGEN_OPTION_FILE__")) {
    define("__CODEGEN_OPTION_FILE__", QCUBED_CONFIG_DIR . '/codegen_options.json');
}


/**
 * Class OptionFile
 * Interface to the option file that lets you specify various hand edited and automated options
 * per field. We currently use this for the ModelConnectorEditor, but it could potentialy be
 * used for other things too.
 *
 * Regarding the choice of json file: we needed a file format that works well hand editing, but also can
 * look good when machine generated. There are a few choices: XML is somewhat cumbersome, and is not completely
 * straight forward when moving to PHP objects. YML would require people learning YML, they have enough to do.
 * PHP objects can be output, but they don't look every good when output by machine. JSON seemed to be the one
 * that was easiest to implement with the needed requirements.
 *
 * Note that this ties table and field names in the database to these options. If the table or field name
 * changes in the database, the options will be lost. We can try to guess as to whether changes were made based upon
 * the index of the changes in the field list, but not entirely easy to do. Best would be for developer to hand-code
 * the changes in the json file in this case.
 *
 * This will be used by the designer to record the changes in preparation for codegen.
 * @package QCubed\Codegen
 */
class OptionFile extends ObjectBase
{
    protected $options = array();
    protected $blnChanged = false;

    const TABLE_OPTIONS_FIELD_NAME = '*';

    public function __construct()
    {
        if (file_exists(__CODEGEN_OPTION_FILE__)) {
            $strContent = file_get_contents(__CODEGEN_OPTION_FILE__);

            if ($strContent) {
                $this->options = json_decode($strContent, true);
            }
        }

        // TODO: Analyze the result for changes and make a guess as to whether a table name or field name was changed
    }

    /**
     * Save the current configuration into the options file.
     */
    function save()
    {
        if (!$this->blnChanged) {
            return;
        }
        $flags = JSON_PRETTY_PRINT;
        $strContent = json_encode($this->options, $flags);

        file_put_contents(__CODEGEN_OPTION_FILE__, $strContent);
        $this->blnChanged = false;
    }

    /**
     * Makes sure save is the final step.
     */
    function __destruct()
    {
        $this->save();
    }


    /**
     * Set an option for a widget associated with the given table and field.
     *
     * @param $strTableName
     * @param $strFieldName
     * @param $strOptionName
     * @param $mixValue
     */
    public function setOption($strTableName, $strFieldName, $strOptionName, $mixValue)
    {
        $this->options[$strTableName][$strFieldName][$strOptionName] = $mixValue;
        $this->blnChanged = true;
    }

    /**
     * Bulk option setting.
     *
     * @param $strClassName
     * @param $strFieldName
     * @param $mixValue
     */
    public function setOptions($strClassName, $strFieldName, $mixValue)
    {
        if (empty ($mixValue)) {
            unset($this->options[$strClassName][$strFieldName]);
        } else {
            $this->options[$strClassName][$strFieldName] = $mixValue;
        }
        $this->blnChanged = true;
    }

    /**
     * Remove the option
     *
     * @param $strClassName
     * @param $strFieldName
     * @param $strOptionName
     */
    public function unsetOption($strClassName, $strFieldName, $strOptionName)
    {
        unset ($this->options[$strClassName][$strFieldName][$strOptionName]);
        $this->blnChanged = true;
    }

    /**
     * Lookup an option.
     *
     * @param $strClassName
     * @param $strFieldName
     * @param $strOptionName
     * @return mixed
     */
    public function getOption($strClassName, $strFieldName, $strOptionName)
    {
        if (isset ($this->options[$strClassName][$strFieldName][$strOptionName])) {
            return $this->options[$strClassName][$strFieldName][$strOptionName];
        } else {
            return null;
        }
    }

    /**
     * Return all the options associated with the given table and field.
     * @param $strClassName
     * @param $strFieldName
     * @return mixed
     */
    public function getOptions($strClassName, $strFieldName)
    {
        if (isset($this->options[$strClassName][$strFieldName])) {
            return $this->options[$strClassName][$strFieldName];
        } else {
            return array();
        }
    }

}