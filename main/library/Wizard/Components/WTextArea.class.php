<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextArea.class.php,v 1.3 2005/08/10 17:52:05 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * This class allows for the creation of a textarea element
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextArea.class.php,v 1.3 2005/08/10 17:52:05 adamfranco Exp $
 */
class WTextArea 
	extends WizardComponent 
{

	var $_value;
	
	var $_style = null;
	var $_rows;
	var $_cols;
	
	/**
	 * Virtual Constructor
	 * @param integer $rows
	 * @param integer $cols
	 * @access public
	 * @return ref object
	 * @static
	 */
	function &withRowsAndColumns ($rows, $cols) {
		$obj =& new WTextArea();
		$obj->_rows = $rows;
		$obj->_cols = $cols;
		return $obj;
	}
	
	/**
	 * Constructor
	 * @access public
	 * @return WTextArea
	 */
	function WTextArea () {
		$this->_value = '';
		$this->_rows = 3;
		$this->_cols = 60;
	}
	
	/**
	 * Sets the inner text value of the textarea.
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
		$this->_value = $value;
	}
	
	/**
	 * Sets the number of visible rows in this textarea.
	 * @param integer $rows
	 * @access public
	 * @return void
	 */
	function setRows ($rows) {
		$this->_rows = $rows;
	}
	
	/**
	 * Sets the number of visible columns in this textarea.
	 * @param integer $cols
	 * @access public
	 * @return void
	 */
	function setColumns ($cols) {
		$this->_cols = $cols;
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
		
		$style = '';
		if ($this->_style) $style = " style=\"".addslashes($this->_style)."\"";
		
		$m = "<textarea rows='".$this->_rows."' cols='".$this->_cols."'$style name='$name'>".htmlentities($this->_value)."</textarea>";
		
		return $m;
	}
}

?>