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
 * @version $Id: delete_group.act.php,v 1.9 2005/09/07 21:18:25 adamfranco Exp $
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
if ($authZ->isUserAuthorized($ids->getId("edu.middlebury.authorization.delete"), $ids->getId($idString))) {
	$agentManager->deleteGroup($ids->getId($idString));
}

// Send us back to where we were (add_delete_group.php)

$harmoni->request->endNamespace();

$harmoni->history->goBack("polyphony/agents/delete_group");

// Delete the given group
//$shared->deleteGroup($groupId);
