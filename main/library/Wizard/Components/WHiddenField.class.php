<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WHiddenField.class.php,v 1.6 2007/09/04 20:28:07 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * This class allows for the creation of a input type='hidden'
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WHiddenField.class.php,v 1.6 2007/09/04 20:28:07 adamfranco Exp $
 */
class WHiddenField 
	extends WizardComponent 
{

	var $_value;
	
	/**
	 * Virtual Constructor
	 * @param string $value
	 * @access public
	 * @return ref object
	 * @static
	 */
	function withValue ($value) {
		$obj = new WHiddenField();
		$obj->_value = $value;
		return $obj;
	}
	
	/**
	 * Virtual Constructor with fromString naming convention
	 * @param string $aString
	 * @access public
	 * @return ref object
	 * @static
	 */
	function fromString ($aString) {
		$obj = new WHiddenField();
		$obj->_value = $aString;
		return $obj;
	}
	
	/**
	 * Sets the value of this hidden field.
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
		if ($val !== false && $val !== null) $this->_value = $val;
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
		
		$val = htmlspecialchars($this->_value, ENT_QUOTES);
		$m = "<input type='hidden' name='$name' id='$name' value='$val' />";
		
		return $m;
	}
}

?>