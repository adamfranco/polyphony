<?
/**
 *
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardStep.abstract.php,v 1.9 2005/06/22 18:20:46 gabeschine Exp $
 */

/**
 * Require our needed classes
 * 
 */
require_once(dirname(__FILE__)."/WizardStep.interface.php");

/**
 * The Wizard class provides a system for registering Wizard properties and 
 * associating those properties with the appropriate form elements.
 *
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardStep.abstract.php,v 1.9 2005/06/22 18:20:46 gabeschine Exp $
 * @author Adam Franco
 * @access public
 */

class WizardStepAbstract 
	extends WizardStepInterface {
	
	/**
	 * The displayName of this WizardStep
	 * @var protected string _displayName
	 */
	var $_displayName;
	
	/**
	 * The properties handled by the Wizard.
	 * @var protected array _properties
	 */
	var $_properties = array();
	
	/**
	 * The text with un-parsed wizard tags.
	 * @var protected string _text 
	 */
	var $_text;
	
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
	function &createProperty ( $propertyName, & $validatorRule, $isValueRequired = TRUE ) {
		ArgumentValidator::validate($propertyName, new StringValidatorRule, true);
		ArgumentValidator::validate($validatorRule, new ExtendsValidatorRule("ValidatorRuleInterface"), true);
		ArgumentValidator::validate($isValueRequired, new BooleanValidatorRule, true);
		
		if ($this->_properties[$propertyName])
			throwError(new Error("Property, ".$propertyName.", already exists in Wizard.", "Wizard", 1));

		$this->_properties[$propertyName] =& new WizardProperty( $propertyName, $validatorRule, $isValueRequired );
		return $this->_properties[$propertyName];
	}
	
	/**
	 * Gets a Property
	 * @parm string $name The name of the requested property.
	 * @return object WizardProperty
	 */
	function &getProperty ( $propertyName ) {
		ArgumentValidator::validate($propertyName, new StringValidatorRule, true);
		if (!$this->_properties[$propertyName])
			throwError(new Error("Property, ".$propertyName.", does not exist in Wizard.", "Wizard", 1));

		return $this->_properties[$propertyName];
	}
	
	/**
	 * Gets an array all Properties indexed by property name.
	 * @return array 
	 */
	function &getProperties () {
		return $this->_properties;
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
		foreach (array_keys($this->_properties) as $name) {
			if (!$this->_properties[$name]->update())
				$valid = FALSE;
		}
		
		return $valid;
	}
	
	/**
	 * Go through all properties and check the validity of their stored values. 
	 * Return false if any of the submitted values are invalid.
	 *
	 * @access public
	 * @return boolean True on success. False on invalid Property values.
	 */
	function arePropertiesValid () {
		$valid = TRUE;
		foreach (array_keys($this->_properties) as $name) {
			if (!$this->_properties[$name]->validate())
				$valid = FALSE;
		}
		
		return $valid;
	}
	
	/**
	 * Returns a layout of content for this WizardStep
	 * @param object Harmoni The harmoni object which contains the current context.
	 * @return object Layout
	 */
	function &getLayout (& $harmoni) {
		
		
		$text = $this->_parseText();
		$stepLayout =& new Block ($text, 3);
		return $stepLayout;
	}
	
	/**
	 * Returns the parsed value of text in the step from within [[bla bla]] tags. 
	 * @param string $text
	 * @access protected
	 * @return mixed
	 */
	 function _returnParsedValue($text)
	 {
	 	ereg("(.+)(\[([0-9]*)\])?", $text, $parts);
	 	$propertyName = $parts[1];
	 	$isArray = $parts[2]!=''?true:false; // if they included a [] or a [i]
	 	$arrayIndex = $parts[3]?$parts[3]:null;
	 	
	 	// Make sure that we have the requested property
	 	if (!is_object($this->_properties[$propertyName])) {
	 		throwError(new Error("The property-name, '$propertyName', included in the text for this step, '".$this->getDisplayName()."', does not exist in this step", "Wizard", TRUE));
	 	}
	 	
	 	$propValue = $this->_properties[$propertyName]->getValue();
	 	if ($isArray && is_array($propValue)) {
	 		if ($arrayIndex != null) { // a specific index has been requested
	 		$value = $propValue[$arrayIndex];
	 		} else {
	 			$value = next($propValue);
	 		}
	 	} else $value = $propValue;
	 	
	 	return $value;
	 }
	
	/**
	 * Returns the parsed name of the property represented within the passed text.
	 * @param string $text
	 * @access protected
	 * @return string
	 */
	function _returnParsedName($text)
	{
	 	ereg("(.+)(\[([0-9]*)\])?", $text, $parts);
	 	$propertyName = $parts[1];
		return $propertyName;
	}
	 
	/**
	 * Parses the step text to fit the current values of the step properties.
	 * Parsed elements can have two forms:
	 * 		[[PropertyName]]
	 * or
	 * 		[[PropertyName Operator ComparisonValue|StringIfTrue|StringIfFalse]]
	 *
	 * where PropertyName is either just the name of a property or a specific array index from that property (ie, 
	 * emails[2] instead of just emails).
	 * 
	 * The property-name element should not be quoted unless quotes are
	 * nessisary for use in the comparison string. In that case single quotes, ',
	 * should be used. Examples:
	 *
	 * 		<input type='text' name='title' value='[[title]]' />
	 *
	 * 		<input type='text' name='age' value='[[age]]' /> [[age < 18|*You are not old enough!*|You are old enough.]]
	 * 		
	 * 		<input type='radio' name='width' value='5' [[width == 5| checked='checked'|]] /> Narrow Width
	 * 		<input type='radio' name='width' value='10' [[width == 10| checked='checked'|]] /> Wide Width
	 * 		
	 * 		<input type='radio' name='size' value='S' [['size' == 'S'| checked='checked'|]] /> Small
	 * 		<input type='radio' name='size' value='L' [['size' == 'L'| checked='checked'|]] /> Large
	 * 
	 * @access private
	 * @return string
	 */
	function _parseText () {
		// Make a copy of our form text for output
		$outputText = $this->_text;
		
		// Get a list of all [[xxxxx]] elements
		preg_match_all("/\[{2}[^\[]*\]{2}/", $outputText, $matches);
		if (count($matches[0])) {
			foreach ($matches[0] as $match) {
				
				// if this element is of the [[propertyname]] form,
				// replace the element with the value of the property.
				if (preg_match("/\[{2}([^|]*)\]{2}/", $match, $parts)) {
					$property = $parts[1];
					$value = $this->_returnParsedValue($property);
					$outputText = str_replace($match, htmlspecialchars($value, ENT_QUOTES), $outputText);
				
				// if this element is of the 
				// [['PropertyName' == 'ComparisonVal'|StringIfEquivalent|StringIfNotEquivalent]]
				// form, then compare the value of the property to the ComparisonVal
				// and replace the whole element with the appropriate.
				//
				// RegEx Details - Look for
				// '[[' followed by something followed by
				// 	== OR < OR > OR <= OR >=
				// followed by "|", a string, "|", another string, then ']]'
				} else if (preg_match("/\[{2}(.*)\w*(==|<|>|<=|>=)\w*([^<>=]*)\|(.*)\|(.*)\]{2}/", $match, $parts)) {
					
					$property = trim($parts[1]);
					// If the property name is quoted, get the value and quote it.
					// RegEx Details: look for begining and ending quotes.
					if (preg_match("/^'(.*)'$/", $property, $propertyParts)) 
						$value = "'".$this->_returnParsedValue($propertyParts[1])."'";
					else
						$value = $this->_returnParsedValue($property);
					
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
					debug::output(printpre($parts, TRUE));
					debug::output(printpre($this->_properties[$parts[1]], TRUE));
					$propertyName = $this->_returnParsedName($parts[1]);
					if (!$this->_properties[$propertyName]->validate())
						$outputText = str_replace($match, $this->_properties[$propertyName]->getErrorString(), $outputText);
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
