<?php
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: login_type.act.php,v 1.10 2005/06/07 12:29:15 gabeschine Exp $
 */
 
require_once(HARMONI."GUIManager/Components/Block.class.php");


require_once(HARMONI."GUIManager/Components/Block.class.php");

$isAuthenticated = FALSE;
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
		return $introText;
	}
}