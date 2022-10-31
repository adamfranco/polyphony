<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WMultiSelectList.class.php,v 1.6 2007/09/19 14:04:51 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * This class allows for the creation of select lists where you can select multiple elements.
 * 
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WMultiSelectList.class.php,v 1.6 2007/09/19 14:04:51 adamfranco Exp $
 */
class WMultiSelectList 
	extends WizardComponent 
{

	var $_value = array();
	var $_style = null;
	var $_size = 2;
	
	var $_items = array();
	
	/**
	 * Constructor
	 * @access public
	 * @return WMultiSelectList
	 */
	function __construct () {
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
	 * Sets the number of viewable elements in this list.
	 * @param integer $size
	 * @access public
	 * @return void
	 */
	function setSize ($size) {
		$this->_size = $size;
	}
	
	/**
	 * Sets the passed value to be selected in the list.
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
		$this->_value[] = $value;
		$this->_value = array_unique($this->_value);
	}
	
	/**
	 * Adds a radio option to this list.
	 * @param string $value The short value that represents the displayed text.
	 * @param string $displayText The text to show to the end user.
	 * @access public
	 * @return void
	 */
	function addOption ($value, $displayText) {
		$this->_items[$value] = $displayText;
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
		if (is_array($val)) $this->_value = $val;
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
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		$name = RequestContext::name($fieldName)."[]";
		
		$style = '';
		
		if ($this->_style) $style = " style=\"".addslashes($this->_style)."\""; 

		$m = "<select name='$name' size='".$this->_size."' multiple$style>\n";
		
		foreach (array_keys($this->_items) as $key) {
			$disp = $this->_items[$key];
			$selected = in_array($key, $this->_value)?" selected='selected'":"";
			$val = htmlspecialchars($key, ENT_QUOTES);
						
			$m .= "<option value='$val'$selected>".htmlspecialchars($disp)."</option>\n";
		}
		
		$m .= "</select>\n";
		
		return $m;
	}
}

?>