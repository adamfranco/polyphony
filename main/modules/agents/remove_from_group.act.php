<?php

/**
 * remove_from_group.act.php
 * This action will add the agent and group ids passed to it to the specified group.
 * 11/10/04 Adam Franco
 * @copyright 2004 MIddlebury College
 * @package polyphony.modules.agents
 */



$agentManager =& Services::getService("Agent");
$idManager =& Services::getService("Id");

//printpre($_REQUEST);

$id =& $idManager->getId($_REQUEST['destinationgroup']);
$destGroup =& $agentManager->getGroup($id);

foreach ($_REQUEST as $idString => $type) {

	if ($type == "group") {
		$id =& $idManager->getId(strval($idString));
		$member =& $agentManager->getGroup($id);
		$destGroup->remove($member);
		
	} else if ($type == "agent") {
		$id =& $idManager->getId(strval($idString));
		$member =& $agentManager->getAgent($id);
		$destGroup->remove($member);
	}	
}

// Send us back to where we were
$currentPathInfo = array_slice($harmoni->pathInfoParts, 2);

header("Location: ".MYURL."/".implode("/",$currentPathInfo));
