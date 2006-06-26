<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelContinueLogic.class.php,v 1.2 2006/06/26 12:51:46 adamfranco Exp $
 */ 

/**
 * a logic rule that does not persist data changes
 * 
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelContinueLogic.class.php,v 1.2 2006/06/26 12:51:46 adamfranco Exp $
 */
class WCancelContinueLogic extends WLogicRule {
		
	/**
	 * constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function WCancelContinueLogic () {
		parent::WLogicRule();
		
	}
	
	
}

?>