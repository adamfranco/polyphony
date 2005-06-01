<?php
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: login_type.act.php,v 1.6 2005/06/01 19:33:51 gabeschine Exp $
 */

$isAuthenticated = FALSE;
$authN =& Services::getService("AuthN");
$typeString = urldecode($harmoni->request->get("polyphony/type"));
$typeParts = explode("::", $typeString);
$authType = new Type ($typeParts[0],$typeParts[1],$typeParts[2]);

/*
$currentPathInfo = array_slice($harmoni->pathInfoParts, 3);
$returnURL = MYURL."/".implode("/",$currentPathInfo);
$getString = "";
if (count($_GET)) {
	$getString .= "?";
	foreach ($_GET as $name => $value) {
		$getString .= "&".$name."=".$value;
	}
	$returnURL .= $getString;
}
*/

if ($authN->isUserAuthenticated($authType)) {
	$harmoni->history->goBack("polyphony/login");
//	header("Location: ".$returnURL);
}
// If we aren't authenticated, try to authenticate.
else {
	// Try authenticating with this type
	$authN->authenticateUser($authType);

	// If they are authenticated, return.
	if ($authN->isUserAuthenticated($authType)) {
		$harmoni->history->goBack("polyphony/login");
//		header("Location: ".$returnURL);
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
		$defaultTextDomain = textdomain(NULL);
		textdomain("polyphony");
		
		
		ob_start();
		
		print "<p>";
		print _("Log in failed.");
		print "\n<br /><a href='".$harmoni->history->getReturnURL("polyphony/login")."'>";
		print _("Go Back");
		print "</a> ";
		print _(" or ");
		print "\n<a href='".$harmoni->request->quickURL("auth","login_type")."'>";
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