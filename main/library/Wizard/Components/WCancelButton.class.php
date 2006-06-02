<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelButton.class.php,v 1.5.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.class.php");

/**
 * This adds a cancel button to a wizard. It will automatically trigger the Wizard's cancel
 * event.
 * 
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelButton.class.php,v 1.5.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */
class WCancelButton extends WEventButton {
	function WCancelButton() {
		$this->setEventAndLabel("edu.middlebury.polyphony.wizard.cancel", dgettext("polyphony","Cancel"));
		$this->setOnClick("ignoreValidation(this.form)");
		$this->setControl(true);
	}

}

?>