<?php

/**
 * @since Jul 22, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveCancelListener.class.php,v 1.1 2007/11/16 18:50:59 adamfranco Exp $
 */ 

/**
 * Supplies an event listener to a {@link Wizard} and keeps track of if a Save or Cancel event
 * was posted. This is useful for when saving Wizards in session to handle events. 
 * 
 * @since Jul 22, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveCancelListener.class.php,v 1.1 2007/11/16 18:50:59 adamfranco Exp $
 */
class WSaveCancelListener 
	extends WizardEventListener 
{
	var $_save = false;
	var $_cancel = false;
	
	var $_added = false;
	
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