<?

/**
 * This is an abstract class that provides much of the functionality of the 
 * various WizardProperties. 
 * 
 * @package concerto.wizard
 * @author Adam Franco
 * @copyright 2004 Middlebury College
 * @access public
 * @version $Id: WizardProperty.class.php,v 1.2 2004/06/01 20:08:11 adamfranco Exp $
 */
 
class WizardProperty {
	
	/**
	 * @attribute string _name The name the property
	 */
	var $_name;
	
	/**
	 * @attribute mixed _value The current value of the property
	 */
	var $_value;
	
	/**
	 * @attribute string _isValueRequired If false, the existance of a value in
	 * the $_REQUEST array will not be required. This is needed for checkbox
	 * values which are simply not submitted if unchecked.
	 */
	var $_isValueRequired;
	
	/**
	 * @attribute mixed _defaultValue The default value of the property
	 */
	var $_defaultValue;
	
	/**
	 * Constructor: throw error as this is an abstract class.
	 */
	function WizardProperty ( $name, & $validatorRule, $isValueRequired = TRUE ) {
		ArgumentValidator::validate($name, new StringValidatorRule, true);
		ArgumentValidator::validate($validatorRule, new ExtendsValidatorRule("ValidatorRuleInterface"), true);
		ArgumentValidator::validate($isValueRequired, new BooleanValidatorRule, true);

		$this->_name = $name;
		$this->_validatorRule  =& $validatorRule;
		$this->_isValueRequired = $isValueRequired;
		$this->_errorString = " <span style='color: f00'>* "._("The value specified is not valid.")."</span>";
	}
	
	/**
	 * Returns the value of this Property
	 * @access public
	 * @return mixed The Value
	 */
	function getValue () {
		if (isset($this->_value))
			return $this->_value;
		else
			return $this->_defaultValue;
	}
	
	/**
	 * Set the default value. This will be returned by getValue if no
	 * value has been set yet.
	 * @param mixed $defaultValue The new default value for this Property.
	 * @access public
	 * @return void
	 */
	function setDefaultValue ( $defaultValue ) {
		$this->_defaultValue = $defaultValue;
	}
	
	/**
	 * Set the error string. This will be returned by getErrorString 
	 * @param string $errorString The string to return if updating this property
	 *			fails.
	 * @access public
	 * @return void
	 */
	function setErrorString ( $errorString ) {
		ArgumentValidator::validate($errorString, new StringValidatorRule, true);
		$this->_errorString = $errorString;
	}
	
	/**
	 * Returns the error string for this property. The error string is to
	 * be used when validation fails
	 * @access public
	 * @return string
	 */
	function getErrorString () {
		return $this->_errorString;
	}
	
	/**
	 * Update the value of this property from the current environment.
	 * @access public
	 * @return boolean True on successful update with valid .
	 */
	function update () {
		// Set the value from the request array.
		if (isset($_REQUEST[$this->_name]) || !$this->_isValueRequired)
			$this->_value = $_REQUEST[$this->_name];
		else
			throwError(new Error("Requested property, ".$this->_name.", does not exist in the _REQUEST array.", "Wizard", 1));

		return $this->validate();			
	}
	
	/**
	 * Validates the current value of the property
	 * @access public
	 * @return boolean
	 */
	function validate () {
		$value =& $this->getValue();
		return $this->_validatorRule->check($value);	
	}
}