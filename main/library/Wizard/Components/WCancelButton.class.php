<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelButton.class.php,v 1.6 2006/06/07 19:22:35 adamfranco Exp $
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
 * @version $Id: WCancelButton.class.php,v 1.6 2006/06/07 19:22:35 adamfranco Exp $
 */
class WCancelButton extends WEventButton {
	function WCancelButton() {
		$this->setEventAndLabel("edu.middlebury.polyphony.wizard.cancel", dgettext("polyphony","Cancel"));
		$this->addOnClick("ignoreValidation(this.form);");
	}

}

?>