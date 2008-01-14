<?php
/**
 * @since 1/14/08
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RequiredStepWizard.class.php,v 1.1 2008/01/14 20:57:19 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SimpleStepWizard.class.php");

/**
 * This wizard adds the ability to require users to visit certain steps.
 * 
 * @since 1/14/08
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RequiredStepWizard.class.php,v 1.1 2008/01/14 20:57:19 adamfranco Exp $
 */
class RequiredStepWizard
	extends SimpleStepWizard
{
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
	static function withText ($text, $class="RequiredStepWizard" ) {
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
	static function withDefaultLayout ($pre = '', $class="RequiredStepWizard" ) {
		return parent::withDefaultLayout($pre, $class);
	}
	
	
/*********************************************************
 * Instance Vars and methods
 *********************************************************/

		
	/**
	 * @var array $requiredSteps;  
	 * @access private
	 * @since 1/14/08
	 */
	private $requiredSteps;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 1/14/08
	 */
	public function __construct () {
		parent::__construct();
		$this->requiredSteps = array();
	}
	
	/**
	 * Make a step required
	 * 
	 * @param string $stepName
	 * @return void
	 * @access public
	 * @since 1/14/08
	 */
	public function makeStepRequired ($stepName) {
		if (is_null($this->_stepContainer->getStep($stepName)))
			throw new Exception("Unknown Step name, '$stepName'.");
		
		$this->requiredSteps[$stepName] = false;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @return boolean - TRUE if everything is OK
	 * @access public
	 * @since 1/14/08
	 */
	public function update ($fieldName) {
		$this->recordStepVisit();
		$ok = parent::update($fieldName);
		$this->recordStepVisit();
		return $ok;
	}
	
	/**
	 * Record the current step and update the save-button to reflect the visited-state
	 * of the steps
	 * 
	 * @return void
	 * @access protected
	 * @since 1/14/08
	 */
	protected function recordStepVisit () {
		if (isset($this->requiredSteps[$this->getCurrentStepName()]))
			$this->requiredSteps[$this->getCurrentStepName()] = true;
		
		$this->getSaveButton()->setEnabled($this->doneVisitingRequired());
	}
	
	/**
	 * Answer true if we are done visiting the required steps.
	 * 
	 * @return boolean
	 * @access protected
	 * @since 1/14/08
	 */
	protected function doneVisitingRequired () {
		foreach ($this->requiredSteps as $name => $visited) {
			if (!$visited)
				return false;
		}
		return true;
	}
	
}

?>