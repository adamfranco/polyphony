<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: StepWizard.abstract.php,v 1.4 2007/09/04 20:28:05 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/SimpleWizard.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WizardStepContainer.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WCancelButton.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WStepDisplayBar.class.php");


/**
 * Abstract parent for wizards that contain multiple steps
 * 
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: StepWizard.abstract.php,v 1.4 2007/09/04 20:28:05 adamfranco Exp $
 */
class StepWizard extends SimpleWizard {
		
	var $_stepContainer;
	var $_cancelButton;

	/**
	 * Answers the step container
	 * 
	 * @return array the steps for this wizard
	 * @access public
	 * @since 5/5/06
	 */
	function getSteps () {
		return $this->_stepContainer->getSteps();
	}

	/**
	 * Adds a new {@link WizardStep} to this wizard.
	 * @param string $name A short id/name for this step.
	 * @param ref object $step
	 * @access public
	 * @return ref object
	 */
	function addStep ($name, $step) {
		return $this->_stepContainer->addStep($name, $step);
	}
	
	/**
	 * Sets the step to the named step.
	 * @param string $name
	 * @access public
	 * @return void
	 */
	function setStep ($name) {
		$this->_stepContainer->setStep($name);
	}
// @todo passing the class of the step wizard to the parent classes!!
	
	/**
	 * Answer the name of the current step
	 * 
	 * @return string
	 * @access public
	 * @since 6/5/07
	 */
	function getCurrentStepName () {
		return $this->_stepContainer->getCurrentStepName();
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$values = parent::getAllValues();
		return $values['_steps'];
	}

}

?>