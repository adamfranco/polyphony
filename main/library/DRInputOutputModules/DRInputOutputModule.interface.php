<?php

/**
 * InputOutput modules are classes which generate HTML for the display or editing
 * of InfoRecords. Which InputOutput module to use is determined by the Format
 * of the InfoStructure corresponding to that InfoRecord. For example, a Structure
 * using the "DataManagerPrimitive" Format would use the DataManagerPrimative
 * InputOutput module for displaying generating forms for editing its data.
 * 
 * @package polyphony.drinputoutput
 * @version $Id: DRInputOutputModule.interface.php,v 1.1 2004/10/19 22:42:46 adamfranco Exp $
 * @date $Date: 2004/10/19 22:42:46 $
 * @copyright 2004 Middlebury College
 */

class DRInputOutputModuleInterface {
		
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
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
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
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
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
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
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
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
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
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
}

?>