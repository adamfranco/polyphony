<?php
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: login_type.act.php,v 1.3 2005/04/07 17:07:53 adamfranco Exp $
 */

$isAuthenticated = FALSE;
$authN =& Services::getService("AuthN");
$typeString = urldecode($harmoni->pathInfoParts[2]);
$typeParts = explode("::", $typeString);
$authType = new Type ($typeParts[0],$typeParts[1],$typeParts[2]);

$currentPathInfo = array_slice($harmoni->pathInfoParts, 3);
$returnHeader = "Location: ".MYURL."/".implode("/",$currentPathInfo);

if ($authN->isUserAuthenticated($authType)) {
	header($returnHeader);
}
// If we aren't authenticated, try to authenticate.
else {
	// Try authenticating with this type
	$authN->authenticateUser($authType);

	// If they are authenticated, return.
	if ($authN->isUserAuthenticated($authType)) {
		header($returnHeader);
	}
	
	// Otherwise, print our our failed-login screen:
	else {
		// Get the Layout compontents. See core/modules/moduleStructure.txt
		// for more info. 
		$harmoni->ActionHandler->execute("window", "screen");
		$mainScreen =& $harmoni->getAttachedData('mainScreen');
		$statusBar =& $harmoni->getAttachedData('statusBar');
		$centerPane =& $harmoni->getAttachedData('centerPane');
		
		// Set our textdomain
		$defaultTextDomain = textdomain();
		textdomain("polyphony");
		
		
		ob_start();
		
		print "<p>";
		print _("Log in failed.");
		print "\n<br /><a href='".MYURL."/".implode("/",$currentPathInfo)."'>";
		print _("Go Back");
		print "</a> ";
		print _(" or ");
		print "\n<a href='".MYURL."/auth/login_type/".$harmoni->pathInfoParts[2]."/".implode("/",$currentPathInfo)."'>";
		print _("Try Again.");
		print "</p>";
		
		$introText =& new Block(ob_get_contents(), 2);
		ob_end_clean();
		$centerPane->add($introText, null, null, CENTER, CENTER);
		
		// go back to the default text domain
		textdomain($defaultTextDomain);
		
		// return the main layout.
		return $mainScreen;
	}
}