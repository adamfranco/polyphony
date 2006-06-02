<?php
/**
 * @since 5/31/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WLogicStepContainer.class.php,v 1.1 2006/06/02 16:00:28 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WizardStepContainer.class.php");

/**
 * StepContainer that uses logic rules to add steps to its queue
 * 
 * @since 5/31/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WLogicStepContainer.class.php,v 1.1 2006/06/02 16:00:28 cws-midd Exp $
 */
class WLogicStepContainer extends WizardStepContainer {
		
	var $_stepQueue;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function WLogicStepContainer () {
		parent::WizardStepContainer();
		$this->_stepQueue = array();
	}
	
	/**
	 * tells the wizard component to update itself... via the current step.
	 * 
	 * @param string $fieldName
	 * @return boolean
	 * @access public
	 * @since 5/31/06
	 */
	function update ($fieldName) {
		return $this->_steps[$this->_currStep]->update($fieldName."_".
			$this->_stepNames[$this->_currStep]);
	}
	
	/**
	 * Goes to the next step, if possible.  Via invoking logic class.
	 * @param ref object WLogicButton $button that has logic attached
	 * @access public
	 * @return void
	 */
	function nextStep (&$button) {
		$controller =& $button->getLogicRule();
		
		$this->addStepsToQueue($controller->getRequiredSteps());
		$oldStep = $this->_currStep;
		$this->setStep(array_shift($this->_stepQueue));

		$wizard =& $this->getWizard();
		$wizard->triggerLater("edu.middlebury.polyphony.wizard.step_changed", $this, array(
				'from'=>$oldStep, 'to'=>$this->_currStep));
	}
	
	function previousStep () {
		// do nothing, can't go back in logic wizard
	}
	
	/**
	 * Returns if this StepContainer has a next step.
	 * @access public
	 * @return boolean
	 */
	function hasNext () {
		return (count($this->_stepQueue) > 0)?true:false;
	}
	
	function hasPrevious () {
		// do nothing, can't go back in logic wizard
	}
	
	/**
	 * adds the passed steps to the current step queue if they are not there
	 * 
	 * @param array $steps
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function addStepsToQueue ($steps) {
		$this->_stepQueue = array_unique(
							array_merge($this->_stepQueue, $steps));
	}
}

?>