<?php
/**
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WDynamicStep.class.php,v 1.3 2007/09/19 14:04:51 adamfranco Exp $
 */

require_once(POLYPHONY."/main/library/Wizard/Components/WizardStep.class.php");

/**
 * The Wizard class provides a system for registering Wizard properties and 
 * associating those properties with the appropriate form elements.
 *
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WDynamicStep.class.php,v 1.3 2007/09/19 14:04:51 adamfranco Exp $
 * @author Gabe Schine
 */
class WDynamicStep extends WizardStep {
	
	var $_callBack="";

	
	
	
	/**
	 * Sets the callback function of this dynamic wizard step
	 * @param string $displayName
	 * @return void
	 */
	function setDynamicFunction($function) {
		$this->_callBack = $function;
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
		
		if($this->_callBack!=""){
			$code = '$this->_contentText = '.$this->_callBack.'($this);';
			 eval($code);	
		}		
		return Wizard::parseText($this->_contentText, $this->getChildren(), $fieldName."_");
	}
	
	/**
	 * Sets a {@link WizardComponent} to this component, and returns the newly added component.
	 * This is actually the same as addComponent, but the name makes a little more sense.
	 *
	 * @param string $name The short-string name of the component - this is used for creating form input field names and storing data.
	 * @param ref object $component A {@link WizardComponent} to add.
	 * @access public
	 * @return ref object
	 */
	function setComponent ($name, $component) {
		return $this->addComponent($name,$component);
	}
	
}

