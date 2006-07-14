<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: LogicStepWizard.class.php,v 1.3 2006/07/14 19:40:18 sporktim Exp $
 */ 

/**
 * A step wizard that allows for complexities beyond a linear path of steps
 * 
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: LogicStepWizard.class.php,v 1.3 2006/07/14 19:40:18 sporktim Exp $
 */
 
 require_once(POLYPHONY."/main/library/Wizard/StepWizard.abstract.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WLogicStepContainer.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WSaveContinueButton.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WCancelContinueButton.class.php");

 
 
 
 
 
class LogicStepWizard extends StepWizard {
		
	var $_saveContinueButton;
	var $_cancelContinueButton;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function LogicStepWizard () {
		$this->_stepContainer =& new WLogicStepContainer();
		$this->_saveContinueButton =& new WSaveContinueButton();
		$this->_cancelContinueButton =& new WCancelContinueButton();
		$this->_cancelButton =& new WCancelButton();
		$this->_saveButton =& new WSaveButton();
		
		$this->addComponent('_steps', $this->_stepContainer);
		$this->_stepContainer->addComponent('_saveContinue', $this->_saveContinueButton);
		$this->_stepContainer->addComponent('_cancelContinue', $this->_saveContinueButton);
		$this->addComponent('_cancel', $this->_cancelButton);
		$this->addComponent('_save', $this->_saveButton);
	}
	
	/**
	 * Returns a new LogicStepWizard with the layout defined as passed. The layout
	 * may include any of the following tags:
	 * 
	 * _saveContinue		- 		a next button that saves changes
	 * _cancelContinue		-		a next button that does not save changes
	 * _steps		-		the place where the current step content will go
	 * _cancel		-		the next step button
	 * _save		-		the previous step button
	 * @access public
	 * @param string $text
	 * @return ref object
	 * @static
	 */
	function &withText ($text) {
		return parent::withText($text, 'LogicStepWizard');
	}

	/**
	 * updates the wizard by updating the current step and allowing the logic to flow
	 * 
	 * @param string $fieldName
	 * @return boolean
	 * @access public
	 * @since 5/31/06
	 */
	function update ($fieldName) {
		return $this->_stepContainer->update($fieldName."__steps");
	}
}

?>