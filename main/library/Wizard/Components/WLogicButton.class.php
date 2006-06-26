<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WLogicButton.class.php,v 1.3 2006/06/26 12:51:46 adamfranco Exp $
 */ 

/**
 * Buttons of this class tree are used in logic wizards to control wizard flow.
 * 
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WLogicButton.class.php,v 1.3 2006/06/26 12:51:46 adamfranco Exp $
 */
class WLogicButton extends WEventButton {
		
	var $_logic = null;
	var $_label = "NO LABEL";
	var $_pressed = false;
	var $_onclick = null;
	
	
	/**
	 * virtual constructor
	 * 
	 * @param ref object WLogicController $controller
	 * @param string $label
	 * @return ref object
	 * @access public
	 * @since 5/31/06
	 */
	function &withLogicAndLabel (&$controller, $label) {
		$button =& new WLogicButton();
		
		$button->setLogicAndLabel($controller, $label);
		
		return $button;
	}	

	/**
	 * virtual constructor
	 * 
	 * @param string $label
	 * @return ref object
	 * @access public
	 * @since 5/31/06
	 */
	function &withLabel (&$controller, $label) {
		$button =& new WLogicButton();
		
		$button->setLabel($label);
		
		return $button;
	}	
	
	/**
	 * Sets the logic controller and label for the button
	 * 
	 * @param ref object WLogicController $controller
	 * @param string $label
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function setLogicAndLabel (&$controller, $label) {
		$this->_logic =& $controller;
		$this->_label = $label;
	}

	/**
	 * Sets the label for the button.
	 * @param string $label
	 * @param optional string $textDomain the gettext() text domain to use for the label.
	 * @access public
	 * @return void
	 */
	function setLabel ($label) {
		$this->_label = $label;
	}
	
	/**
	 * Sets the on-click javascript to be called.
	 * @param string $javascript
	 * @access public
	 * @return void
	 */
	function addOnClick ($javascript) {
		$this->_onclick .= " ".$javascript;
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
		if ($val) {
			// @todo not sure what to do here
			$this->_pressed = true;
		}
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$val = $this->_pressed;
		$this->_pressed = false;
		return $val;
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
		$label = htmlspecialchars($this->_label, ENT_QUOTES);
		$onclick = '';
		if ($this->_onclick) $onclick = addslashes($this->_onclick) . ";";
		$m = "<input type='hidden' name='$name' id='$name' value='0' />\n";
		$m .= "<input type='button' value='$label' onclick='$onclick if (validateWizard(this.form)) { getWizardElement(\"$name\").value=\"1\"; this.form.submit(); }'".($this->isEnabled()?"":" disabled='disabled'")." />";
		return $m;
	}

}

?>