<?php
/**
 * @since 7/21/05
 * @package polyphony.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: login.act.php,v 1.17 2007/10/12 19:18:50 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * Change the language to the one specified by the user
 * 
 * @since 7/21/05
 * @package polyphony.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: login.act.php,v 1.17 2007/10/12 19:18:50 adamfranco Exp $
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
	function execute () {
		$harmoni = Harmoni::instance();
		
		unset($_SESSION['polyphony/login_failed']);
		
		// If we are using only cookies, but cookies aren't enabled
		// (and hence not set), print an error message.
		if ($harmoni->config->get("sessionUseOnlyCookies")
			&& !isset($_COOKIE[$harmoni->config->get("sessionName")]))
		{
			RequestContext::sendTo($harmoni->request->quickURL('auth', 'cookies_required'));
		}

		$isAuthenticated = FALSE;
		$authN = Services::getService("AuthN");
		
		// authenticate.
		$authTypes =$authN->getAuthenticationTypes();
		while ($authTypes->hasNext()) {
			$authType =$authTypes->next();
			
			// Try authenticating with this type
			$authN->authenticateUser($authType);
			
			// If they are authenticated, quit
			if ($authN->isUserAuthenticated($authType)) {
				$isAuthenticated = TRUE;
				break;
			}
		}

		if ($isAuthenticated) {
			// Send us back to where we want to be if we succeeded 
			// (usually where we were)
			$harmoni->history->goBack("polyphony/display_login");
		} else {
			$_SESSION['polyphony/login_failed'] = true;
			// send us to where we want to be if we failed
			// (possibly some form of authentication viewer)
			$harmoni->history->goBack("polyphony/login_fail");
		}
		
		$null = null;
		return $null;
	}
}