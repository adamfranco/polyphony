<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WPasswordField.class.php,v 1.3 2007/09/19 14:04:51 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/WizardComponent.abstract.php");

/**
 * This adds an input type='password' field to a {@link Wizard}.
 * 
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WPasswordField.class.php,v 1.3 2007/09/19 14:04:51 adamfranco Exp $
 */
class WPasswordField 
	extends WizardComponent 
{

	var $_size = 30;
	var $_maxlength = 255;
	var $_style = null;
	var $_value = null;
	
	/**
	 * Sets the size of this password field.
	 * @param int $size
	 * @access public
	 * @return void
	 */
	function setSize ($size) {
		$this->_size = $size;
	}
	
	/**
	 * Sets the maxlength of the value of this field.
	 * @param integer $maxlength
	 * @access public
	 * @return void
	 */
	function setMaxLength ($maxlength) {
		$this->_maxlength = $maxlength;
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
		$m = "<input type='password' name='$name' size='".$this->_size."' maxlength='".$this->_maxlength."'";
		if ($this->_style) {
			$m .= " style=\"".addslashes($this->_style)."\"";
		}
		$m .= " />";
		
		return $m;
	}
}

?>