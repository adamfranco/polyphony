<?php
/**
 *
 * @package polyphony.library.repository.inputoutput
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DataManagerPrimativesModule.class.php,v 1.2 2005/02/04 23:06:11 adamfranco Exp $
 */

/**
 * Require the class we are extending
 * 
 */
require_once(dirname(__FILE__)."/../RepositoryInputOutputModule.interface.php");

/**
 * InputOutput modules are classes which generate HTML for the display or editing
 * of Records. Which InputOutput module to use is determined by the Format
 * of the RecordStructure corresponding to that Record. For example, a Structure
 * using the "DataManagerPrimitive" Format would use the DataManagerPrimative
 * InputOutput module for displaying generating forms for editing its data.
 * 
 * @package polyphony.library.repository.inputoutput
 * @version $Id: DataManagerPrimativesModule.class.php,v 1.2 2005/02/04 23:06:11 adamfranco Exp $
 * @since $Date: 2005/02/04 23:06:11 $
 * @copyright 2004 Middlebury College
 */

class DataManagerPrimativesModule
	extends RepositoryInputOutputModuleInterface {
	
	/**
	 * Constructor
	 * 
	 * @return obj
	 * @access public
	 * @since 10/19/04
	 */
	function DataManagerPrimativesModule () {
		
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
	 * @since 10/19/04
	 */
	function createWizardStepsForPartStructures ( & $record, & $wizard, & $partStructures ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("Record"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
		ArgumentValidator::validate($partStructures, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("PartStructure")));
		
		$recordStructure =& $record->getRecordStructure();
		$recordStructureId =& $recordStructure->getId();
		
		// Go through each of the PartStructures and create a step for it.
		foreach (array_keys($partStructures) as $key) {
			$partStructure =& $partStructures[$key];
			$this->_addPartStructureStep($wizard, $record, $partStructure);
		}
	}
	
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
	function createWizardSteps ( & $record, & $wizard ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("Record"));
		
		$recordStructure =& $record->getRecordStructure();
		$recordStructureId =& $recordStructure->getId();
		
		// Get all the partStrucutres
		$partStructureIterator =& $recordStructure->getPartStructures();
		$partStructures = array();
		while($partStructureIterator->hasNext()) {
			$partStructures[] =& $partStructureIterator->next();
		}
		
		$this->createWizardStepsForPartStructures($record, $wizard, $partStructures);
	}
	
	/**
	 * Get the values submitted in the wizard and update the Record with them.
	 * 
	 * @param object $record
	 * @param object $wizard
	 * @return void
	 * @access public
	 * @since 10/19/04
	 */
	function updateFromWizard ( & $record, & $wizard ) {
		$recordStructure =& $record->getRecordStructure();
		$recordStructureId =& $recordStructure->getId();
		
		$properties =& $wizard->getProperties();
		
		// Delete the old parts
		$parts =& $record->getParts();
		while ($parts->hasNext()) {
			$part =& $parts->next();
			$partId =& $part->getId();
			$record->deletePart($partId);
			
		}
		
		// Go through each of the partStructures and save any values as parts.
		$partStructures = $recordStructure->getPartStructures();
		while ($partStructures->hasNext()) {
			$partStructure =& $partStructures->next();
			$partStructureId =& $partStructure->getId();
			$partStructureType = & $partStructure->getType();
			$valueObjClass =& $partStructureType->getKeyword();
			
			if ($partStructure->isRepeatable()) {
				// Add a part for each property
				foreach (array_keys($properties[$partStructureId->getIdString()]) as $setIndex) {
					$set =& $properties[$partStructureId->getIdString()][$setIndex];
					foreach (array_keys($set) as $propertyKey) {
						$property =& $set[$propertyKey];
						$value =& new $valueObjClass($property->getValue());
						$newPart =& $record->createPart($partStructureId, $value);
					}
				}
			} else {
				// Add a part for the property
				$property =& $properties[$partStructureId->getIdString()];
				$value =& new $valueObjClass($property->getValue());
				$newPart =& $record->createPart($partStructureId, $value);
			}
		}
	}

	/**
	 * Generate HTML for displaying the Record
	 * 
	 * @param object $record
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function generateDisplay ( & $repositoryId, & $assetId, & $record ) {
		ArgumentValidator::validate($repositoryId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("Record"));
		
		// Get all the partstructures
		$recordStructure =& $record->getRecordStructure();
		$partStructureIterator =& $recordStructure->getPartStructures();
		$partStructures = array();
		while($partStructureIterator->hasNext()) {
			$partStructures[] =& $partStructureIterator->next();
		}
		
		return $this->generateDisplayForParts($asset, $record, $partStructures);
	}

	/**
	 * Generate HTML for displaying particular parts of the Record 
	 * 
	 * @param object $record The record to print.
	 * @param array $partStructures An array of particular partstructures to print. 
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function generateDisplayForPartStructures ( &$repositoryId, & $assetId, & $record, & $partStructures ) {
		ArgumentValidator::validate($repositoryId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("Record"));
		ArgumentValidator::validate($partStructures, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("PartStructure")));

		$partIterator =& $record->getParts();
		$parts = array();
		while($partIterator->hasNext()) {
			$part =& $partIterator->next();
			$partStructure =& $part->getPartStructure();
			$partStructureId =& $partStructure->getId();
			if (!is_array($parts[$partStructureId->getIdString()]))
				$parts[$partStructureId->getIdString()] = array();
			$parts[$partStructureId->getIdString()][] =& $part;
		}
		
		// print out the parts;
		ob_start();
		
		foreach (array_keys($partStructures) as $key) {
			$partStructure =& $partStructures[$key];
			$partStructureId =& $partStructure->getId();
			
			foreach (array_keys($parts[$partStructureId->getIdString()]) as $key) {
				$part =& $parts[$partStructureId->getIdString()][$key];
				$value =& $part->getValue();
				
				print "\n<strong>".$partStructure->getDisplayName().":</strong> \n";			
				print $value->toString();
				print "\n<br />";
			}
		}
		
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}

	/**
	 * Add a step for an PartStructure
	 * 
	 * @param object Wizard $wizard The wizard to add the step to.
	 * @param object Record $record The Record to modify.
	 * @param object PartStructure $partStructure The partStructure to add the step for.
	 * @return void
	 * @access public
	 * @since 8/30/04
	 */
	function _addPartStructureStep (& $wizard, & $record, & $partStructure) {
		
		$partStructureId =& $partStructure->getId();
		$partStructureType =& $partStructure->getType();
		$parts =& $record->getParts();
		
		// Create the step
		if ($partStructure->isRepeatable()) {
			$step =& $wizard->addStep(new MultiValuedWizardStep($partStructure->getDisplayName(), strval($partStructureId->getIdString())));
		} else {
			$step =& $wizard->createStep($partStructure->getDisplayName());
		}
		
		// Switch for any special partStructure types
		switch (TRUE) {
			
			// default Works for most part types
			default:
				$property =& $step->createProperty(strval($partStructureId->getIdString()),
									new AlwaysTrueValidatorRule,
									$partStructure->isMandatory());
				
				ob_start();
				print "\n<em>".$partStructure->getDescription()."</em>\n<hr />";
				print "\n<br /><strong>".$partStructure->getDisplayName()."</strong>:";
				print " <input type='text'";
				print " name='".$partStructureId->getIdString()."'";
				print " value='[[".$partStructureId->getIdString()."]]' /> ";
				print " [[".$partStructureId->getIdString()."|Error]]";
				if ($partStructure->isRepeatable()) {
					print "\n<br />[Buttons] <em>"._("Click here to save the value above.")."</em>";
					print "\n<br /><hr />";
					print _("Already Added:");
					print "\n<table>";
					print "[List]\n<tr>";
					print "\n<td valign='top'>[ListButtons]<br />[ListMoveButtons]</td>";
					print "\n<td style='padding-bottom: 20px'>";
					print "\n\t<strong>".$partStructure->getDisplayName().":</strong>"
						." [[".$partStructureId->getIdString()."]]";
					print "</td>\n</tr>[/List]\n</table>";
				}
				$step->setText(ob_get_contents());
				ob_end_clean();
				
				// If we have parts, load their values as the defaults.
				while ($parts->hasNext()) {
					$part =& $parts->next();
					$currentPartStructure =& $part->getPartStructure();
					
					if ($partStructureId->isEqual($currentPartStructure->getId())) {
						$valueObj =& $part->getValue();
						$property->setValue($valueObj->toString());
						if ($partStructure->isRepeatable()) {
							$step->saveCurrentPropertiesAsNewSet();
						} else {
							break;
						}
					}
				}
		}
	}
}

?>