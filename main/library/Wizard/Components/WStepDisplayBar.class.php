<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WStepDisplayBar.class.php,v 1.6 2007/10/16 13:44:33 adamfranco Exp $
 */ 
 
require_once(POLYPHONY."/main/library/Wizard/WizardComponent.abstract.php");

/**
 * This adds a "Next" button to the wizard and throws the appropriate event.
 * 
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WStepDisplayBar.class.php,v 1.6 2007/10/16 13:44:33 adamfranco Exp $
 */
class WStepDisplayBar 
	extends WizardComponent 
{
	var $_stepContainer;
// 	var $_event = "'edu.middlebury.polyphony.wizard.step_changed";
	
	/**
	 * Constructor
	 * @param ref object $stepContainer A {@link WizardStepContainer} object.
	 * @param optional string $event
	 * @access public
	 * @return void
	 */
	function WStepDisplayBar ($stepContainer) {
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
		$val = RequestContext::value($fieldName);
		if ($val !== '' && $val !== null) {
			// advance the step!
			$this->_stepContainer->setStepByKey($val);
		}
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return null;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		ob_start();
		print "<div>";
		print "<input type='hidden' name='".RequestContext::name($fieldName)."' value=''/>";
		
		$steps = $this->_stepContainer->getSteps();
		$currStep = $this->_stepContainer->getCurrentStep();
		$a = array();
		foreach (array_keys($steps) as $stepKey) {
			ob_start();
			if ($stepKey == $currStep) 
				print "<b>";
			else {
				print "<a href='#' onclick=\"";
				print "var input = this.parentNode.firstChild; ";
				print "input.value = '".$stepKey."'; ";
				print "submitWizard(input.form); ";
				print "return false; \">";
			}
			
			print ($stepKey+1).". ".$steps[$stepKey]->getDisplayName();
			
			if ($stepKey == $currStep) 
				print "</b>";
			else
				print "</a>";
			
			$a[] = ob_get_clean();
		}
		print implode("\n&raquo;\n", $a);
		print "</div>";
		return ob_get_clean();
	}
}

?>