<?php
/**
 * @package polyphony.modules.authentication
 */

$authN =& Services::getService("AuthN");
$typeString = urldecode($harmoni->pathInfoParts[2]);
$typeParts = explode("::", $typeString);
$authType = new Type ($typeParts[0],$typeParts[1],$typeParts[2]);

// Try authenticating with this type
$authN->destroyAuthenticationForType($authType);

// Send us back to where we were
$currentPathInfo = array_slice($harmoni->pathInfoParts, 3);
	
header("Location: ".MYURL."/".implode("/",$currentPathInfo));