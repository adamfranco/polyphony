<?php
/**
 * @since 5/31/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveContinueButton.class.php,v 1.1.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */ 

/**
 * a button that persists data changes
 * 
 * @since 5/31/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveContinueButton.class.php,v 1.1.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */
class WSaveContinueButton extends WLogicButton {
	
	/**
	 * constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function WSaveContinueButton () {
		parent::withLogicAndLabel(new WSaveContinueLogic(), 'Save Changes and Continue');
	}
		
}

?>