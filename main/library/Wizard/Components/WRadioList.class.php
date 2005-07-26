<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WRadioList.class.php,v 1.2 2005/07/26 20:30:39 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.interface.php');

/**
 * This class allows for the creation of lists of input type='radio' items of a specific group.
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WRadioList.class.php,v 1.2 2005/07/26 20:30:39 adamfranco Exp $
 */
class WRadioList extends WizardComponent {
	var $_eachPre;
	var $_eachPost;
	var $_pre;
	var $_post;
	var $_parent;
	var $_value;
	var $_style = null;
	
	var $_items = array();
	
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
	function &withLayout ($pre, $eachPre, $eachPost, $post, $class='WRadioList') {
		$obj =& new $class();
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
	function WRadioList () {
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
	 * Adds a radio option to this list.
	 * @param string $value The short value that represents the displayed text.
	 * @param string $displayText The text to show to the end user.
	 * @access public
	 * @return void
	 */
	function addOption ($value, $displayText) {
		$this->_items[$value] = $displayText;
	}
	
	/**
	 * Sets this component's parent (some kind of {@link WizardComponentWithChildren} so that it can
	 * have access to its information, if needed.
	 * @param ref object $parent
	 * @access public
	 * @return void
	 */
	function setParent (&$parent) {
		$this->_parent =& $parent;
	}
	
	/**
	 * Returns the top-level {@link Wizard} in which this component resides.
	 * @access public
	 * @return ref object
	 */
	function &getWizard () {
		return $this->_parent->getParent();
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
			$checked = $this->_value==$key?" checked":"";
			$val = htmlentities($key, ENT_QUOTES);
			
			$javascript = '';
			$id = $name . "_" . addslashes($key);
			$others = array_diff(array($id), $ids);
			
			$javascript .= "document.getElementById('$id').checked = true; ";
			foreach ($others as $otherId) {
				$javascript .= "document.getElementById('$otherId').checked = false; ";
			}
			
			$s[] = "<label onmousedown=\"$javascript\" style='cursor: pointer;'$style><input type='radio' name='$name' id='$id' value='$val'$checked /> $disp</label>";
		}
		
		$m .= $this->_eachPre;
		$m .= implode($this->_eachPost.$this->_eachPre, $s);
		$m .= $this->_eachPost;
		
		$m .= $this->_post;
				
		return $m;
	}
}

?>