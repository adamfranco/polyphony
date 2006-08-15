<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelButton.class.php,v 1.7 2006/08/15 20:51:43 sporktim Exp $
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
 * @version $Id: WCancelButton.class.php,v 1.7 2006/08/15 20:51:43 sporktim Exp $
 */
class WCancelButton extends WEventButton {
	function WCancelButton($label=null) {
		if(is_null($label)){
			$label = dgettext("polyphony", "Cancel");
		}
		$this->setEventAndLabel("edu.middlebury.polyphony.wizard.cancel", $label);
		$this->addOnClick("ignoreValidation(this.form);");
	}

}

?>