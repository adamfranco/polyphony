<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextArea.class.php,v 1.13 2007/10/10 22:58:56 adamfranco Exp $
 */ 

require_once(dirname(__FILE__).'/WTextInput.abstract.php');

/**
 * This class allows for the creation of a textarea element
 * 
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextArea.class.php,v 1.13 2007/10/10 22:58:56 adamfranco Exp $
 */
class WTextArea 
	extends WTextInput 
{

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
	static static function withRowsAndColumns ($rows, $cols, $class = 'WTextArea') {
		$obj = new $class();
		$obj->setRows($rows);
		$obj->setColumns($cols);
		return $obj;
	}
	
	/**
	 * Constructor
	 * @access public
	 * @return WTextArea
	 */
	function __construct () {
		$this->_value = '';
		$this->_rows = 3;
		$this->_cols = 60;
	}
	
	/**
	 * Sets the number of visible rows in this textarea.
	 * @param integer $rows
	 * @access public
	 * @return void
	 */
	function setRows ($rows) {
		ArgumentValidator::validate($rows, IntegerValidatorRule::getRule());
		$this->_rows = $rows;
	}
	
	/**
	 * Sets the number of visible columns in this textarea.
	 * @param integer $cols
	 * @access public
	 * @return void
	 */
	function setColumns ($cols) {
		ArgumentValidator::validate($cols, IntegerValidatorRule::getRule());
		$this->_cols = $cols;
	}
	
	/**
	 * Sets a text-wrap attrtibute for the text area or null to unset.
	 *
	 * @param string $wrapStyle null, 'hard', 'soft', 'virtual', 'off', 'physical'
	 * @access public
	 * @return void
	 */
	function setWrap ($wrapStyle = null) {
		$styles = array(null, 'hard', 'soft', 'virtual', 'off', 'physical');
		if (!in_array($wrapStyle, $styles))
			throw new InvalidArgumentException("'$wrapStyle' is not one of ".implode(", ", $styles));
			
		$this->_wrapStyle = $wrapStyle;
	}
	
	/**
	 * Sets the size of this text area. This method allows Text-Areas to be used
	 * more interchangebly with text-fields
	 * @param int $size
	 * @access public
	 * @return void
	 */
	function setSize ($size) {
		$this->setColumns($size);
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
		
		$m = "\n\t\t\t<textarea rows='".$this->_rows."' cols='".$this->_cols."'";
		if (isset($this->_wrapStyle) && is_string($this->_wrapStyle))
			$m .= " wrap='".$this->_wrapStyle."'";
		$m .= "\n\t\t\t\tname='$name'";
		$m .= "\n\t\t\t\tid='$name'";
		$m .= (!$this->isEnabled()?" readonly='readonly'":"");
		
		if ($this->_style) {
			$m .= "\n\t\t\t\tstyle=\"".str_replace("\"", "\\\"", $this->_style)."\"";
		}
		
		if ($this->_onchange) {
			$m .= "\n\t\t\t\tonchange=\"".str_replace("\"", "\\\"", $this->_onchange)."\"";
		}
		
		if ($this->_value != null && $this->_value != $this->_startingDisplay) {
			$m .= ">".htmlspecialchars($this->_value);
		} else if ($this->_startingDisplay) {
			$v = htmlspecialchars($this->_startingDisplay, ENT_QUOTES);
			$m .= "\n\t\t\t\tonfocus='if (this.value == \"$v\") { this.value=\"\";   this.style.color=\"#000\";}'";
			$m .= "\n\t\t\t\tonblur='if (this.value == \"\") { this.value=\"$v\";  this.style.color=\"#888\";}'";
			$m .= " style='color: #888'>".$v;			
		} else {
			$m .= ">".htmlspecialchars($this->_value);;
		}
		
		$m .= "</textarea>";
		
		$errText = $this->getErrorText();
		$errRule =$this->getErrorRule();
		$errStyle = $this->getErrorStyle();
		
		if ($errText && $errRule) {
			$m .= "<span id='".$fieldName."_error' style=\"padding-left: 10px; $errStyle\">&laquo; $errText</span>";	
			$m .= Wizard::getValidationJavascript($fieldName, $errRule, $fieldName."_error", $this->_showError);
			$this->_showError = false;
		}
		
		return $m;
	}
}

?>