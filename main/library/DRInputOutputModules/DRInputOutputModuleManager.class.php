<?php

require_once(dirname(__FILE__)."/modules/DataManagerPrimativesModule.class.php");

/**
 * The DRInputOutModuleManager is responcible for sending records to the 
 * appropriate DRInputOutputModule based on their Schema Formats.
 * 
 * @package polyphony.drinputoutput
 * @version $Id: DRInputOutputModuleManager.class.php,v 1.1 2004/10/19 22:42:46 adamfranco Exp $
 * @date $Date: 2004/10/19 22:42:46 $
 * @copyright 2004 Middlebury College
 */

class DRInputOutputModuleManager {

	/**
	 * Constructor, set up the relations of the Formats to Modules
	 * 
	 * @return object
	 * @access public
	 * @date 10/19/04
	 */
	function DRInputOutputModuleManager () {
		$this->_modules = array();
		$this->_modules["DataManagerPrimatives"] = new DataManagerPrimativesModule;
// 		$this->_modules['Harmoni File'] = new HarmoniFileModule;
// 		$this->_modules['text/plain'] = new PlainTextModule;
	}
	
	
		
	/**
	 * Create wizard steps for editing the values of the specified Record and
	 * add them to the wizard.
	 * 
	 * @param object $record
	 * @param object $wizard The wizard to add the steps to.
	 * @param array $parts An ordered array of the parts to include.
	 * @return void
	 * @access public
	 * @date 10/19/04
	 */
	function createWizardStepsForParts ( & $record, & $wizard, & $parts ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
		ArgumentValidator::validate($parts, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("InfoPart")));
		
		$structure =& $record->getInfoStructure();
		$format = $structure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "DRInputOutputModuleManager", true));
		
		return $this->_modules[$format]->createWizardStepsForParts($record, $wizard, $parts);
	}
	
	/**
	 * Create wizard steps for editing the values of the specified Record and
	 * add them to the wizard.
	 * 
	 * @param object $record
	 * @param object $wizard The wizard to add the steps to.
	 * @return void
	 * @access public
	 * @date 10/19/04
	 */
	function createWizardSteps ( & $record, & $wizard ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
				
		$structure =& $record->getInfoStructure();
		$format = $structure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "DRInputOutputModuleManager", true));
		
		return $this->_modules[$format]->createWizardSteps($record, $wizard);
	}
	
	/**
	 * Get the values submitted in the wizard and update the Record with them.
	 * 
	 * @param object $record
	 * @param object $wizard
	 * @return void
	 * @access public
	 * @date 10/19/04
	 */
	function updateFromWizard ( & $record, & $wizard ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		
		$structure =& $record->getInfoStructure();
		$format = $structure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "DRInputOutputModuleManager", true));
		
		return $this->_modules[$format]->updateFromWizard($record, $wizard);
	}
	
	/**
	 * Generate HTML for displaying the Record
	 * 
	 * @param object $record
	 * @return string
	 * @access public
	 * @date 10/19/04
	 */
	function generateDisplay ( & $record ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		
		$structure =& $record->getInfoStructure();
		$format = $structure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "DRInputOutputModuleManager", true));
		
		return $this->_modules[$format]->generateDisplay($record);
	}
	
	/**
	 * Generate HTML for displaying particular fields of the Record 
	 * 
	 * @param object $record The record to print.
	 * @param array $fields An array of particular fields to print. 
	 * @return string
	 * @access public
	 * @date 10/19/04
	 */
	function generateDisplayForFields ( & $record, & $fields ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		ArgumentValidator::validate($fields, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("InfoField")));
		
		$structure =& $record->getInfoStructure();
		$format = $structure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "DRInputOutputModuleManager", true));
		
		return $this->_modules[$format]->generateDisplayForFields($record, $fields);
	}
	
	/**
	 * Start the service
	 * 
	 * @return void
	 * @access public
	 * @date 6/28/04
	 */
	function start () {
		
	}
	
	/**
	 * Stop the service
	 * 
	 * @return void
	 * @access public
	 * @date 6/28/04
	 */
	function stop () {
		
	}
}

?>