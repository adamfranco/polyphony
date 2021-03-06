<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WRadioList.class.php,v 1.13 2007/10/10 22:58:56 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * This class allows for the creation of lists of input type='radio' items of a specific group.
 * 
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WRadioList.class.php,v 1.13 2007/10/10 22:58:56 adamfranco Exp $
 */
class WRadioList 
	extends WizardComponent 
{

	var $_eachPre;
	var $_eachPost;
	var $_pre;
	var $_post;
	var $_value;
	var $_onchange;
	var $_style = null;
	
	var $_items = array();
	var $_extendedHtml = array();
	
	/**
	 * Virtual constructor - creates this object with the specified layout.
	 * @param string $pre A string to prepend onto the markup block (ex, "<ul>")
	 * @param string $eachPre A string to put at the beginning of each of the 
	 *		elements (ex, "<li>")
	 * @param string $eachPost A string to put at the end of each of the 
	 *		elements (ex, "</li>")
	 * @param string $post A string to tack onto the end of the block (ex, "</ul>")
	 * @access public
	 * @return ref object
	 * @static
	 */
	static function withLayout ($pre, $eachPre, $eachPost, $post, $class='WRadioList') {
		$obj = new $class();
		$obj->_pre = $pre;
		$obj->_post = $post;
		$obj->_eachPre = $eachPre;
		$obj->_eachPost = $eachPost;
		return $obj;
	}
	
	/**
	 * Constructor
	 * @access public
	 * @return WRadioList
	 */
	function __construct () {
		$this->_pre = $this->_post = '';
		$this->_eachPost = "\n<br/>";
	}
	
	/**
	 * sets the CSS style for the labels of the radio buttons.
	 * @param string $style
	 * @access public
	 * @return void
	 */
	function setStyle ($style) {
		$this->_style = $style;
	}
	
	/**
	 * Sets the value of this radio button group.
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
		$this->_value = $value;
	}
	
	/**
	 * Add commands to the javascript onchange attribute.
	 * @param string $commands
	 * @access public
	 * @return void
	 */
	function addOnChange($commands) {
		$this->_onchange .= " ".$commands;
	}
	
	/**
	 * Adds a radio option to this list.
	 * @param string $value The short value that represents the displayed text.
	 * @param optional string $displayText The text to show to the end user. Defaults to $value.
	 * @param optional string $extendedHtml Text to add to the item after the display text.
	 * @access public
	 * @return void
	 */
	function addOption ($value, $displayText = null, $extendedHtml = null) {
		if ($displayText == null) $displayText = $value;
		$this->_items[$value] = $displayText;
		$this->_extendedHtml[$value] = $extendedHtml;
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
		
		$m = $this->_pre;
		$style = '';
		
		if ($this->_style) $style = " style=\"".addslashes($this->_style)."\""; 
		
		$s = array();
		$ids = array();
		foreach (array_keys($this->_items) as $key) {
			$ids[] = $name . "_" . addslashes($key);
		}
		foreach (array_keys($this->_items) as $key) {
			$disp = $this->_items[$key];
			$extendedHtml = $this->getExtendedHtml($fieldName, $key);
			$checked = $this->_value==$key?" checked='checked'":"";
			$val = htmlspecialchars($key, ENT_QUOTES);
			
			$javascript = '';
			$id = $name . "_" . addslashes($key);
			$others = array_diff(array($id), $ids);
			
			$javascript .= "document.getElementById('$id').checked = true; ";
			foreach ($others as $otherId) {
				$javascript .= "document.getElementById('$otherId').checked = false; ";
			}
			
			if ($this->_onchange)
				$javascript .= " ".str_replace("\"", "\\\"", $this->_onchange)."; ";
			
			$s[] = "<label onmousedown=\"$javascript\" style='cursor: pointer;'$style><input type='radio' name='$name' id='$id' value='$val'$checked /> $disp</label>".$extendedHtml;
		}
		
		$m .= $this->_eachPre;
		$m .= implode($this->_eachPost.$this->_eachPre, $s);
		$m .= $this->_eachPost;
		
		$m .= $this->_post;
				
		return $m;
	}
	
	/**
	 * Answer the extended HTML for an item
	 * 
	 * @param string $fieldName The field name to use when outputting form data or
	 * @param string $key
	 * @return string
	 * @access protected
	 * @since 5/19/08
	 */
	protected function getExtendedHtml ($fieldName, $key) {
		return $this->_extendedHtml[$key];
	}
}

?>