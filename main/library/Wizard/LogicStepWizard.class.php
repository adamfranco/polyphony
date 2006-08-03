<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: LogicStepWizard.class.php,v 1.5 2006/08/03 20:51:57 sporktim Exp $
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
 * @version $Id: LogicStepWizard.class.php,v 1.5 2006/08/03 20:51:57 sporktim Exp $
 */
 
 require_once(POLYPHONY."/main/library/Wizard/StepWizard.abstract.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WLogicStepContainer.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WSaveContinueButton.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WLogicButton.class.php");


 
 
 
 
 
class LogicStepWizard extends StepWizard {
		
	var $_saveContinueButton;

	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function LogicStepWizard () {
		$this->_stepContainer =& new WLogicStepContainer();
		$this->_saveContinueButton =& new WSaveContinueButton($this->_stepContainer);
		$this->_nextButton =& new WNextStepButton($this->_stepContainer);
		$this->_cancelButton =& new WCancelButton();
		$this->_saveButton =& new WSaveButton();
		$this->addComponent('_steps', $this->_stepContainer);
		//$this->_stepContainer->addComponent('_saveContinue', $this->_saveContinueButton);
		//$this->_stepContainer->addComponent('_cancelContinue', $this->_cancelContinueButton);
		$this->addComponent('_saveContinue', $this->_saveContinueButton);
		$this->addComponent('_next', $this->_nextButton);
		$this->addComponent('_cancel', $this->_cancelButton);
		$this->addComponent('_save', $this->_saveButton);
		
		print "LogicStepWizard";
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
	/*function update ($fieldName) {
			
		$ok =  $this->_stepContainer->update($fieldName."__steps");
		$this->triggerEvent("edu.middlebury.polyphony.wizard.update", $this);
		foreach (array_keys($this->_eventsLater) as $key) {
			$info =& $this->_eventsLater[$key];
			$this->triggerEvent($info[0], $info[1], $info[2]);
			unset($this->_eventsLater[$key]);
		}
		return $ok;
		
	}*/
	
	
	function &withDefaultLayout ($pre = '') {
		return parent::withText($pre . 
				"<div>\n" .
				"<table width='100%' border='0' cellpadding='0' cellspacing='2'>\n" .
				"<tr>\n" .
				"<td align='left' width='50%'>\n" .
				"[[_cancel]]<br/>\n" .
				"[[_save]]" .
				"</td>\n" .
				"<td align='right' width='50%'>\n" .
				"[[_next]]<br/>\n" .
				"[[_saveContinue]]" .
				"</td></tr></table>" .
				"</div>\n" .
				"<hr/>\n" .
				"<div>\n" .
				"[[_steps]]" .
				"</div>\n", "LogicStepWizard");
	}
	
	
}

?>