<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCheckBox.class.php,v 1.1 2005/07/22 15:42:32 gabeschine Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.interface.php');

/**
 * This class allows for the creation of a input type='checkbox' element.
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCheckBox.class.php,v 1.1 2005/07/22 15:42:32 gabeschine Exp $
 */
class WCheckBox extends WizardComponent {
	var $_parent;
	var $_value;
	
	var $_style = null;
	
	var $_label = '';
	
	/**
	 * Virtual Constructor
	 * @param string $label
	 * @access public
	 * @return ref object
	 * @static
	 */
	function &withLabel ($label) {
		$obj =& new WCheckBox();
		$obj->_label = $label;
		return $obj;
	}
	
	/**
	 * Constructor
	 * @access public
	 * @return WRadioList
	 */
	function WCheckBox () {
		$this->_value = false;
	}
	
	/**
	 * Sets the CSS style of the label for this element.
	 * @param string $style
	 * @access public
	 * @return void
	 */
	function setStyle ($style) {
		$this->_style = $style;
	}
	
	/**
	 * Sets if this checkbox should be checked or not as a default value.
	 * @param boolean $checked
	 * @access public
	 * @return void
	 */
	function setChecked ($checked) {
		$this->_value = $checked;
	}
	
	/**
	 * Sets the label for this checkbox element.
	 * @param string $label;
	 * @access public
	 * @return void
	 */
	function setLabel ($label) {
		$this->_label = $label;
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
		if ($val == '1') $this->_value = true;
		else if ($val == '0') $this->_value = false;
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * 
	 * In this case, a "1" or a "0" is returned, depending on the checked state of the checkbox.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return $this->_value?"1":"0";
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
		$dummyName = $name . "_dummy";
		
		$val = $this->_value?"1":"0";
		$checked = $this->_value?" checked":"";
		
		$style = " style='cursor: pointer;'";
		if ($this->_style) $style = " style=\"cursor: pointer; ".addslashes($this->_style)."\"";
		
		$javascript1 = "document.getElementById('$dummyName').checked = (document.getElementById('$dummyName').checked? false : true); ";
		$javascript2 = "document.getElementById('$name').value = (document.getElementById('$dummyName').checked? '1' : '0'); ";
		
		$m = "<input type='hidden' name='$name' id='$name' value='$val' /><input type='checkbox' onmouseup=\"$javascript2\" id='$dummyName'$checked /> <label$style onmousedown=\"$javascript1$javascript2\" for='$dummyName'>".$this->_label."</label>";
		
		return $m;
	}
}

?>