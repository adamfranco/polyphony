<?

require_once(dirname(__FILE__)."/WizardProperty.class.php");
require_once(dirname(__FILE__)."/WizardStep.interface.php");

/**
 * The Wizard class provides a system for registering Wizard properties and 
 * associating those properties with the appropriate form elements.
 *
 * @package polyphony.wizard
 * @author Adam Franco
 * @copyright 2004 Middlebury College
 * @access public
 * @version $Id: MultiValuedWizardStep.class.php,v 1.1 2004/07/16 22:14:31 adamfranco Exp $
 */

class MultiValuedWizardStep 
	extends WizardStepInterface {
	
	/**
	 * The displayName of this WizardStep
	 * @attribute private string _displayName
	 */
	var $_displayName;
	
	/**
	 * The properties handled by the Wizard.
	 * @attribute private array _properties
	 */
	var $_properties;
	
	/**
	 * Sets of properties created in this step
	 * @attribute private array _propertySets
	 */
	var $_propertySets;
	
	/**
	 * Constructor
	 * @param string $displayName The displayName of this step.
	 */
	function MultiValuedWizardStep ( $displayName ) {
		ArgumentValidator::validate($displayName, new StringValidatorRule, true);
		
		$this->_displayName = $displayName;
		$this->_properties = array();
		$this->_propertySets = array();
	}
	
	/**
	 * Returns the displayName of this WizardStep
	 * @return string
	 */
	function getDisplayName () {
		return $this->_displayName;
	}
	
	/**
	 * creates a new Property for this step
	 * @parm string $propertyName The property name.
	 * @param object $validatorRule A ValidatorRule (that exends the 
	 * 		ValidatorRuleInterface) that will be used to validate the form
	 *		inputs
	 * @return object WizardProperty
	 */
	function & createProperty ( $propertyName, & $validatorRule, $isValueRequired = TRUE ) {
		ArgumentValidator::validate($propertyName, new StringValidatorRule, true);
		ArgumentValidator::validate($validatorRule, new ExtendsValidatorRule("ValidatorRuleInterface"), true);
		ArgumentValidator::validate($isValueRequired, new BooleanValidatorRule, true);
		
		if ($this->_properties[$propertyName])
			throwError(new Error("Property, ".$propertyName.", already exists in Wizard.", "Wizard", 1));

		$this->_properties[$propertyName] =& new WizardProperty( $propertyName, $validatorRule, $isValueRequired );
		$this->_registeredProperties[$propertyName] = array( "propertyName" => $propertyName, "validatorRule" => $validatorRule, "isValueRequired" => $isValueRequired );

		return $this->_properties[$propertyName];
	}
	
	/**
	 * Save the values of the currentProperties as a new Set.
	 * 
	 * @return integer The index of the new set
	 * @access public
	 * @date 7/14/04
	 */
	function saveCurrentPropertiesAsNewSet () {
		$newIndex = count($this->_propertySets);
		$this->_propertySets[$newIndex] = array();
		
		foreach (array_keys($this->_properties) as $propertyName) {
			$this->_propertySets[$newIndex][$propertyName] = $this->_properties[$propertyName]->getValue();
		}
		
		return $newIndex;
	}
	
	/**
	 * Save the values of the currentProperties to $setIndex.
	 * 
	 * @param integer $setIndex The index of the set to save to.
	 * @return void
	 * @access public
	 * @date 7/14/04
	 */
	function saveCurrentPropertiesToSet ($setIndex) {
		if (!$this->_propertySets[$setIndex])
			throwError(new Error("Unknown setIndex, '$setIndex'.", "Wizard"));
		
		foreach (array_keys($this->_properties) as $propertyName) {
			$this->_propertySets[$setIndex][$propertyName] = $this->_properties[$propertyName]->getValue();
		}
	}	

	/**
	 * Load the values of $setIndex into the currentProperties.
	 * 
	 * @param integer $setIndex The index of the set to load.
	 * @return void
	 * @access public
	 * @date 7/14/04
	 */
	function loadCurrentPropertiesFromSet ($setIndex) {
		if (!$this->_propertySets[$setIndex])
			throwError(new Error("Unknown setIndex, '$setIndex'.", "Wizard"));
		
		foreach (array_keys($this->_properties) as $propertyName) {
			$this->_properties[$propertyName]->setValue($this->_propertySets[$setIndex][$propertyName]);
		}
	}
	
	/**
	 * Delete a set of property values
	 * 
	 * @param integer $setIndex The index of the set to delete
	 * @return void
	 * @access public
	 * @date 7/15/04
	 */
	function deleteSet ($setIndex) {
		if (!$this->_propertySets[$setIndex])
			throwError(new Error("Unknown setIndex, '$setIndex'.", "Wizard"));
		
		unset($this->_propertySets[$setIndex]);
	}
	
	/**
	 * Move the Set up in the order (closer to 0)
	 * 
	 * @param integer $setIndex The set to move.
	 * @return void
	 * @access public
	 * @date 7/16/04
	 */
	function moveSetUp ( $setIndex ) {
		if (!$this->_propertySets[$setIndex])
			throwError(new Error("Unknown setIndex, '$setIndex'.", "Wizard"));
		
		if ($setIndex > 0) {
			$tmp = $this->_propertySets[$setIndex-1];
			$this->_propertySets[$setIndex-1] = $this->_propertySets[$setIndex];
			$this->_propertySets[$setIndex] = $tmp;
		}
	}
	
	/**
	 * Move the Set down in the order (further from 0)
	 * 
	 * @param integer $setIndex The set to move.
	 * @return void
	 * @access public
	 * @date 7/16/04
	 */
	function moveSetDown ( $setIndex ) {
		if (!$this->_propertySets[$setIndex])
			throwError(new Error("Unknown setIndex, '$setIndex'.", "Wizard"));
		
		if ($setIndex < count($this->_propertySets)-1) {
			$tmp = $this->_propertySets[$setIndex+1];
			$this->_propertySets[$setIndex+1] = $this->_propertySets[$setIndex];
			$this->_propertySets[$setIndex] = $tmp;
		}
	}
	
	/**
	 * Gets a Property
	 * @parm string $name The name of the requested property.
	 * @return object WizardProperty
	 */
	function & getProperty ( $propertyName ) {
		ArgumentValidator::validate($propertyName, new StringValidatorRule, true);
		if (!$this->_properties[$propertyName])
			throwError(new Error("Property, ".$propertyName.", does not exist in Wizard.", "Wizard", 1));

		return $this->_properties[$propertyName];
	}
	
	/**
	 * Gets an array all Properties indexed by property name.
	 * @return array 
	 */
	function & getProperties () {
		$properties = array();
		
		foreach ($this->_propertySets as $index => $propertySet) {
			foreach ($propertySet as $propertyName => $value) {
				$properties[$propertyName.".".$index] = new WizardProperty (
															$propertyName,
															$this->_registeredProperties[$propertyName]["rule"],
															$this->_registeredProperties[$propertyName]["isValueRequired"]
															);
				$properties[$propertyName.".".$index]->setValue($value);
			}
		}
		
		return $properties;
	}
	
	/**
	 * Go through all properties and update their values from the submitted
	 * form. Return false if any of the submitted values are invalid.
	 *
	 * @access public
	 * @return boolean True on success. False on invalid Property values.
	 */
	function updateProperties () {
		$valid = TRUE;
		
		// On update, there are several possible situations:
		//		someone hit the "delete set" button
		//		someone hit the "edit set" button
		//		someone submitted form information
		//			the form info was for a new set
		//			the form info was for an existing set

		// First, lets save any info requested.
		if ($_REQUEST['__save_new'] || $_REQUEST['__current_property_set']) {
			foreach (array_keys($this->_properties) as $name) {
				if (!$this->_properties[$name]->update())
					$valid = FALSE;
			}
			
			if ($_REQUEST['__current_property_set']) {
				$this->saveCurrentPropertiesToSet($_REQUEST['__current_property_set']);
			} else {
				$this->saveCurrentPropertiesAsNewSet();
			}
		}
		
		// If we requested a set to edit, load that one as the current set.
		if ($_REQUEST['__edit_set'] !== NULL) {
			$this->loadCurrentPropertiesFromSet($_REQUEST['__edit_set']);
		}
		
		// if we requested a set to delete, delete that set.
		if ($_REQUEST['__move_set_up'] !== NULL) {
			$this->moveSetUp($_REQUEST['__move_set_up']);
		}
		
		// if we requested a set to delete, delete that set.
		if ($_REQUEST['__move_set_down'] !== NULL) {
			$this->moveSetDown($_REQUEST['__move_set_down']);
		}
		
		return $valid;
	}
	
	/**
	 * Returns a layout of content for this WizardStep
	 * @param object Harmoni The harmoni object which contains the current context.
	 * @return object Layout
	 */
	function & getLayout (& $harmoni) {
		$stepLayout =& new SingleContentLayout (TEXT_BLOCK_WIDGET, 2);
		
		$text = $this->_parseText();
		
		$stepLayout->addComponent(new Content($text));
		
		return $stepLayout;
	}
	
	/**
	 * Sets the text of this wizard step. The text is a string and can contain
	 * elements that will be parsed with values from the current state of the
	 * step properties.
	 *
	 * Parsed elements can have two forms:
	 * 		[[PropertyName]]
	 * or
	 * 		[[PropertyName Operator ComparisonValue|StringIfTrue|StringIfFalse]]
	 * 
	 * The property-name element should not be quoted unless quotes are
	 * nessisary for use in the comparison string. In that case single quotes, ',
	 * should be used. Examples:
	 *
	 * 		<input type='text' name='title' value='[[title]]'>
	 *
	 * 		<input type='text' name='age' value='[[age]]'> [[age < 18|*You are not old enough!*|You are old enough.]]
	 * 		
	 * 		<input type='radio' name='width' value='5' [[width == 5| checked='checked'|]]> Narrow Width
	 * 		<input type='radio' name='width' value='10' [[width == 10| checked='checked'|]]> Wide Width
	 * 		
	 * 		<input type='radio' name='size' value='S' [['size' == 'S'| checked='checked'|]]> Small
	 * 		<input type='radio' name='size' value='L' [['size' == 'L'| checked='checked'|]]> Large
	 * 
	 * @param string $text The HTML text for this step.
	 * @access public
	 * @return void
	 */
	function setText ( $text ) {
		ArgumentValidator::validate($text, new StringValidatorRule, true);
		
		$this->_text = $text;
	}
	
	/**
	 * Parses the step text to fit the current values of the step properties.
	 * Parsed elements can have two forms:
	 * 		[[PropertyName]]
	 * or
	 * 		[[PropertyName Operator ComparisonValue|StringIfTrue|StringIfFalse]]
	 * 
	 * The property-name element should not be quoted unless quotes are
	 * nessisary for use in the comparison string. In that case single quotes, ',
	 * should be used. Examples:
	 *
	 * 		<input type='text' name='title' value='[[title]]'>
	 *
	 * 		<input type='text' name='age' value='[[age]]'> [[age < 18|*You are not old enough!*|You are old enough.]]
	 * 		
	 * 		<input type='radio' name='width' value='5' [[width == 5| checked='checked'|]]> Narrow Width
	 * 		<input type='radio' name='width' value='10' [[width == 10| checked='checked'|]]> Wide Width
	 * 		
	 * 		<input type='radio' name='size' value='S' [['size' == 'S'| checked='checked'|]]> Small
	 * 		<input type='radio' name='size' value='L' [['size' == 'L'| checked='checked'|]]> Large
	 * 
	 * @access private
	 * @return string
	 */
	function _parseText () {
		// Make a copy of our form text for output
		$outputText = $this->_text;
		
		
		// First, pull out our [Buttons] elements and replace them
		// with our buttons.
		ob_start();
		print "\nSave as <input type='submit' name='__save_new' value='New' />";
		print "\n<input type='hidden' name='__update' value='Force Update' />";
		if ($_REQUEST['__edit_set'])
			print "\n<br />Save as number: <input type='submit' name='__current_property_set' value='".$_REQUEST['__edit_set']."' />";
		
		$outputText = str_replace("[Buttons]", ob_get_contents(), $outputText);
		ob_end_clean();
		
		
		// Replace the [list] ... [/list] with our list text.
		preg_match_all("/\[List\](.*)\[\/List\]/", $outputText, $listMatches);
//		printpre($listMatches);
		if (count($listMatches[0])) {
			foreach ($listMatches[0] as $key => $val) {
				$origListMatch = $listMatches[0][$key];
				$listMatch = $listMatches[1][$key];
				
				$listText = "";
				foreach (array_keys($this->_propertySets) as $setIndex) {
					$setText = $listMatch;
					
					// Edit/delete Buttons
					ob_start();
					print "Edit Number: <input type='submit' name='__edit_set' value='".$setIndex."' />";
					print "\n<br />Delete Number: <input type='submit' name='__delete_set' value='".$setIndex."' />";
					$setText = str_replace("[ListButtons]", ob_get_contents(), $setText);
					ob_end_clean();
					
					// Move up/down Buttons
					ob_start();
					print "Move Up Number: <input type='submit' name='__move_set_up' value='".$setIndex."' />";
//					print "\n<br />Move Down Number: <input type='submit' name='__move_set_down' value='".$setIndex."' />";
					$setText = str_replace("[ListMoveButtons]", ob_get_contents(), $setText);
					ob_end_clean();
				
					preg_match_all("/\[{2}[^\[]*\]{2}/", $setText, $matches);
					if (count($matches[0])) {
						foreach ($matches[0] as $match) {
							// if this element is of the [[propertyname]] form,
							// replace the element with the value of the property.
							if (preg_match("/\[{2}([^|]*)\]{2}/", $match, $parts)) {
								$setText = str_replace($match, htmlspecialchars($this->_propertySets[$setIndex][$parts[1]], ENT_QUOTES), $setText);
							}
						}
					}
					$listText .= $setText;
				}
				
				$outputText = str_replace($origListMatch, $listText, $outputText);
			}
		}
		
		// Get a list of all [[xxxxx]] elements
		preg_match_all("/\[{2}[^\[]*\]{2}/", $outputText, $matches);
		if (count($matches[0])) {
			foreach ($matches[0] as $match) {
				
				// if this element is of the [[propertyname]] form,
				// replace the element with the value of the property.
				if (preg_match("/\[{2}([^|]*)\]{2}/", $match, $parts)) {
					$outputText = str_replace($match, htmlspecialchars($this->_properties[$parts[1]]->getValue(), ENT_QUOTES), $outputText);
				
				// if this element is of the 
				// [['PropertyName' == 'ComparisonVal'|StringIfEquivalent|StringIfNotEquivalent]]
				// form, then compare the value of the property to the ComparisonVal
				// and replace the whole element with the appropriate.
				//
				// RegEx Details - Look for
				// '[[' followed by something followed by
				// 	== OR < OR > OR <= OR >=
				// followed by "|", a string, "|", another string, then ']]'
				} else if (preg_match("/\[{2}(.*)(==|<|>|<=|>=)([^<>=]*)\|(.*)\|(.*)\]{2}/", $match, $parts)) {
					
					$name = trim($parts[1]);
					// If the property name is quoted, get the value and quote it.
					// RegEx Details: look for begining and ending quotes.
					if (preg_match("/^'(.*)'$/", $name, $nameParts)) {
						if (!$this->_properties[$nameParts[1]])
							throwError(new Error("Property, ".$$nameParts[1].", does not exist in Wizard.", "Wizard", TRUE));
							
						$value = "'".$this->_properties[$nameParts[1]]->getValue()."'";
					} else {
						if (!$this->_properties[$name])
							throwError(new Error("Property, ".$name.", does not exist in Wizard.", "Wizard", TRUE));
							
						$value = $this->_properties[$name]->getValue();
					}
					
					$operator = trim($parts[2]);
					$compVal = trim($parts[3]);
					
					// Build our comparison operation string
					$comparison = "if (".$value." ".$operator." ".$compVal.") return TRUE; else return FALSE;";
					
					// Evaluate our comparison and replace with the appropriate
					// string.
					if (eval($comparison))
						$outputText = str_replace($match, $parts[4], $outputText);
					else
						$outputText = str_replace($match, $parts[5], $outputText);
				
				// if this element is of the [[propertyname|Error]] form,
				// replace the element with the error string of the property if 
				//the property's value doesn't validate.
				} else if (preg_match("/\[{2}([^|]*)\|Error\]{2}/", $match, $parts)) {
					if (!$this->_properties[$parts[1]]->validate())
						$outputText = str_replace($match, $this->_properties[$parts[1]]->getErrorString(), $outputText);
					else
						$outputText = str_replace($match, "", $outputText);
				
				} else {
					throwError(new Error("Unknown String form, ".$match.".", "Wizard", 1));
				}
			}
			return $outputText;
		
		// If we don't have any [[xxxxx]] elements, just return the text.
		} else {
			return $outputText;
		}
	}
}

?>