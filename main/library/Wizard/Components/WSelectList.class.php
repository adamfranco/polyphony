<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSelectList.class.php,v 1.12 2006/05/26 14:14:29 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * This class allows for the creation of select lists.
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSelectList.class.php,v 1.12 2006/05/26 14:14:29 adamfranco Exp $
 */
class WSelectList 
	extends WizardComponent 
{

	var $_value;
	var $_style = null;
	var $_onchange = '';
	
	var $_items = array();
	
	/**
	 * Constructor
	 * @access public
	 * @return WSelectList
	 */
	function WSelectList () {
		// do nothing
	}
	
	/**
	 * sets the CSS style for the labels of the radio buttons.
	 * @param string $style
	 * @access public
	 * @return void
	 */
	function setStyle ($style) {
		$this->_style = $style;
	}
	
	/**
	 * Sets the text of the field to display until the user enters the field.
	 * @param string $text
	 * @access public
	 * @return void
	 */
	function setStartingDisplayText ($text) {
		if (!$this->isOption('_starting_display')) {
			$this->addOption('_starting_display', $text);
		}
		$this->setValue('_starting_display');
	}
	
	
	/**
	 * Sets the value of this radio button group.
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
// 		ArgumentValidator::validate($value, StringValidatorRule::getRule());
		if (is_object($value))
			$this->_value = $value->asString();
		else
			$this->_value = $value;
	}
	
	/**
	 * Adds an option to this list.
	 * @param string $value The short value that represents the displayed text.
	 * @param string $displayText The text to show to the end user.
	 * @access public
	 * @return void
	 */
	function addOption ($value, $displayText) {
		$this->_items[$value] = $displayText;
	}
	
	/**
	 * Answer true if the value passed is a valid option
	 * 
	 * @param string $value
	 * @return boolean
	 * @access public
	 * @since 4/28/06
	 */
	function isOption ($value) {
		$rule =& StringValidatorRule::getRule();
		if ($rule->check($value))
			return array_key_exists($value, $this->_items);
		else
			return array_key_exists('', $this->_items);
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
		if ($val !== false && $val !== null) 
			$this->_value = $val;
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		if ($this->_value == '_starting_display')
			return null;
		else
			return $this->_value;
			
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
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		$name = RequestContext::name($fieldName);
		
		$style = '';
		
		if ($this->_style) $style = " style=\"".addslashes($this->_style)."\""; 

		$m = "<select name='$name' $style ";
		if ($this->_onchange && $this->isEnabled()) {
			$m .= "\n\t\t\t\tonchange=\"".str_replace("\"", "\\\"", $this->_onchange)."\"";
		}
		if (!$this->isEnabled())
			$m .= "\n\t\t\t\tdisabled=\"disabled\"";
		$m .= ">\n";
		
		foreach (array_keys($this->_items) as $key) {
			$disp = $this->_items[$key];
			$selected = $this->_value==$key?" selected='selected'":"";
			$val = htmlspecialchars($key, ENT_QUOTES);
						
			$m .= "<option value='$val'$selected>".htmlspecialchars($disp)."</option>\n";
		}
		
		if ($this->_value && !$this->isOption($this->_value)) {
			$m .= "<option value='".htmlspecialchars($this->_value, ENT_QUOTES)."' selected='selected'>"._("(Current value, '").htmlspecialchars($this->_value)." "._("', is not in allowed list.)")."</option>\n";
		}
		
		$m .= "</select>\n";
		
		return $m;
	}
}

?>