<?php

/**
 * @since Jul 22, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WUpdateListener.class.php,v 1.4 2007/09/19 14:04:51 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WizardEventListener.abstract.php");

/**
 * Supplies an event listener to a {@link Wizard} for update events, this will
 * pass the control back to the wizard if it would like it.
 * 
 * @since Jul 22, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WUpdateListener.class.php,v 1.4 2007/09/19 14:04:51 adamfranco Exp $
 */
class WUpdateListener 
	extends WizardEventListener 
	/* implements EventListener */ 
{	
	var $_added = false;
	var $_callBackFunction;
	
	/**
	 * constructor
	 * 
	 * @param string $callBack the function to call when the event is called
	 * @access public
	 * @since 8/04/06
	 */
	function WUpdateListener ($callBack) {
		$this->_callBackFunction = $callBack;
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
		$this->_attemptAdding();
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
	 * Handles an event triggered by an {@link EventTrigger}. The event type is passed in case this
	 * particular EventListener is handling more than one type of event.
	 * @param string $eventType
	 * @param ref object $source The source object of the event.
	 * @param array $context An array of contextual parameters - the content will be dependent on the thrown event.
	 * @access public
	 * @return void
	 */
	function handleEvent ($eventType, $source, $context) {
		if ($eventType == 'edu.middlebury.polyphony.wizard.update'){
			$action = $this->_callBackFunction.'($source, $context);';
			eval($action);
		}
	}
}
?>