<?php
/**
 * @since 7/21/05
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: login_type.act.php,v 1.14 2005/07/22 15:35:16 adamfranco Exp $
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
 * @version $Id: login_type.act.php,v 1.14 2005/07/22 15:35:16 adamfranco Exp $
 */
class login_typeAction
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
		
		//$isAuthenticated = FALSE;
		$authN =& Services::getService("AuthN");

		$harmoni->request->startNamespace("polyphony");
		$authType =& HarmoniType::stringToType(urldecode($harmoni->request->get("type")));
		$harmoni->request->endNamespace();
	
		if ($authN->isUserAuthenticated($authType)) {
			$harmoni->history->goBack("polyphony/login");
		}
		// If we aren't authenticated, try to authenticate.
		else {
			$harmoni->request->startNamespace("polyphony");
			
			$currentUrl =& $harmoni->request->mkURL();
			$currentUrl->setValue("type", $harmoni->request->get("type"));
			$harmoni->history->markReturnURL("polyphony/authentication", $currentUrl);
				
			$harmoni->request->endNamespace();
			
			// Try authenticating with this type
			$authN->authenticateUser($authType);
		
			// If they are authenticated, return.
			if ($authN->isUserAuthenticated($authType)) {
				$harmoni->history->goBack("polyphony/login");
			}
			
			// Otherwise, print our our failed-login screen:
			else {
				
				// Set our textdomain
				$defaultTextDomain = textdomain("polyphony");
				
				$harmoni->request->startNamespace("polyphony");
				
				ob_start();
				
				print "<p>";
				print _("Log in failed.");
				print "\n<br /><a href='".$harmoni->history->getReturnURL("polyphony/login")."'>";
				print _("Go Back");
				print "</a> ";
				print _(" or ");
				print "\n<a href='".$harmoni->history->getReturnURL("polyphony/authentication")."'>";
				print _("Try Again.");
				print "</p>";
				
				$introText =& new Block(ob_get_contents(), 3);
				ob_end_clean();
				
				$harmoni->request->endNamespace();
				
				// go back to the default text domain
				textdomain($defaultTextDomain);
				
				return $introText;
			}
		}
	}
}