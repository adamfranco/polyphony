<?php
/**
 * @since 5/31/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: LogicStepWizard.class.php,v 1.9 2007/10/10 22:58:52 adamfranco Exp $
 */ 

/**
 * A step wizard that allows for complexities beyond a linear path of steps
 * 
 * @since 5/31/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: LogicStepWizard.class.php,v 1.9 2007/10/10 22:58:52 adamfranco Exp $
 */
 
 require_once(POLYPHONY."/main/library/Wizard/StepWizard.abstract.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WLogicStepContainer.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WSaveContinueButton.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WBackButton.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WForwardButton.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WLogicButton.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WCallbackButton.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WDynamicStep.class.php");


 
 
 
 
 
class LogicStepWizard extends StepWizard {
		
	//var $_saveContinueButton;
	var $_backButton;
	var $_forwardButton;

	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function __construct () {
		$this->_stepContainer = new WLogicStepContainer();
		$this->addComponent('_steps', $this->_stepContainer);
		
		
		//$this->_saveContinueButton = new WSaveContinueButton($this->_stepContainer);
		//$this->_nextButton = new WNextStepButton($this->_stepContainer);
		$this->_backButton = new WBackButton($this->_stepContainer);
		$this->_forwardButton = new WForwardButton($this->_stepContainer);
		$this->_cancelButton = new WCancelButton();
		$this->_saveButton = new WSaveButton();


		//$this->addComponent('_saveContinue', $this->_saveContinueButton);
		//$this->addComponent('_next', $this->_nextButton);
		
		$this->addComponent('_back', $this->_backButton);
		$this->addComponent('_forward', $this->_forwardButton);
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
	static function withText ($text, $class = 'LogicStepWizard') {
		return parent::withText($text, $class);
	}
	
	/**
	 * Set the required steps for this wizard
	 *
	 * @access public
	 * @param array $arrayOfSteps
	 * $return void
	 */
	function setRequiredSteps($arrayOfSteps) {
		$this->_stepContainer->setRequiredSteps($arrayOfSteps);
	}
	
	/**
	 * Get the step container for this wizard
	 *
	 * @access public
	 * $return object WLogicStepContainer
	 */
	function getStepContainer(){
		return $this->_stepContainer;
	}

	
	
	function withDefaultLayout ($pre = '') {
		return parent::withText($pre . 
				"<div>\n" .
				"<table width='100%' border='0' cellpadding='0' cellspacing='2'>\n" .
				"<tr>\n" .
				"<td align='left' width='33%'>\n" .
				"[[_back]]\n" .
				"[[_forward]]" .
				"</td>\n" .
				"<td align='center' width='34%'>\n" .
				"[[_save]]\n" .
				"[[_cancel]]" .
				"</td>\n" .
				"<td align='right' width='34%'>\n" .
				"&nbsp" .
				"</td>\n" .
				"</tr></table>" .
				"</div>\n" .
				"<hr/>\n" .
				"<div>\n" .
				"[[_steps]]" .
				"</div>\n", "LogicStepWizard");
	}
	
	
}

?>