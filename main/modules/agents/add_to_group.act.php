<?

/**
* add_to_group.act.php
* This action will add the agent and group ids passed to it to the specified group.
* 11/10/04 Adam Franco
* copyright 2004 MIddlebury College
*/



$idManager =& Services::getService("Id");
$agentManager =& Services::getService("Agent");

//printpre($_REQUEST);

$id =& $idManager->getId($_REQUEST['destinationgroup']);
$destGroup =& $agentManager->getGroup($id);

foreach ($_REQUEST as $idString => $type) {

	if ($type == "group") {
		$id =& $idManager->getId(strval($idString));
		$member =& $agentManager->getGroup($id);
		$destGroup->add($member);
		
	} else if ($type == "agent") {
		$id =& $idManager->getId(strval($idString));
		$member =& $agentManager->getAgent($id);
		$destGroup->add($member);
	}	
}

// Send us back to where we were
$currentPathInfo = array_slice($harmoni->pathInfoParts, 2);

header("Location: ".MYURL."/".implode("/",$currentPathInfo));