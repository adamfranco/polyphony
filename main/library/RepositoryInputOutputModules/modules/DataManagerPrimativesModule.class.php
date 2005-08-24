<?php
/**
 *
 * @package polyphony.library.repository.inputoutput
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DataManagerPrimativesModule.class.php,v 1.9 2005/08/24 14:34:42 cws-midd Exp $
 */

/**
 * Require the class we are extending
 * 
 */
require_once(dirname(__FILE__)."/../RepositoryInputOutputModule.interface.php");
require_once(POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/inc.php");

/**
 * InputOutput modules are classes which generate HTML for the display or editing
 * of Records. Which InputOutput module to use is determined by the Format
 * of the RecordStructure corresponding to that Record. For example, a Structure
 * using the "DataManagerPrimitive" Format would use the DataManagerPrimative
 * InputOutput module for displaying generating forms for editing its data.
 * 
 * @package polyphony.library.repository.inputoutput
 * @version $Id: DataManagerPrimativesModule.class.php,v 1.9 2005/08/24 14:34:42 cws-midd Exp $
 * @since $Date: 2005/08/24 14:34:42 $
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
		ArgumentValidator::validate($record, ExtendsValidatorRule::getRule("RecordInterface"));
		ArgumentValidator::validate($wizard, ExtendsValidatorRule::getRule("Wizard"));
		ArgumentValidator::validate($partStructures, ArrayValidatorRuleWithRule::getRule(ExtendsValidatorRule::getRule("PartStructure")));
		
		$recordStructure =& $record->getRecordStructure();
		$recordStructureId =& $recordStructure->getId();
		
		/* build an interface for editing this record! */
		$m = '';

		$step =& $wizard->addStep("record", new WizardStep());
		$step->setDisplayName(dgettext("polyphony","Edit Record"));
		
		// Go through each of the PartStructures and create a component for it
		foreach (array_keys($partStructures) as $key) {
			$partStructure =& $partStructures[$key];
			$m .= $this->_addComponentForPartStructure($step, $record, $partStructure);
			$m .= "<br/>";
		}

		$step->setContent($m);

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
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		
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
		
		$properties =& $wizard->getAllValues();
		$values =& $properties["record"];
		
		// Delete the old parts
		$parts =& $record->getParts();
		while ($parts->hasNext()) {
			$part =& $parts->next();
			$partId =& $part->getId();
			$record->deletePart($partId);
		}
		$parts =& $record->getParts();
		if ($parts->hasNext()) print "have a part!!!"; // debug
		
		// Go through each of the partStructures and save any values as parts.
		$partStructures = $recordStructure->getPartStructures();
		while ($partStructures->hasNext()) {
			$partStructure =& $partStructures->next();
			$partStructureId =& $partStructure->getId();
			$partStructureType = & $partStructure->getType();
			$valueObjClass =& $partStructureType->getKeyword();
			$id = str_replace(".","_",$partStructureId->getIdString());
			
			if ($partStructure->isRepeatable()) {
				// Add a part for each property
				foreach (array_keys($values[$id]) as $valueIndex) {
					$value =& $values[$id][$valueIndex]["value"];
					$newPart =& $record->createPart($partStructureId, $value);
				}
			} else {
				// Add a part for the property
				$value =& $values[$id];
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
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		
		// Get all the partstructures
		$recordStructure =& $record->getRecordStructure();
		$partStructureIterator =& $recordStructure->getPartStructures();
		$partStructures = array();
		while($partStructureIterator->hasNext()) {
			$partStructures[] =& $partStructureIterator->next();
		}
		
		return $this->generateDisplayForPartStructure($asset, $record, $partStructures);
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
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		ArgumentValidator::validate($partStructures, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("PartStructure")));

		$partIterator =& $record->getParts();
		$parts = array();
		while($partIterator->hasNext()) {
			$part =& $partIterator->next();
			$partStructure =& $part->getPartStructure();
			$partStructureId =& $partStructure->getId();
			if (!isset($parts[$partStructureId->getIdString()]) || !is_array($parts[$partStructureId->getIdString()]))
				$parts[$partStructureId->getIdString()] = array();
			$parts[$partStructureId->getIdString()][] =& $part;
		}
		
		// print out the parts;
		ob_start();
		
		foreach (array_keys($partStructures) as $key) {
			$partStructure =& $partStructures[$key];
			$partStructureId =& $partStructure->getId();
			if(isset($parts[$partStructureId->getIdString()])) {	
				if (is_array($parts[$partStructureId->getIdString()])) {
					foreach (array_keys($parts[$partStructureId->getIdString()]) as $key) {
						$part =& $parts[$partStructureId->getIdString()][$key];
						$value =& $part->getValue();
					
						print "\n<strong>".$partStructure->getDisplayName().":</strong> \n";			
						print $value->asString();
						print "\n<br />";
					}
				}
			}
		}
		
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}

	/**
	 * Returns the a string for the passed part structure corresponding to a {@link WizardComponent} that is added automatically.
	 * 
	 * @param object WizardStep $wizard The wizard step to add components.
	 * @param object Record $record The Record to modify.
	 * @param object PartStructure $partStructure The partStructure to add the step for.
	 * @return string
	 * @access public
	 * @since 8/30/04
	 */
	function _addComponentForPartStructure (& $wizardStep, & $record, & $partStructure) {
		
		$partStructureId =& $partStructure->getId();
		$partStructureType =& $partStructure->getType();
		$parts =& $record->getPartsByPartStructure($partStructureId);
		
		// get the display name
		$name = $partStructure->getDisplayName();
		
		// get the id
		$id = str_replace(".","_",$partStructureId->getIdString());
		
		// get the datamanager data type
		$dataType = $partStructureType->getKeyword();
		
		// get the correct component for this data type
		$component =& PrimitiveIOManager::createComponent($dataType);
		if (!$component) return '';
		
		$m = '';
		
		if ($partStructure->isRepeatable()) {
			// replace $component with a wrapper.
			$mult =& new WRepeatableComponentCollection();
			$mult->setElementLayout("[[value]]");
			
			$mult->addComponent("value", $component);
			$mult->setStartingNumber(0);
			
			$component =& $mult;
			
			$m = "<table border='0'><tr><td valign='top'><b>$name</b>:</td><td valign='top'>[[$id]]</td></tr></table>";
			
			// set our values
			while($parts->hasNext()) {
				$part =& $parts->next();
				$collection = array();
				$collection['value'] =& $part->getValue();
				$component->addValueCollection($collection);
				unset($collection);
			}
		} else {
			$m = "<b>$name</b>: [[$id]]";
			
			// set the default value.
			if ($parts->hasNext()) {
				$part =& $parts->next();
				$value =& $part->getValue();
				$component->setValue($value);
			}
		}
			
		// add the component
		$wizardStep->addComponent($id, $component);
		
		return $m;
		
		/* THIS CODE IS OLD
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
						$property->setValue($valueObj->asString());
						if ($partStructure->isRepeatable()) {
							$step->saveCurrentPropertiesAsNewSet();
						} else {
							break;
						}
					}
				}
		}
		*/
	}
}

?>
