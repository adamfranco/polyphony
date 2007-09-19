<?php
/**
 * @since 7/21/05
 * @package polyphony.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: logout.act.php,v 1.10 2007/09/19 14:04:53 adamfranco Exp $
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
 * @version $Id: logout.act.php,v 1.10 2007/09/19 14:04:53 adamfranco Exp $
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
	function execute ( $harmoni ) { 
		$authN = Services::getService("AuthN");
		
		// dethenticate. :-)
		$authN->destroyAuthentication();
		
		// Send us back to where we were
		$harmoni->history->goBack("polyphony/login");
		
		$null = null;
		return $null;
	}
}