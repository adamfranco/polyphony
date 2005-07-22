<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveButton.class.php,v 1.1 2005/07/22 15:42:34 gabeschine Exp $
 */ 
 
require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.abstract.php");

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
 * @version $Id: WSaveButton.class.php,v 1.1 2005/07/22 15:42:34 gabeschine Exp $
 */
class WSaveButton extends WEventButton {
	function WSaveButton() {
		$this->setEventAndLabel("edu.middlebury.polyphony.wizard.save", dgettext("polyphony","Save"));
	}
}

?>