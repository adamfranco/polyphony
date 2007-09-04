<?php
/**
 * @since 6/7/07
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveWithChoiceButtonList.class.php,v 1.2 2007/09/04 20:28:08 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * The WSaveWithChoiceButtonList provides a set of multiple elements similar
 * to the WRadioList, but triggers a save action when one of the buttons is pressed.
 * The value of that button is then entered as the value of the list, providing
 * easy determination of which button was pressed by the client code.
 * 
 * @since 6/7/07
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveWithChoiceButtonList.class.php,v 1.2 2007/09/04 20:28:08 adamfranco Exp $
 */
class WSaveWithChoiceButtonList
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
	
	var $_event = "edu.middlebury.polyphony.wizard.save";
	
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
	function withLayout ($pre, $eachPre, $eachPost, $post, $class='WSaveWithChoiceButtonList') {
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
	function WSaveWithChoiceButtonList () {
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
		reset($this->_items);
		
		for ($i = 0; $i < count($this->_items); $i++) {
			if (RequestContext::value($fieldName."__".$i)) {
				$this->_value = key($this->_items);
				
				// trigger the save event on the wizard
				$wizard =$this->getWizard();
				$wizard->triggerLater($this->_event, $wizard);
				
				return true;
			}
			// loop through the items in sync with the indexes.
			next($this->_items);
		}
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
		ob_start();
		
		print $this->_pre;
		$style = '';
		
		if ($this->_style) $style = " style=\"".addslashes($this->_style)."\""; 
		
		$s = array();

		foreach (array_keys($this->_items) as $i => $key) {
			print $this->_eachPre;
			
			$display = $this->_items[$key];
			$extendedHtml = $this->_extendedHtml[$key];
			$name = RequestContext::name($fieldName."__".$i);
			
			$javascript = '';
			if ($this->_onchange)
				$javascript = " ".str_replace("\"", "\\\"", $this->_onchange)."; ";
			
			print "<input type='submit' name='$name' value=\"$display\" onclick=\"$javascript\"/>";
			print $extendedHtml;
			
			print $this->_eachPost;
		}
		
		print $this->_post;
				
		return ob_get_clean();
	}
}

?>