<?

/**
* remove_from_group.act.php
* This action will add the agent and group ids passed to it to the specified group.
* 11/10/04 Adam Franco
* copyright 2004 MIddlebury College
*/



$shared =& Services::getService("Shared");

printpre($_REQUEST);

$id =& $shared->getId($_REQUEST['destinationgroup']);
$destGroup =& $shared->getGroup($id);

foreach ($_REQUEST as $idString => $type) {

	if ($type == "group") {
		$id =& $shared->getId(strval($idString));
		$member =& $shared->getGroup($id);
		$destGroup->remove($member);
		
	} else if ($type == "agent") {
		$id =& $shared->getId(strval($idString));
		$member =& $shared->getAgent($id);
		$destGroup->remove($member);
	}	
}

// Send us back to where we were
$currentPathInfo = array_slice($harmoni->pathInfoParts, 2);

header("Location: ".MYURL."/".implode("/",$currentPathInfo));