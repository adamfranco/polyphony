<?php
/**
 * @since 7/21/05
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: login.act.php,v 1.10 2005/07/22 15:35:14 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * Change the language to the one specified by the user
 * 
 * @since 7/21/05
 * @package polyphony.modules.language
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: login.act.php,v 1.10 2005/07/22 15:35:14 adamfranco Exp $
 */
class loginAction
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
	function execute ( &$harmoni ) {
		$harmoni =& Harmoni::instance();

		$isAuthenticated = FALSE;
		$authN =& Services::getService("AuthN");
		
		// authenticate.
		$authTypes =& $authN->getAuthenticationTypes();
		while ($authTypes->hasNext()) {
			$authType =& $authTypes->next();
			
			// Try authenticating with this type
			$authN->authenticateUser($authType);
			
			// If they are authenticated, quit
			if ($authN->isUserAuthenticated($authType)) {
				$isAuthenticated = TRUE;
				break;
			}
		}
		
		if ($isAuthenticated) {
			// Send us back to where we were
			$harmoni->history->goBack("polyphony/login");
		} else {
			throwError(new Error("Could not authenticate, but we weren't forwarded to the failed auth action for some reason.","Polyphony::auth::login",true));
		}
	}
}