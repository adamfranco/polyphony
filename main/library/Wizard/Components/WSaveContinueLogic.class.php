<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveContinueLogic.class.php,v 1.3 2006/07/14 19:40:19 sporktim Exp $
 */ 

/**
 * a logic rule that persists data changes
 * 
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveContinueLogic.class.php,v 1.3 2006/07/14 19:40:19 sporktim Exp $
 */
 
 
 require_once(POLYPHONY."/main/library/Wizard/Components/WLogicRule.class.php");
 
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