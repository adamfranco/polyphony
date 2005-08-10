<?php

/**
 * delete_group.act.php
 * This action will delete the group as specified by add_delete_group.act.php
 * 11/29/04 Ryan Richards
 *
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: delete_group.act.php,v 1.8 2005/08/10 21:20:17 gabeschine Exp $
 */

$harmoni->request->startNamespace("polyphony-agents");
$harmoni->request->passthrough("expandedGroups");

// Get services
$agentManager =& Services::getService("Agent");
$authZ =& Services::getService("AuthZ");
$ids =& Services::getService("Id");

// Get info passed to this action via the URL
$idString = $harmoni->request->get("groupId");

// check our authorization
if ($authZ->isUserAuthorized($ids->getId("edu.middlebury.authorization.delete_groups"), $ids->getId("edu.middlebury.authorization.root"))) {
	$agentManager->deleteGroup($ids->getId($idString));
}

// Send us back to where we were (add_delete_group.php)

$harmoni->request->endNamespace();

$harmoni->history->goBack("polyphony/agents/delete_group");

// Delete the given group
//$shared->deleteGroup($groupId);
