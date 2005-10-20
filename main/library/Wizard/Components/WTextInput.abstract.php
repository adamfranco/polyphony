<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextInput.abstract.php,v 1.2 2005/10/20 19:43:51 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/ErrorCheckingWizardComponent.abstract.php");

/**
 * This adds an input type='text' field to a {@link Wizard}.
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextInput.abstract.php,v 1.2 2005/10/20 19:43:51 adamfranco Exp $
 */
class WTextInput
	extends ErrorCheckingWizardComponent 
{

	var $_style = null;
	var $_value = null;
	var $_startingDisplay = null;
	var $_readonly = false;
	var $_onchange = null;
	var $_showError = false;
	
	/**
	 * Sets the text of the field to display until the user enters the field.
	 * @param string $text
	 * @access public
	 * @return void
	 */
	function setStartingDisplayText ($text) {
		$this->_startingDisplay = $text;
	}
	
	/**
	 * Sets the CSS style of this field.
	 * @param string $style
	 * @access public
	 * @return void
	 */
	function setStyle ($style) {
		$this->_style = $style;
	}

	/**
	 * Sets the value of this text field.
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
		$this->_value = $value;
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
		$val = RequestContext::value($fieldName);
		if ($val 
			&& (!$this->_startingDisplay || $val != $this->_startingDisplay))
		{
			$this->_value = $val;
		}
	}
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE.
	 * @access public
	 * @return boolean
	 */
	function validate () {
		$rule =& $this->getErrorRule();
		if (!$rule) return true;
		
		$err = $rule->checkValue($this);
		if (!$err) $this->_showError = true;
		return $err;
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return $this->_value;
	}
	
	/**
	 * Sets the readonly flag for this element.
	 * @param boolean $bool
	 *
	 * @return void
	 **/
	function setReadOnly($bool)
	{
		$this->_readonly = $bool;
	}
	
	/**
	 * Sets the javascript onchange attribute.
	 * @param string $commands
	 * @access public
	 * @return void
	 */
	function setOnChange($commands) {
		$this->_onchange = $commands;
	}
}

?>