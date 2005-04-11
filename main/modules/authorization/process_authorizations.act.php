<?

/**
 * process_authorizations.act.php
 * This action will create or delete authorizations as specified by edit_authorizations.act.php
 * 11/18/04 Ryan Richards
 *
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: process_authorizations.act.php,v 1.9 2005/04/11 20:03:07 adamfranco Exp $
 */

// Get services
$idManager =& Services::getService("Id");
$authZ =& Services::getService("AuthZ");

// Get info passed to this action via the URL
$createOrDelete = $harmoni->pathInfoParts['2'];
$agentIdString = $harmoni->pathInfoParts['3'];
$functionIdString = $harmoni->pathInfoParts['4'];
$qualifierIdString = $harmoni->pathInfoParts['5'];

// Get Ids from these strings
$agentId =& $idManager->getId($agentIdString);
$functionId =& $idManager->getId($functionIdString);
$qualifierId =& $idManager->getId($qualifierIdString);


// Process authorizations
if ($createOrDelete == 'create') {
	$authZ->createAuthorization($agentId, $functionId, $qualifierId);

} else {
	$authorizations =& $authZ->getExplicitAZs($agentId, $functionId, $qualifierId, false);
	while ($authorizations->hasNext()) {
		$authorization =& $authorizations->next();
		$authZ->deleteAuthorization($authorization);

	}
}


// Send us back to where we were (edit_authorizations.act.php)
$currentPathInfo = array_slice($harmoni->pathInfoParts, 6);

header("Location: ".MYURL."/".implode("/",$currentPathInfo)."?agent=".$_GET['agent']);
