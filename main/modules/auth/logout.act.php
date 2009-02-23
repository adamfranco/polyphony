<?php
/**
 * @since 7/21/05
 * @package polyphony.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: logout.act.php,v 1.13 2007/12/17 16:15:20 adamfranco Exp $
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
 * @version $Id: logout.act.php,v 1.13 2007/12/17 16:15:20 adamfranco Exp $
 */
class logoutAction
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
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function execute () {
		$harmoni = Harmoni::instance();
		$authN = Services::getService("AuthN");
		
		unset($_SESSION['polyphony/login_failed']);
		
		// dethenticate. :-)
		$authN->destroyAuthentication();
		
		// Send us back to where we were
		$harmoni->history->goBack("polyphony/display_login");
		
		$null = null;
		return $null;
	}
}