<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextArea.class.php,v 1.5 2005/10/20 19:10:01 adamfranco Exp $
 */ 

require_once(dirname(__FILE__).'/WTextInput.abstract.php');

/**
 * This class allows for the creation of a textarea element
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextArea.class.php,v 1.5 2005/10/20 19:10:01 adamfranco Exp $
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
		$m .= "\n\t\t\t\tname='$name'";
		$m .= ($this->_readonly?" readonly='readonly'":"");
		
		if ($this->_style) {
			$m .= "\n\t\t\t\tstyle=\"".str_replace("\"", "\\\"", $this->_style)."\"";
		}
		
		if ($this->_onchange) {
			$m .= "\n\t\t\t\tonchange=\"".str_replace("\"", "\\\"", $this->_onchange)."\"";
		}
		
		if ($this->_value != null && $this->_value != $this->_startingDisplay) {
			$m .= ">".htmlentities($this->_value);
		} else if ($this->_startingDisplay) {
			$v = htmlentities($this->_startingDisplay, ENT_QUOTES);
			$m .= "\n\t\t\t\tonfocus='if (this.value == \"$v\") { this.value=\"\"; }'";
			$m .= "\n\t\t\t\tonblur='if (this.value == \"\") { this.value=\"$v\"; }'";
			$m .= ">".$v;			
		} else {
			$m .= ">".htmlentities($this->_value);;
		}
		
		$m .= "</textarea>";
		
		$errText = $this->getErrorText();
		$errRule =& $this->getErrorRule();
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