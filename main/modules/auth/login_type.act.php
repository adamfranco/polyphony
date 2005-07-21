<?php
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: login_type.act.php,v 1.12 2005/07/21 00:10:45 thebravecowboy Exp $
 */
 
require_once(HARMONI."GUIManager/Components/Block.class.php");
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

class login_typeAction
	extends MainWindowAction
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
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Login");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();

		// Set our textdomain
		$defaultTextDomain = textdomain("polyphony");
		
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
				$defaultTextDomain = textdomain(NULL);
				textdomain("polyphony");
				
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
				
				$introText =& new Block(ob_get_contents(), 2);
				ob_end_clean();
				
				$harmoni->request->endNamespace();
				
				// go back to the default text domain
				textdomain($defaultTextDomain);
				
				// return the main layout.
				//return $introText;
			}
		}
	}
}