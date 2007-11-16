<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WChooseOptionButton.class.php,v 1.6 2007/11/16 18:39:40 adamfranco Exp $
 */

require_once(dirname(__FILE__)."/WEventButton.class.php");

/**
 * This appears to be an implementation of EventButton that centers around a 
 * dropdown menu.
 * 
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WChooseOptionButton.class.php,v 1.6 2007/11/16 18:39:40 adamfranco Exp $
 */
class WChooseOptionButton 
	extends WEventButton
{
	var $_options = array();
	var $_option;
	
	/**
	 * virtual constructor
	 * @param string $event
	 * @param string $label
	 * @access public
	 * @return ref object
	 * @static
	 */
	static function withEventAndLabel ($event, $label) {
		$obj = new WChooseOptionButton();
		$obj->setEventAndLabel($event, $label);
		
		return $obj;
	}
	
	/**
	 * virtual constructor - creates the button with a "nop" event
	 * @param string $label
	 * @access public
	 * @return ref object
	 * @static
	 */
	static function withLabel ($label) {
		$obj = new WChooseOptionButton();
		$obj->_label = $label;
		return $obj;
	}
	
	/**
	 * Add an array of value collections for use when adding
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 * @access public
	 * @since 11/1/05
	 */
	function addOptionValue ( $name, $value ) {
		$this->_options[$name] =$value;
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
		parent::update($fieldName);
		$val = RequestContext::value($fieldName);
		$option = stripslashes(RequestContext::value($fieldName."_option"));
		if ($val) {
			$this->_option = $option;
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
		$option = $this->_option;
		$this->_pressed = false;
		$this->_option = null;
		
		$values = array();
		$values['pressed'] = $val;
		$values['option_name'] = $option;
		$values['option'] =$this->_options[$option];
		return $values;
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
		$m = parent::getMarkup($fieldName);
		$m .= "\n<select name='".$name."_option'>";
		
		foreach (array_keys($this->_options) as $option)
			if ($option != '')
				$m .= "\n\t<option value='".addslashes($option)."'>".$option."</option>";
		
		$m .= "\n</select>";
		
		return $m;
	}
}

?>