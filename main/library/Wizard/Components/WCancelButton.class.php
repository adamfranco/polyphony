<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelButton.class.php,v 1.10 2007/11/16 18:39:40 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.class.php");

/**
 * This adds a cancel button to a wizard. It will automatically trigger the Wizard's cancel
 * event.
 * 
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelButton.class.php,v 1.10 2007/11/16 18:39:40 adamfranco Exp $
 */
class WCancelButton 
	extends WEventButton 
{

	function __construct($label=null) {
		parent::__construct();
		
		if(is_null($label)){
			$label = dgettext("polyphony", "Cancel");
		}
		$this->setEventAndLabel("edu.middlebury.polyphony.wizard.cancel", $label);
		$this->addOnClick("ignoreValidation(this.form);");
	}

	/**
	 * Virtual constructor for a custom label
	 * 
	 * @param string $label
	 * @return object WSaveButton
	 * @access public
	 * @static
	 * @since 7/27/05
	 */
	static function withLabel($label) {
		$button = new WCancelButton();
		$button->setLabel($label);
		return $button;
	}
}

?>