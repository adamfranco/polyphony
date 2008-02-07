<?php
/**
 * @package polyphony.repository.inputoutput
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryInputOutputModule.interface.php,v 1.7 2008/02/07 20:09:03 adamfranco Exp $
 */

/**
 * InputOutput modules are classes which generate HTML for the display or editing
 * of Records. Which InputOutput module to use is determined by the Format
 * of the RecordStructure corresponding to that Record. For example, a Structure
 * using the "DataManagerPrimitive" Format would use the DataManagerPrimative
 * InputOutput module for displaying generating forms for editing its data.
 *
 * @package polyphony.repository.inputoutput
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryInputOutputModule.interface.php,v 1.7 2008/02/07 20:09:03 adamfranco Exp $
 */

interface RepositoryInputOutputModuleInterface {
		
	/**
	 * Create wizard steps for editing the values of the specified Record and
	 * add them to the wizard.
	 * 
	 * @param object $record
	 * @param object $wizard The wizard to add the steps to.
	 * @return void
	 * @access public
	 * @since 10/19/04
	 */
	function createWizardSteps ( Record $record, Wizard $wizard ) ;
	
	/**
	 * Create wizard steps for editing the values of the specified Record and
	 * add them to the wizard.
	 * 
	 * @param object $record
	 * @param object $wizard The wizard to add the steps to.
	 * @param array $partStructures An ordered array of the partstructures to include.
	 * @return void
	 * @access public
	 * @since 10/19/04
	 */
	function createWizardStepsForPartStructures ( Record $record, Wizard $wizard, array $partStructures );
	
	/**
	 * Get the values submitted in the wizard and update the Record with them.
	 * 
	 * @param object $record
	 * @param object $wizard
	 * @return void
	 * @access public
	 * @since 10/19/04
	 */
	function updateFromWizard ( Record $record, Wizard $wizard );
	
	/**
	 * Generate HTML for displaying the Record
	 * 
	 * @param object $record
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function generateDisplay ( Id $repositoryId, Id $assetId, Record $record );
	
	/**
	 * Generate HTML for displaying particular parts of the Record 
	 * 
	 * @param object $record The record to print.
	 * @param array $partStructures An array of particular partstructures to print. 
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function generateDisplayForPartStructures ( Id $repositoryId, Id $assetId, Record $record, array $partStructures );
}

?>