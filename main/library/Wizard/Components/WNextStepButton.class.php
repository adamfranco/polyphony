<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WNextStepButton.class.php,v 1.2 2005/08/10 17:52:05 adamfranco Exp $
 */ 
 
require_once(POLYPHONY."/main/library/Wizard/WizardComponent.abstract.php");

/**
 * This adds a "Next" button to the wizard and throws the appropriate event.
 * 
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WNextStepButton.class.php,v 1.2 2005/08/10 17:52:05 adamfranco Exp $
 */
class WNextStepButton 
	extends WizardComponent 
{

	var $_stepContainer;
	
	/**
	 * Constructor
	 * @param ref object $stepContainer A {@link WizardStepContainer} object.
	 * @access public
	 * @return void
	 */
	function WNextStepButton (&$stepContainer) {
		$this->_stepContainer =& $stepContainer;
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
		$wizard =& $this->getWizard();
		$wizard->_test = $fieldName;
		$val = RequestContext::value($fieldName);
		if ($val) {
			// advance the step!
			$this->_stepContainer->nextStep();
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
		$name = RequestContext::name($fieldName);
		$disabled = $this->_stepContainer->hasNext()?"":" disabled";
		$label = dgettext("polyphony","Next");
		return "<input type='submit' name='$name' value='$label'$disabled />";
	}
}

?>