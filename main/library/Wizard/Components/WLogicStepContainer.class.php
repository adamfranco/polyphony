<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WLogicStepContainer.class.php,v 1.6 2007/09/04 20:28:07 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WizardStepContainer.class.php");

/**
 * StepContainer that add steps to its stack as the wizard goes along
 * 
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WLogicStepContainer.class.php,v 1.6 2007/09/04 20:28:07 adamfranco Exp $
 */
class WLogicStepContainer extends WizardStepContainer {
		
	var $_stepStack;
	
	var $_backNamesStack;
	var $_backStepsStack;
	var $_forwardNamesStack;
	var $_forwardStepsStack;
	
	
	
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function WLogicStepContainer () {
		parent::WizardStepContainer();
		$this->_currStep = null;
		$this->_stepStack = array();
		$this->_backNamesStack = array();
		$this->_backStepsStack = array();
		$this->_forwardNamesStack = array();
		$this->_forwardStepsStack = array();
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
	 * Set the steps for this step container.  Remove forward and backward capability.
	 *
	 * @access public
	 * @param array $arrayOfSteps
	 * $return void
	 */
	function setRequiredSteps($arrayOfSteps) {
		//steps are listed backwards in a stack.
		$this->_stepStack = array_reverse($arrayOfSteps);
		
	
		
		$this->nextStep();		

			//clear forward and back
		$this->_backNamesStack = array();
		$this->_backStepsStack = array();
		$this->_forwardNamesStack = array();
		$this->_forwardStepsStack = array();	
	}

	
	
	
	
	/**
	 * Goes to the next step, if possible, by popping the next step off the stack.
	 * @param ref object WLogicButton $button that has logic attached
	 * @access public
	 * @return void
	 */
	function nextStep ($button=null) {
		
		
		
		//add this step to the back stacks		
		if(!is_null($this->_currStep)){
			array_push($this->_backNamesStack,$this->getCurrentStepName());	
			
			
			array_push($this->_backStepsStack,array_values($this->_stepStack));	
		}
		
		
		
		
		
		
		//clear the forward stacks
		$this->_forwardNamesStack = array();
		$this->_forwardStepsStack = array();

		//if there is a WLogicButton passed in, add the appropriate steps
		
		if(func_num_args()>0){
			$controller =$button->getLogicRule();		
			$this->pushSteps($controller->getRequiredSteps());			
		}
		
		$nextStep = array_pop($this->_stepStack);
		
		
		$this->setStep($nextStep);
		
		
	}
	
	/**
	 * Go backward in the history, if possible.  
	 * @access public
	 * @return void
	 */
	function goBack() {
		//aren't stacks awesome?  This code is so awesome, I'll add my name--Tim
		
		
		
		//add the current step to back in case we need to return to it
		array_push($this->_forwardNamesStack,$this->getCurrentStepName());
		array_push($this->_forwardStepsStack,array_values($this->_stepStack));		
		
		//remove and save the values from the back stacks
		$nextStep = array_pop($this->_backNamesStack);
		$nextStack =  array_values(array_pop($this->_backStepsStack));
		
	
		//change to the right step
		$this->setStep($nextStep);
		$this->_stepStack = $nextStack;
		
	
		
	}
	
	/**
	 * Go forward in the history, if possible.  
	 * @access public
	 * @return void
	 */
	function goForward() {
		//aren't stacks awesome?  This code is so awesome, I'll my name--Tim
		
		//add the current step to forward in case we need to return to it
		array_push($this->_backNamesStack,$this->getCurrentStepName());
		array_push($this->_backStepsStack,array_values($this->_stepStack));		
		
		//remove and save the values from the forward stacks
		$nextStep = array_pop($this->_forwardNamesStack);
		$nextStack =  array_values(array_pop($this->_forwardStepsStack));
		
		//change to the right step
		$this->setStep($nextStep);
		$this->_stepStack = $nextStack;
	}
	
	
	/**
	 * Returns if this StepContainer has a next forward step.
	 * @access public
	 * @return boolean
	 */
	function canGoForward () {
		return (count($this->_forwardNamesStack) > 0);
	}
	
	/**
	 * Returns if this StepContainer has a next forward step.
	 * @access public
	 * @return boolean
	 */
	function canGoBack () {
		return (count($this->_backNamesStack) > 0);
	}
	
	
	function previousStep () {
		// do nothing, can't go back in logic wizard
		throwError(new Error("A (logic) Wizard nevers goes back on his word! (or his steps)","WLogicStepContainer",true));
	}
	
	/**
	 * Returns if this StepContainer has a next step.
	 * @access public
	 * @return boolean
	 */
	function hasNext () {
		return (count($this->_stepStack) > 0)?true:false;
	}
	
	
	function hasPrevious () {
		// do nothing, can't go back in logic wizard
		throwError(new Error("A (logic) Wizard nevers goes back on his word! (or his steps)","WLogicStepContainer",true));
	}
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE.
	 * 
	 * @access public
	 * @return boolean
	 */
	function validate () {
		$step =$this->_steps[$this->_currStep];
		return $step->validate();
	}
	
	
	/**
	 * adds the passed steps to the current step stack if they are not there
	 * 
	 * @param array $steps
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function pushSteps ($steps) {
		$steps = array_reverse($steps);
		foreach ($steps as $step){
			array_push($this->_stepStack, $step);
		}
		
	}
	
	
}

?>