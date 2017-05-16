<?php
/**
 * QCodeGen
 *
 * Overrides the Codegen\AbstractBase class.
 *
 * Feel free to override any of those methods here to customize your code generation.
 *
 */

namespace QCubed\Project\Codegen;

/**
 * Class Codegen
 *
 * Overrides the default codegen class. Override and implement any functions here to customize the code generation process.
 * @package Project
 * @was QCodeGen
 */
class CodegenBase extends \QCubed\Codegen\CodegenBase {

	/**
	 * Construct the CodeGen object.
	 *
	 * Gives you an opportunity to read your xml file and make codegen changes accordingly.
	 */
	public function __construct($objSettingsXml) {
		// Specify the paths to your template files here. These paths will be searched in the order declared, to
		// find a particular template file. Template files found lower down in the order will override the previous ones.
		static::$TemplatePaths = array (
			QCUBED_BASE_DIR . '/orm/templates/',
            QCUBED_BASE_DIR . '/application/codegen/templates/'

            //QCUBED_PROJECT_INCLUDES_DIR . '/codegen/templates/'
		);
	}

	/**
	 * QCodeGen::Pluralize()
	 *
	 * Example: Overriding the Pluralize method
	 *
	 * @param string $strName
	 * @return string
	 */
	protected function Pluralize($strName) {
		// Special Rules go Here
		switch (true) {
			case ($strName == 'person'):
				return 'people';
			case ($strName == 'Person'):
				return 'People';
			case ($strName == 'PERSON'):
				return 'PEOPLE';

			// Trying to be cute here...
			case (strtolower($strName) == 'fish'):
				return $strName . 'ies';

			// Otherwise, call parent
			default:
				return parent::Pluralize($strName);
		}
	}
}

