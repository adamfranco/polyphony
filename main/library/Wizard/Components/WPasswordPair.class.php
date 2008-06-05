<?php
/**
 * @since 6/4/08
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This component displays a pair of password fields that must have identical values.
 * 
 * @since 6/4/08
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class WPasswordPair
	extends WTextInput
{
		
	var $_size = 30;
	var $_maxlength = 255;
	
	var $_value1 = '';
	var $_value2 = '';
	var $_showEqualityError = false;
	
	/**
	 * Sets the size of this text field.
	 * @param int $size
	 * @access public
	 * @return void
	 */
	function setSize ($size) {
		$this->_size = $size;
	}
	
	/**
	 * Sets the maxlength of the value of this field.
	 * @param integer $maxlength
	 * @access public
	 * @return void
	 */
	function setMaxLength ($maxlength) {
		$this->_maxlength = $maxlength;
	}
	
	/**
	 * Sets the value of this text field.
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
		ArgumentValidator::validate($value, OrValidatorRule::getRule(
			StringValidatorRule::getRule(),
			NumericValidatorRule::getRule()));
		$this->_value1 = $value;
		$this->_value2 = $value;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	function update ($fieldName) {
		$val = RequestContext::value($fieldName.'_1');
		if ($val !== null)
			$this->_value1 = $val;
		
		$val = RequestContext::value($fieldName.'_2');
		if ($val !== null)
			$this->_value2 = $val;
		
		return $this->validate();
	}
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE.
	 * @access public
	 * @return boolean
	 */
	function validate () {
		if ($this->_value1 != $this->_value2) {
			$this->_showEqualityError = true;
			return false;
		}
		
		return parent::validate();
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return $this->_value1;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {		
		ob_start();
		for ($i=1; $i <= 2; $i++) {
			$name = RequestContext::name($fieldName."_".$i);
			$valProp = '_value'.$i;
			$value = $this->$valProp;
			if ($i > 1)
				print "\n\t\t<br/>";
			print "<input type='password' \n\t\t\t\tname='$name' \n\t\t\t\tid='$fieldName' \n\t\t\t\tsize='".$this->_size."' maxlength='".$this->_maxlength."'".(!$this->isEnabled()?" readonly='readonly'":"");
			if ($value != null) {
				print " value='".htmlspecialchars($value, ENT_QUOTES)."'";
			}
			if ($this->_style) {
				print "\n\t\t\t\tstyle=\"".str_replace("\"", "\\\"", $this->_style)."\"";
			}
			if ($this->_onchange) {
				print "\n\t\t\t\tonchange=\"".str_replace("\"", "\\\"", $this->_onchange)."\"";
			}
			print " />";
		}
		
		$errText = $this->getErrorText();
		$errRule =$this->getErrorRule();
		$errStyle = $this->getErrorStyle();
		
		if ($errRule && $errText) {
			print "\n\t\t<span id='".$fieldName."_error' style=\"padding-left: 10px; $errStyle\">&laquo; $errText</span>";		
			print Wizard::getValidationJavascript($fieldName, $errRule, $fieldName."_error", $this->_showError);
			$this->_showError = false;
		}
		
		if ($this->_showEqualityError == true) {
			$errText = dgettext("polyphony", "Passwords must match.");
			print "\n\t\t<span id='".$fieldName."_error' style=\"padding-left: 10px; $errStyle\">&laquo; $errText</span>";		
			$this->_showEqualityError = false;
		}
		
		return ob_get_clean();
	}
	
}

?>