<?

/**
* process_authorizations.act.php
* This action will create or delete authorizations as specified by edit_authorizations.act.php
* 11/18/04 Ryan Richards
* copyright 2004 MIddlebury College
*/

// Get services
$shared =& Services::getService("Shared");
$authZ =& Services::getService("AuthZ");

// Get info passed to this action via the URL
$createOrDelete = $harmoni->pathInfoParts['2'];
$agentIdString = $harmoni->pathInfoParts['3'];
$functionIdString = $harmoni->pathInfoParts['4'];
$qualifierIdString = $harmoni->pathInfoParts['5'];

// Get Ids from these strings
$agentId =& $shared->getId($agentIdString);
$functionId =& $shared->getId($functionIdString);
$qualifierId =& $shared->getId($qualifierIdString);


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

header("Location: ".MYURL."/".implode("/",$currentPathInfo)."?selection=".$_GET['selection']);
