<?php
/**
 * @since 5/31/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveContinueLogic.class.php,v 1.1.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */ 

/**
 * a logic rule that persists data changes
 * 
 * @since 5/31/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveContinueLogic.class.php,v 1.1.2.1 2006/06/02 21:04:47 cws-midd Exp $
 */
class WSaveContinueLogic extends WLogicRule {
		
	/**
	 * constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function WSaveContinueLogic () {
		parent::WLogicRule();
	}
}

?>