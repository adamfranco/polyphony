<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WEventButton.class.php,v 1.8 2005/12/08 15:24:45 adamfranco Exp $
 */ 

/**
 * This is a base class for any button in a {@link Wizard} that will throw an event when
 * it is activated.
 * 
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WEventButton.class.php,v 1.8 2005/12/08 15:24:45 adamfranco Exp $
 */
class WEventButton 
	extends WizardComponent
{
	var $_event = "nop";
	var $_label = "NO LABEL";
	var $_pressed = false;
	var $_enabled = true;
	var $_onclick = null;
	
	/**
	 * virtual constructor
	 * @param string $event
	 * @param string $label
	 * @access public
	 * @return ref object
	 * @static
	 */
	function &withEventAndLabel ($event, $label) {
		$obj =& new WEventButton();
		$obj->setEventAndLabel($event, $label);
		
		return $obj;
	}
	
	/**
	 * virtual constructor - creates the button with a "nop" event
	 * @param string $label
	 * @access public
	 * @return ref object
	 */
	function &withLabel ($label) {
		$obj =& new WEventButton();
		$obj->_label = $label;
		return $obj;
	}
	
	/**
	 * Sets the event type and label for the button.
	 * @param string $event
	 * @param string $label
	 * @param optional string $textDomain the gettext() text domain to use for the label.
	 * @access public
	 * @return void
	 */
	function setEventAndLabel ($event, $label) {
		$this->_label = $label;
		$this->_event = $event;
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
	 * Sets if this component will be enabled or disabled.
	 * @param boolean $enabled
	 * @access public
	 * @return void
	 */
	function setEnabled ($enabled) {
		$this->_enabled = $enabled;
	}
	
	/**
	 * Answers true if this component will be enabled.
	 * @access public
	 * @return boolean
	 */
	function getEnabled () {
		return $this->_enabled;
	}
	
	/**
	 * Sets the on-click javascript to be called.
	 * @param string $javascript
	 * @access public
	 * @return void
	 */
	function setOnClick ($javascript) {
		$this->_onclick = $javascript;
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
			// trigger the save event on the wizard
			$wizard =& $this->getWizard();
			$wizard->triggerLater($this->_event, $wizard);
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
		$label = htmlentities($this->_label, ENT_QUOTES);
		$onclick = '';
		if ($this->_onclick) $onclick = addslashes($this->_onclick) . ";";
		$m = "<input type='hidden' name='$name' id='$name' value='0' />\n";
		$m .= "<input type='button' value='$label' onclick='$onclick if (validateWizard(this.form)) { getWizardElement(\"$name\").value=\"1\"; this.form.submit(); }'".($this->getEnabled()?"":" disabled='disabled'")." />";
		return $m;
	}
}

?>