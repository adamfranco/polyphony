<?

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
	
	header("Location: ".MYURL."/".implode("/",$currentPathInfo));
} else {
	throwError(new Error("Could not authenticate, but we weren't forwarded to the failed auth action for some reason.","Polyphony::auth::login",true));
}