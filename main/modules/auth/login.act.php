<?
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: login.act.php,v 1.8 2005/06/07 13:41:54 adamfranco Exp $
 */
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
	$currentPathInfo = array_slice($harmoni->pathInfoParts, 2);
	
	$harmoni->history->goBack("polyphony/login");
} else {
	throwError(new Error("Could not authenticate, but we weren't forwarded to the failed auth action for some reason.","Polyphony::auth::login",true));
}