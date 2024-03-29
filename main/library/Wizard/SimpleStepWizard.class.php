<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleStepWizard.class.php,v 1.17 2008/01/14 20:57:19 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/SimpleWizard.class.php");

require_once(POLYPHONY."/main/library/Wizard/Components/WizardStepContainer.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WNextStepButton.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WPreviousStepButton.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WSaveButton.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WCancelButton.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WStepDisplayBar.class.php");
require_once(POLYPHONY."/main/library/Wizard/Listeners/WStepChangedListener.class.php");

/**
 * typecomment
 * 
 * @since Jul 20, 2005
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleStepWizard.class.php,v 1.17 2008/01/14 20:57:19 adamfranco Exp $
 */
class SimpleStepWizard extends SimpleWizard {
	var $_stepContainer;
	var $_nextButton;
	var $_prevButton;
	var $_saveButton;
	var $_cancelButton;
	
	/**
	 * Constructor
	 * @access public
	 * @return void
	 */
	function __construct () {
		$this->_stepContainer = new WizardStepContainer();
		$this->_nextButton = new WNextStepButton($this->_stepContainer);
		$this->_prevButton = new WPreviousStepButton($this->_stepContainer);
		$this->_saveButton = new WSaveButton();
		$this->_cancelButton = new WCancelButton();
		
		$this->addComponent("_steps", $this->_stepContainer);
		$this->addComponent("_save", $this->_saveButton);
		$this->addComponent("_cancel", $this->_cancelButton);
		$this->addComponent("_next", $this->_nextButton);
		$this->addComponent("_prev", $this->_prevButton);
		$this->addComponent("_stepsBar", new WStepDisplayBar($this->_stepContainer));
		
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
	 * Returns a new SimpleStepWizard with the layout defined as passed. The layout
	 * may include any of the following tags:
	 * 
	 * _save		- 		a save button
	 * _cancel		-		a cancel button
	 * _steps		-		the place where the current step content will go
	 * _next		-		the next step button
	 * _prev		-		the previous step button
	 * @access public
	 * @param string $text
	 * @return ref object
	 * @static
	 */
	static function withText ($text, $class="SimpleStepWizard" ) {
		return parent::withText($text, $class);
	}
	
	/**
	 * Returns a new SimpleStepWizard with the default layout and a title.
	 * @param string $title
	 * @access public
	 * @return ref object
	 * @static
	 */
	static function withTitleAndDefaultLayout ($title) {
		return self::withDefaultLayout("<h2>$title</h2>\n");
	}
	
	/**
	 * Returns a new SimpleStepWizard with the default layout including all the buttons.
	 * @param optional string $pre Some text to put before the layout text.
	 * @access public
	 * @return ref object
	 * @static
	 */
	static function withDefaultLayout ($pre = '', $class="SimpleStepWizard") {
		return parent::withText($pre . 
				"<div>\n" .
				"[[_stepsBar]]" .
				"<table width='100%' border='0' cellpadding='0' cellspacing='2'>\n" .
				"<tr>\n" .
				"<td align='left' width='50%'>\n" .
				"[[_cancel]]<br/>\n" .
				"[[_prev]]" .
				"</td>\n" .
				"<td align='right' width='50%'>\n" .
				"[[_save]]<br/>\n" .
				"[[_next]]" .
				"</td></tr></table>" .
				"</div>\n" .
				"<hr/>\n" .
				"<div>\n" .
				"[[_steps]]" .
				"</div>\n", $class);
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
	
	/**
	 * Answer the 'Save' button to allow modification of its label and properties.
	 * 
	 * @return object WSaveButton
	 * @access public
	 * @since 6/4/07
	 */
	function getSaveButton () {
		return $this->_saveButton;
	}
	
	/**
	 * Answer the 'Cancel' button to allow modification of its label and properties.
	 * 
	 * @return object WSaveButton
	 * @access public
	 * @since 6/4/07
	 */
	function getCancelButton () {
		return $this->_cancelButton;
	}
}

?>