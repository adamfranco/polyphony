<?php

/**
 * @since Jul 22, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveCancelListener.class.php,v 1.3.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WizardEventListener.abstract.php");

/**
 * Supplies an event listener to a {@link Wizard} and keeps track of if a Save or Cancel event
 * was posted. This is useful for when saving Wizards in session to handle events. 
 * 
 * @since Jul 22, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveCancelListener.class.php,v 1.3.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */
class WSaveCancelListener 
	extends WizardEventListener 
	/* implements EventListener */ 
{
	var $_save = false;
	var $_cancel = false;
	
	var $_added = false;
		
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
		$this->_save = $this->_cancel = false;
	}
	
	/**
	 * Returns TRUE if a save event was triggered this pageload.
	 * @access public
	 * @return boolean
	 */
	function isSaveRequested () {
		return $this->_save;
	}
	
	/**
	 * Returns TRUE if a cancel event was triggered this pageload.
	 * @access public
	 * @return boolean
	 */
	function isCancelRequested () {
		return $this->_cancel;
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
	function handleEvent ($eventType, &$source, $context) {
		switch ($eventType) {
			case 'edu.middlebury.polyphony.wizard.save':
				$this->_save = true;
				$this->_cancel = false;
				break;
			case 'edu.middlebury.polyphony.wizard.cancel':
				$this->_save = false;
				$this->_cancel = true;
				break;
		}
	}
}
?>