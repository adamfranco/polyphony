<?php

require_once(dirname(__FILE__)."/modules/DataManagerPrimativesModule.class.php");
require_once(dirname(__FILE__)."/modules/HarmoniFileModule.class.php");

/**
 * The RepositoryInputOutModuleManager is responcible for sending records to the 
 * appropriate RepositoryInputOutputModule based on their Schema Formats.
 * 
 * @package polyphony.repository.inputoutput
 * @version $Id: RepositoryInputOutputModuleManager.class.php,v 1.2 2005/01/27 17:13:10 adamfranco Exp $
 * @date $Date: 2005/01/27 17:13:10 $
 * @copyright 2004 Middlebury College
 */

class RepositoryInputOutputModuleManager {

	/**
	 * Constructor, set up the relations of the Formats to Modules
	 * 
	 * @return object
	 * @access public
	 * @date 10/19/04
	 */
	function RepositoryInputOutputModuleManager () {
		$this->_modules = array();
		$this->_modules["DataManagerPrimatives"] =& new DataManagerPrimativesModule;
 		$this->_modules['Harmoni File'] =& new HarmoniFileModule;
// 		$this->_modules['text/plain'] = new PlainTextModule;
	}
	
	
		
	/**
	 * Create wizard steps for editing the values of the specified Record and
	 * add them to the wizard.
	 * 
	 * @param object $record
	 * @param object $wizard The wizard to add the steps to.
	 * @param array $partStructures An ordered array of the partStructures to include.
	 * @return void
	 * @access public
	 * @date 10/19/04
	 */
	function createWizardStepsForPartStructures ( & $record, & $wizard, & $partStructures ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("Record"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
		ArgumentValidator::validate($partStructures, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("PartStructure")));
		
		$recordStructure =& $record->getRecordStructure();
		$format = $recordStructure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "RepositoryInputOutputModuleManager", true));
		
		return $this->_modules[$format]->createWizardStepsForPartStructures($record, $wizard, $partStructures);
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
		ArgumentValidator::validate($record, new ExtendsValidatorRule("Record"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
				
		$recordStructure =& $record->getRecordStructure();
		$format = $recordStructure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "RepositoryInputOutputModuleManager", true));
		
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
		ArgumentValidator::validate($record, new ExtendsValidatorRule("Record"));
		
		$recordStructure =& $record->getRecordStructure();
		$format = $recordStructure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "RepositoryInputOutputModuleManager", true));
		
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
	function generateDisplay ( & $repositoryId, & $assetId, & $record ) {
		ArgumentValidator::validate($repositoryId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("Record"));
		
		$recordStructure =& $record->getRecordStructure();
		$format = $recordStructure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "RepositoryInputOutputModuleManager", true));
		
		return $this->_modules[$format]->generateDisplay($repositoryId, $assetId, $record);
	}
	
	/**
	 * Generate HTML for displaying particular fields of the Record 
	 * 
	 * @param object $record The record to print.
	 * @param array $partStructures An array of partStructures to print. 
	 * @return string
	 * @access public
	 * @date 10/19/04
	 */
	function generateDisplayForPartStructures ( & $repositoryId, & $assetId, & $record, & $partStructures ) {
		ArgumentValidator::validate($repositoryId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("Record"));
		ArgumentValidator::validate($partStructures, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("PartStructure")));
		
		$recordStructure =& $record->getRecordStructure();
		$format = $recordStructure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "RepositoryInputOutputModuleManager", true));
		
		return $this->_modules[$format]->generateDisplayForPartStructures($repositoryId, $assetId, $record, $partStructures);
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