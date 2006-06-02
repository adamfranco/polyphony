<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WNextStepButton.class.php,v 1.5.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.class.php");

/**
 * This adds a "Next" button to the wizard and throws the appropriate event.
 * 
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WNextStepButton.class.php,v 1.5.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */
class WNextStepButton 
	extends WEventButton 
{

	var $_stepContainer;
	
	/**
	 * Constructor
	 * @param ref object $stepContainer A {@link WizardStepContainer} object.
	 * @access public
	 * @return void
	 */
	function WNextStepButton (&$stepContainer) {
		$this->setLabel(dgettext("polyphony", "Next"));
		$this->_stepContainer =& $stepContainer;
		$this->setControl(true);
	}
	
	/**
	 * fires the control of this step
	 * 
	 * @return void
	 * @access public
	 * @since 6/2/06
	 */
	function fire () {
		$wiz =& $this->getWizard();
		$wiz->triggerEvent('edu.middlebury.polyphony.wizard.update', $wiz);
		$wiz->triggerLater('edu.middlebury.polyphony.wizard.next_step', $wiz);
	}
		
	/**
	 * Answers true if this component will be enabled.
	 * @access public
	 * @return boolean
	 */
	function isEnabled () {
		return $this->_stepContainer->hasNext();
	}

}

?>