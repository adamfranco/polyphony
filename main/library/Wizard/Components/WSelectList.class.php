<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSelectList.class.php,v 1.1 2005/07/22 15:42:34 gabeschine Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.interface.php');

/**
 * This class allows for the creation of select lists.
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSelectList.class.php,v 1.1 2005/07/22 15:42:34 gabeschine Exp $
 */
class WSelectList extends WizardComponent {
	var $_parent;
	var $_value;
	var $_style = null;
	
	var $_items = array();
	
	/**
	 * Constructor
	 * @access public
	 * @return WRadioList
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
	 * Sets the value of this radio button group.
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
		$this->_value = $value;
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
	 * Sets this component's parent (some kind of {@link WizardComponentWithChildren} so that it can
	 * have access to its information, if needed.
	 * @param ref object $parent
	 * @access public
	 * @return void
	 */
	function setParent (&$parent) {
		$this->_parent =& $parent;
	}
	
	/**
	 * Returns the top-level {@link Wizard} in which this component resides.
	 * @access public
	 * @return ref object
	 */
	function &getWizard () {
		return $this->_parent->getParent();
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
		if ($val) $this->_value = $val;
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
		$name = RequestContext::name($fieldName);
		
		$style = '';
		
		if ($this->_style) $style = " style=\"".addslashes($this->_style)."\""; 

		$m = "<select name='$name'$style>\n";
		
		foreach (array_keys($this->_items) as $key) {
			$disp = $this->_items[$key];
			$selected = $this->_value==$key?" selected":"";
			$val = htmlentities($key, ENT_QUOTES);
						
			$m .= "<option value='$val'$selected>".htmlentities($disp)."</option>\n";
		}
		
		$m .= "</select>\n";
		
		return $m;
	}
}

?>