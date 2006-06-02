<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveButton.class.php,v 1.3.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */ 
 
require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.class.php");

/**
 * This adds a save button to a wizard. It will automatically trigger the Wizard's save
 * event
 * 
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveButton.class.php,v 1.3.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */
class WSaveButton extends WEventButton {
	function WSaveButton() {
		$this->setLabel(dgettext("polyphony","Save"));
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
		$wiz->triggerLater('edu.middlebury.polyphony.wizard.save', $wiz);
	}
	
	/**
	 * Virtual constructor for a custom label
	 * 
	 * @param string $label
	 * @return object WSaveButton
	 * @access public
	 * @since 7/27/05
	 */
	function &withLabel($label) {
		$button =& new WSaveButton();
		$button->setLabel($label);
		return $button;
	}
}

?>