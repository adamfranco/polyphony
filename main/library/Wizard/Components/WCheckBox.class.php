<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCheckBox.class.php,v 1.10 2006/04/21 20:58:12 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * This class allows for the creation of a input type='checkbox' element.
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCheckBox.class.php,v 1.10 2006/04/21 20:58:12 adamfranco Exp $
 */
class WCheckBox 
	extends WizardComponent 
{

	var $_value;
	
	var $_style = null;
	
	var $_label = '';
	
	var $_readonly = false;
	
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
	 * @return WCheckBox
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
	function setValue ($checked) {
		$this->_value = $checked;
	}

	/**
	 * Sets if this checkbox should be checked or not as a default value.
	 * @param boolean $checked
	 * @access public
	 * @return void
	 */
	function setChecked ($checked) {
		$this->setValue($checked);
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
		$dummyName = $fieldName . "_dummy";
		
		$val = $this->_value?"1":"0";
		$checked = $this->_value?" checked='checked'":"";
		
		$style = " style='cursor: pointer;'";
		if ($this->_style) $style = " style=\"cursor: pointer; ".htmlspecialchars($this->_style)."\"";
		
				
		$m = "\n\t\t\t<input type='hidden' \n\t\t\t\tname='$name' \n\t\t\t\tid='$fieldName' \n\t\t\t\tvalue='$val' />";
		
		$m .= "\n\t\t\t<input type='checkbox' ";
		if ($this->_readonly)
			$m .= "\n\t\t\t\tdisabled=\"disabled\"";
		else
			$m .= "\n\t\t\t\tonclick=\"".$this->getSetJS($fieldName)."\" ";
		
		$m .= "\n\t\t\t\tid='$dummyName'$checked />";
		
		$m .= "\n\t\t\t<label$style ";
		if (!$this->_readonly)
			$m .= "\n\t\t\t\tonclick=\"".$this->getToggleJS($fieldName)."\" ";
		$m .= "\n\t\t\t>".$this->_label."</label>";
		
		return $m;
	}
	
	/**
	 * Answer the javascript commands to execute when the checkbox is clicked.
	 * 
	 * @param string $fieldName
	 * @return string
	 * @access public
	 * @since 10/20/05
	 */
	function getToggleJS ($fieldName) {
		$dummyName = $fieldName . "_dummy";
		
		$js = "document.getElementById('$dummyName').checked = (document.getElementById('$dummyName').checked? false : true); ";
		$js .= $this->getSetJS($fieldName);
		return $js;
	}
	
	/**
	 * Answer the javascript commands to execute when the checkbox is clicked.
	 * 
	 * @param string $fieldName
	 * @return string
	 * @access public
	 * @since 10/20/05
	 */
	function getSetJS ($fieldName) {
		$dummyName = $fieldName . "_dummy";
		
		$js = "document.getElementById('$fieldName').value = (document.getElementById('$dummyName').checked? '1' : '0'); ";
		return $js;
	}
	
	/**
	 * Answer the javascript commands to check the checkbox.
	 * 
	 * @return string
	 * @access public
	 * @since 10/20/05
	 */
	function getCheckJS ($fieldName) {
		$dummyName = $fieldName . "_dummy";
		
		$js = "document.getElementById('$dummyName').checked = true; ";
		$js .= $this->getSetJS($fieldName);
		return $js;
	}
	
	/**
	 * Answer the javascript commands to check the checkbox.
	 * 
	 * @return string
	 * @access public
	 * @since 10/20/05
	 */
	function getUncheckJS ($fieldName) {
		$dummyName = $fieldName . "_dummy";
		
		$js = "document.getElementById('$dummyName').checked = false; ";
		$js .= $this->getSetJS($fieldName);
		return $js;
	}
}

?>