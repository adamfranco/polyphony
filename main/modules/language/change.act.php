<?php
/**
 * @since 7/21/05
 * @package polyphony.language
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: change.act.php,v 1.12 2007/12/17 15:52:05 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * Change the language to the one specified by the user
 * 
 * @since 7/21/05
 * @package polyphony.language
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: change.act.php,v 1.12 2007/12/17 15:52:05 adamfranco Exp $
 */
class changeAction
	extends Action
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function execute () {
		$harmoni = Harmoni::instance();
		// Set the new language
		$langLoc = Services::getService('Lang');
		$harmoni->request->startNamespace("polyphony");
		$langLoc->setLanguage($harmoni->request->get("language"));
		$harmoni->request->endNamespace();
		
		debug::output("Setting the language to ".$harmoni->request->get("polyphony/language"));
		debug::output("SESSION: ".printpre($_SESSION, TRUE));
		
		$harmoni->history->goBack("polyphony/language/change");
		
		$null = null;
		return $null;
	}
	
}

?>