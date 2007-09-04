<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WBackButton.class.php,v 1.2 2007/09/04 20:28:06 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.class.php");

/**
 * This adds a "Back" button to the wizard and throws the appropriate event.
 * 
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WBackButton.class.php,v 1.2 2007/09/04 20:28:06 adamfranco Exp $
 */
class WBackButton extends WEventButton {
	
	var $_stepContainer;

	/**
	 * Constructor
	 * @param ref object $stepContainer A {@link WizardStepContainer} object.
	 * @access public
	 * @return void
	 */
	function WBackButton ($stepContainer) {
		$this->setLabel(_("<-- Back"));
		$this->_stepContainer =$stepContainer;
		$this->addOnClick("ignoreValidation(this.form);");
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
		if ($this->getAllValues()) {
			// go back!
			$this->_stepContainer->goBack();
		}
	}
	
	/**
	 * Answers true if this component will be enabled.
	 * @access public
	 * @return boolean
	 */
	function isEnabled () {
		return $this->_stepContainer->canGoBack();
	}

}

?>