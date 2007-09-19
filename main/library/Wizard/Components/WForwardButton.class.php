<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WForwardButton.class.php,v 1.3 2007/09/19 14:04:51 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.class.php");

/**
 * This adds a "Back" button to the wizard and throws the appropriate event.
 * 
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WForwardButton.class.php,v 1.3 2007/09/19 14:04:51 adamfranco Exp $
 */
class WForwardButton extends WEventButton {
	
	var $_stepContainer;

	/**
	 * Constructor
	 * @param ref object $stepContainer A {@link WizardStepContainer} object.
	 * @access public
	 * @return void
	 */
	function WForwardButton ($stepContainer) {
		$this->setLabel(_("Forward -->"));
		$this->_stepContainer =$stepContainer;
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
		$wizard =$this->getWizard();
		if ($this->getAllValues()) {
			// go forward!
			$this->_stepContainer->goForward();
		}
	}
	
	/**
	 * Answers true if this component will be enabled.
	 * @access public
	 * @return boolean
	 */
	function isEnabled () {
		return $this->_stepContainer->canGoForward();
	}

}

?>