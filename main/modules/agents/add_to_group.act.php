<?php

/**
 * add_to_group.act.php
 * This action will add the agent and group ids passed to it to the specified group.
 * 11/10/04 Adam Franco
 *
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_to_group.act.php,v 1.7 2005/06/07 12:28:37 gabeschine Exp $
 */



$idManager =& Services::getService("Id");
$agentManager =& Services::getService("Agent");

$harmoni->request->startNamespace("polyphony-agents");

//printpre($_REQUEST);

$id =& $idManager->getId(RequestContext::value('destinationgroup'));
$destGroup =& $agentManager->getGroup($id);

foreach ($harmoni->request->getKeys() as $idString) {

	$type = RequestContext::value($idString);

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

$harmoni->request->endNamespace();

// Send us back to where we were
$harmoni->history->goBack("polyphony/agents/add_to_group");
//header("Location: ".MYURL."/".implode("/",$currentPathInfo));
