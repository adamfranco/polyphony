<?

/**
* remove_from_group.act.php
* This action will add the agent and group ids passed to it to the specified group.
* 11/10/04 Adam Franco
* copyright 2004 MIddlebury College
*/



$agentManager =& Services::getService("Agent");

printpre($_REQUEST);

$id =& $shared->getId($_REQUEST['destinationgroup']);
$destGroup =& $shared->getGroup($id);

foreach ($_REQUEST as $idString => $type) {

	if ($type == "group") {
		$id =& $agentManager->getId(strval($idString));
		$member =& $agentManager->getGroup($id);
		$destGroup->remove($member);
		
	} else if ($type == "agent") {
		$id =& $agentManager->getId(strval($idString));
		$member =& $agentManager->getAgent($id);
		$destGroup->remove($member);
	}	
}

// Send us back to where we were
$currentPathInfo = array_slice($harmoni->pathInfoParts, 2);

header("Location: ".MYURL."/".implode("/",$currentPathInfo));