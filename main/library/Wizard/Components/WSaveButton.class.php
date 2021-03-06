<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveButton.class.php,v 1.7 2007/11/16 18:39:40 adamfranco Exp $
 */ 
 
require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.class.php");

/**
 * This adds a save button to a wizard. It will automatically trigger the Wizard's save
 * event
 * 
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveButton.class.php,v 1.7 2007/11/16 18:39:40 adamfranco Exp $
 */
class WSaveButton 
	extends WEventButton 
{

	function __construct() {
		parent::__construct();
		
		$this->setEventAndLabel("edu.middlebury.polyphony.wizard.save", dgettext("polyphony","Save"));
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
		$button = new WSaveButton();
		$button->setLabel($label);
		return $button;
	}
}

?>