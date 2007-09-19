<?php

/**
 * @since Jul 22, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardEventListener.abstract.php,v 1.3 2007/09/19 14:04:51 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * Supplies an event listener to a {@link Wizard}
 * 
 * 
 * @since Jul 22, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardEventListener.abstract.php,v 1.3 2007/09/19 14:04:51 adamfranco Exp $
 */
class WizardEventListener 
	extends WizardComponent 
	/* implements EventListener */ 
{
	/**
	 * Sets this component's parent (some kind of {@link WizardComponentWithChildren} so that it can
	 * have access to its information, if needed.
	 * @param ref object $parent
	 * @access public
	 * @return void
	 */
	function setParent ($parent) {
		$this->_parent =$parent;
		
		$this->_attemptAdding();
	}
	
	/**
	 * Attempts to add ourselves to the parent {@link Wizard} as an {@link EventListener}.
	 * @access public
	 * @return void
	 */
	function _attemptAdding () {
		if ($this->_added) return;
		
		$wz =$this->getWizard();
		if ($wz) {
			$wz->addEventListener($this);
			$this->_added = true;
		}
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
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
		
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return null;
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
		return '';
	}
	
	/**
	 * Handles an event triggered by an {@link EventTrigger}. The event type is passed in case this
	 * particular EventListener is handling more than one type of event.
	 * @param string $eventType
	 * @param ref object $source The source object of the event.
	 * @param array $context An array of contextual parameters - the content will be dependent on the thrown event.
	 * @access public
	 * @return void
	 */
	function handleEvent ($eventType, $source, $context) {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
}
?>