<?php

require_once(dirname(__FILE__)."/../DRInputOutputModule.interface.php");

/**
 * InputOutput modules are classes which generate HTML for the display or editing
 * of InfoRecords. Which InputOutput module to use is determined by the Format
 * of the InfoStructure corresponding to that InfoRecord. For example, a Structure
 * using the "DataManagerPrimitive" Format would use the DataManagerPrimative
 * InputOutput module for displaying generating forms for editing its data.
 * 
 * @package polyphony.dr.inputoutput
 * @version $Id: DataManagerPrimativesModule.class.php,v 1.4 2004/12/22 17:02:42 adamfranco Exp $
 * @date $Date: 2004/12/22 17:02:42 $
 * @copyright 2004 Middlebury College
 */

class DataManagerPrimativesModule
	extends DRInputOutputModuleInterface {
	
	/**
	 * Constructor
	 * 
	 * @return obj
	 * @access public
	 * @date 10/19/04
	 */
	function DataManagerPrimativesModule () {
		
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
		$structureId =& $structure->getId();
		
		// Go through each of the infoParts and create a step for it.
		foreach (array_keys($parts) as $key) {
			$part =& $parts[$key];
			$this->_addPartStep($wizard, $record, $part);
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
	 * @date 10/19/04
	 */
	function createWizardSteps ( & $record, & $wizard ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		
		$structure =& $record->getInfoStructure();
		$structureId =& $structure->getId();
		
		// Get all the parts
		$partIterator =& $structure->getInfoParts();
		$parts = array();
		while($partIterator->hasNext()) {
			$parts[] =& $partIterator->next();
		}
		
		$this->createWizardStepsForParts($record, $wizard, $parts);
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
		$structure =& $record->getInfoStructure();
		$structureId =& $structure->getId();
		
		$properties =& $wizard->getProperties();
		
		// Delete the old fields
		$fields =& $record->getInfoFields();
		while ($fields->hasNext()) {
			$field =& $fields->next();
			$fieldId =& $field->getId();
			$record->deleteInfoField($fieldId);
			
		}
		
		// Go through each of the parts and save any values as fields.
		$parts = $structure->getInfoParts();
		while ($parts->hasNext()) {
			$part =& $parts->next();
			$partId =& $part->getId();
			$partType = & $part->getType();
			$valueObjClass =& $partType->getKeyword();
			
			if ($part->isRepeatable()) {
				// Add a field for each property
				foreach (array_keys($properties[$partId->getIdString()]) as $setIndex) {
					$set =& $properties[$partId->getIdString()][$setIndex];
					foreach (array_keys($set) as $propertyKey) {
						$property =& $set[$propertyKey];
						$value =& new $valueObjClass($property->getValue());
						$newField =& $record->createInfoField($partId, $value);
					}
				}
			} else {
				// Add a field for the property
				$property =& $properties[$partId->getIdString()];
				$value =& new $valueObjClass($property->getValue());
				$newField =& $record->createInfoField($partId, $value);
			}
		}
	}

	/**
	 * Generate HTML for displaying the Record
	 * 
	 * @param object $record
	 * @return string
	 * @access public
	 * @date 10/19/04
	 */
	function generateDisplay ( & $drId, & $assetId, & $record ) {
		ArgumentValidator::validate($drId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		
		// Get all the parts
		$structure =& $record->getInfoStructure();
		$partIterator =& $structure->getInfoParts();
		$parts = array();
		while($partIterator->hasNext()) {
			$parts[] =& $partIterator->next();
		}
		
		return $this->generateDisplayForFields($asset, $record, $parts);
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
	function generateDisplayForFields ( &$drId, & $assetId, & $record, & $parts ) {
		ArgumentValidator::validate($drId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		ArgumentValidator::validate($parts, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("InfoPart")));

		$fieldIterator =& $record->getInfoFields();
		$fields = array();
		while($fieldIterator->hasNext()) {
			$field =& $fieldIterator->next();
			$part =& $field->getInfoPart();
			$partId =& $part->getId();
			if (!is_array($fields[$partId->getIdString()]))
				$fields[$partId->getIdString()] = array();
			$fields[$partId->getIdString()][] =& $field;
		}
		
		// print out the fields;
		ob_start();
		
		foreach (array_keys($parts) as $key) {
			$part =& $parts[$key];
			$partId =& $part->getId();
			
			foreach (array_keys($fields[$partId->getIdString()]) as $key) {
				$field =& $fields[$partId->getIdString()][$key];
				$value =& $field->getValue();
				
				print "\n<strong>".$part->getDisplayName().":</strong> \n";			
				print $value->toString();
				print "\n<br />";
			}
		}
		
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}

	/**
	 * Add a step for an infoPart
	 * 
	 * @param object Wizard $wizard The wizard to add the step to.
	 * @param object Record $record The Record to modify.
	 * @param object InfoPart $part The part to add the step for.
	 * @return void
	 * @access public
	 * @date 8/30/04
	 */
	function _addPartStep (& $wizard, & $record, & $part) {
		
		$partId =& $part->getId();
		$partType =& $part->getType();
		$fields =& $record->getInfoFields();
		
		// Create the step
		if ($part->isRepeatable()) {
			$step =& $wizard->addStep(new MultiValuedWizardStep($part->getDisplayName(), strval($partId->getIdString())));
		} else {
			$step =& $wizard->createStep($part->getDisplayName());
		}
		
		// Switch for any special part types
		switch (TRUE) {
			
			// default Works for most field types
			default:
				$property =& $step->createProperty(strval($partId->getIdString()),
									new AlwaysTrueValidatorRule,
									$part->isMandatory());
				
				ob_start();
				print "\n<em>".$part->getDescription()."</em>\n<hr />";
				print "\n<br /><strong>".$part->getDisplayName()."</strong>:";
				print " <input type='text'";
				print " name='".$partId->getIdString()."'";
				print " value='[[".$partId->getIdString()."]]' /> ";
				print " [[".$partId->getIdString()."|Error]]";
				if ($part->isRepeatable()) {
					print "\n<br />[Buttons] <em>"._("Click here to save the value above.")."</em>";
					print "\n<br /><hr />";
					print _("Already Added:");
					print "\n<table>";
					print "[List]\n<tr>";
					print "\n<td valign='top'>[ListButtons]<br />[ListMoveButtons]</td>";
					print "\n<td style='padding-bottom: 20px'>";
					print "\n\t<strong>".$part->getDisplayName().":</strong>"
						." [[".$partId->getIdString()."]]";
					print "</td>\n</tr>[/List]\n</table>";
				}
				$step->setText(ob_get_contents());
				ob_end_clean();
				
				// If we have fields, load their values as the defaults.
				while ($fields->hasNext()) {
					$field =& $fields->next();
					$currentPart =& $field->getInfoPart();
					
					if ($partId->isEqual($currentPart->getId())) {
						$valueObj =& $field->getValue();
						$property->setValue($valueObj->toString());
						if ($part->isRepeatable()) {
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