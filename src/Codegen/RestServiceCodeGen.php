<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Codegen;

use QCubed\Exception\Caller;
use QCubed\Project\Codegen\CodegenBase as QCodegen;

/**
 * RestServiceCodegen
 *
 * This is a stub file for you to generate a reference to a rest service.
 *
 * @package Codegen
 */
class RestServiceCodeGen extends QCodegen
{
    // REST Service-specific Attributes
    protected $strServiceUrl;

    public function __construct($objSettingsXml)
    {
        parent::__construct($objSettingsXml);
        // Lookup Instance-Specific Configuration from the SettingsXml Node
        $this->strServiceUrl = self::lookupSetting($objSettingsXml, null, 'serviceUrl');
    }

    public function getTitle()
    {
        return sprintf('REST Service (%s)', $this->strServiceUrl);
    }

    public function getConfigXml()
    {
        $strCrLf = "\r\n";
        $strToReturn = sprintf('		<restService url="%s">%s', $this->strServiceUrl, $strCrLf);
        $strToReturn .= sprintf('		</restService>%s', $strCrLf);
        return $strToReturn;
    }

    public function getReportLabel()
    {
        return 'There were 2 REST Services available to attempt code generation:';
    }

    public function generateAll()
    {
        $strReport = '';
        $strReport .= "Successfully generated REST Service Class:   TestBlahservice\r\n";
        return $strReport;
    }


    ////////////////////
    // Public Overriders
    ////////////////////

    /**
     * Override method to perform a property "Get"
     * This will get the value of $strName
     *
     * @param string strName Name of the property to get
     * @return mixed
     * @throws Caller
     */
    public function __get($strName)
    {
        switch ($strName) {
            case 'ServiceUrl':
                return $this->strServiceUrl;
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