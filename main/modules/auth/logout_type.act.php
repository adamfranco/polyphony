<?php
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: logout_type.act.php,v 1.3 2005/04/11 18:27:05 adamfranco Exp $
 */
 
$authN =& Services::getService("AuthN");
$typeString = urldecode($harmoni->pathInfoParts[2]);
$typeParts = explode("::", $typeString);
$authType = new Type ($typeParts[0],$typeParts[1],$typeParts[2]);

// Try authenticating with this type
$authN->destroyAuthenticationForType($authType);

// Send us back to where we were
$currentPathInfo = array_slice($harmoni->pathInfoParts, 3);
$returnHeader = "Location: ".MYURL."/".implode("/",$currentPathInfo);
if (count($_GET)) {
	$returnHeader .= "?";
	foreach ($_GET as $name => $value) {
		$returnHeader .= "&".$name."=".$value;
	}
}
	
header($returnHeader);