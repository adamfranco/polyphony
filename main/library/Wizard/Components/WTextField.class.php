<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextField.class.php,v 1.12 2005/12/08 15:47:58 adamfranco Exp $
 */ 

require_once(dirname(__FILE__).'/WTextInput.abstract.php');

/**
 * This adds an input type='text' field to a {@link Wizard}.
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextField.class.php,v 1.12 2005/12/08 15:47:58 adamfranco Exp $
 */
class WTextField 
	extends WTextInput 
{

	var $_size = 30;
	var $_maxlength = 255;
	
	/**
	 * Sets the size of this text field.
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
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {		
		$name = RequestContext::name($fieldName);
		$m = "<input type='text' \n\t\t\t\tname='$name' \n\t\t\t\tid='$fieldName' \n\t\t\t\tsize='".$this->_size."' maxlength='".$this->_maxlength."'".($this->_readonly?" readonly='readonly'":"");
		if ($this->_value != null && $this->_value != $this->_startingDisplay) {
			$m .= " value='".htmlspecialchars($this->_value, ENT_QUOTES)."'";
		} else if ($this->_startingDisplay) {
			$v = htmlspecialchars($this->_startingDisplay, ENT_QUOTES);
			$m .= "\n\t\t\t\tvalue='$v' style='color: #888' ";
			$m .= "\n\t\t\t\tonfocus='if (this.value == \"$v\") { this.value=\"\";  this.style.color=\"#000\";}'";
			$m .= "\n\t\t\t\tonblur='if (this.value == \"\") { this.value=\"$v\"; this.style.color=\"#888\";}'";
		}
		if ($this->_style) {
			$m .= "\n\t\t\t\tstyle=\"".str_replace("\"", "\\\"", $this->_style)."\"";
		}
		if ($this->_onchange) {
			$m .= "\n\t\t\t\tonchange=\"".str_replace("\"", "\\\"", $this->_onchange)."\"";
		}
		$m .= " />";
		
		$errText = $this->getErrorText();
		$errRule =& $this->getErrorRule();
		$errStyle = $this->getErrorStyle();
		
		if ($errText && $errRule) {
			$m .= "\n\t\t<span id='".$fieldName."_error' style=\"padding-left: 10px; $errStyle\">&laquo; $errText</span>";	
			$m .= Wizard::getValidationJavascript($fieldName, $errRule, $fieldName."_error", $this->_showError);
			$this->_showError = false;
		}
		
		return $m;
	}
}

?>