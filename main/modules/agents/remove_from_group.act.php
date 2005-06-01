<?php

/**
 * remove_from_group.act.php
 * This action will add the agent and group ids passed to it to the specified group.
 * 11/10/04 Adam Franco
 *
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: remove_from_group.act.php,v 1.7 2005/06/01 19:33:35 gabeschine Exp $
 */

$harmoni->request->startNamespace("polyphony/agents");

$agentManager =& Services::getService("Agent");
$idManager =& Services::getService("Id");

//printpre($_REQUEST);

$id =& $idManager->getId($harmoni->request->get('destinationgroup'));
$destGroup =& $agentManager->getGroup($id);

foreach ($harmoni->request->getKeys() as $idString) {
	
	$type = $harmoni->request->get($idString);
	
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

$harmoni->request->endNamespace("polyphony/agents");

// send us back to where we were before we started this operation
$harmoni->history->goBack("polyphony/agents/remove_from_group");