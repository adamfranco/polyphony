<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSelectList.class.php,v 1.19 2007/09/19 14:04:51 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * This class allows for the creation of select lists.
 * 
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSelectList.class.php,v 1.19 2007/09/19 14:04:51 adamfranco Exp $
 */
class WSelectList 
	extends WizardComponent 
{

	var $_value;
	var $_style = null;
	var $_onchange = '';
	
	var $_items = array();
	var $_styles = array();
	var $_disabled = array();
	
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
	 * Return True if the select list is on the starting display, rather than 
	 * a real value
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/3/06
	 */
	function isStartingDisplay () {
		if ($this->_value == '_starting_display')
			return true;
		else
			return false;
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
	 * @param string $styles Any styles to pass into the menu option.
	 * @access public
	 * @return void
	 */
	function addOption ($value, $displayText, $styles = null) {
		$this->_items[$value] = $displayText;
		$this->_styles[$value] = $styles;
	}
	
	/**
	 * Add a disabled option to this list.
	 * 
	 * @param string $value The short value that represents the displayed text.
	 * @param string $displayText The text to show to the end user.
	 * @param string $styles Any styles to pass into the menu option.
	 * @return void
	 * @access public
	 * @since 4/3/07
	 */
	function addDisabledOption ($value, $displayText, $styles = null) {
		$this->addOption($value, $displayText, $styles);
		$this->_disabled[$value] = true;
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
		$rule = StringValidatorRule::getRule();
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
	 * Add commands to the javascript onchange attribute.
	 * @param string $commands
	 * @access public
	 * @return void
	 */
	function addOnChange($commands) {
		$this->_onchange .= " ".$commands;
	}
	
	/**
	 * Add a confirmation question that will be present in a javascript 'confirm' 
	 * dialog onchange press.
	 * 
	 * @param string $confirmText
	 * @return void
	 * @access public
	 * @since 6/7/06
	 */
	function addConfirm ($confirmText) {
		if (!isset($this->_confirms))
			$this->_confirms = array();
		$this->_confirms[] = $confirmText;
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
		if (($this->_onchange || (isset($this->_confirms) && count($this->_confirms))) 
			&& $this->isEnabled()) 
		{
			$m .= "\n\t\t\t\tonchange=\"";
			
			if (isset($this->_confirms) && count($this->_confirms)) {
				$m .= "var confirmed = (confirm('";
				$m .= implode("') && confirm('", $this->_confirms);
				$m .= "'));";
			} else {
				$m .= "var confirmed = true; ";
			}
					
			$m .= " if (confirmed) { ";		
			$m .= str_replace("\"", "\\\"", $this->_onchange);
			$m .= " } else { ";
			$m .= 	" this.value = '".htmlspecialchars($this->_value, ENT_QUOTES)."';";
			$m .= " }\"";
		}
		if (!$this->isEnabled())
			$m .= "\n\t\t\t\tdisabled=\"disabled\"";
		$m .= ">\n";
		
		
		foreach (array_keys($this->_items) as $key) {
			$disp = $this->_items[$key];
			$selected = $this->_value==$key?" selected='selected'":"";
			$val = htmlspecialchars($key, ENT_QUOTES);			
			if (!is_null($this->_styles[$key]))
				$style = " style='".$this->_styles[$key]."'";
			else
				$style = '';				
			$m .= "<option value='".$val."'".$selected;
			if (isset($this->_disabled[$val]) && $this->_disabled[$val]) {
				$m .= " disabled='disabled'";
			}
			$m .= $style.">".htmlspecialchars($disp)."</option>\n";
		}
		
		if ($this->_value && !$this->isOption($this->_value)) {
			$m .= "<option value='".htmlspecialchars($this->_value, ENT_QUOTES)."' selected='selected'>"._("(Current value, '").htmlspecialchars($this->_value)." "._("', is not in allowed list.)")."</option>\n";
		}
		
		$m .= "</select>\n";
		
		return $m;
	}
}

?>