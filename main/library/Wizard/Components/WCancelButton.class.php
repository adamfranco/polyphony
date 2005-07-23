<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelButton.class.php,v 1.2 2005/07/23 01:55:29 gabeschine Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.abstract.php");

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
 * @version $Id: WCancelButton.class.php,v 1.2 2005/07/23 01:55:29 gabeschine Exp $
 */
class WCancelButton extends WEventButton {
	function WCancelButton() {
		$this->setEventAndLabel("edu.middlebury.polyphony.wizard.cancel", dgettext("polyphony","Cancel"));
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
		$label = htmlentities($this->_label, ENT_QUOTES);
		return "<input type='submit' name='$name' value='$label' onclick='ignoreValidation(this.form)' />";
	}
}

?>