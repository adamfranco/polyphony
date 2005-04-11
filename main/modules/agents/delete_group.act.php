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
 * @version $Id: delete_group.act.php,v 1.6 2005/04/11 20:02:57 adamfranco Exp $
 */

// Get services
$agentManager =& Services::getService("Agent");
$authZ =& Services::getService("AuthZ");

// Get info passed to this action via the URL
$idString = $harmoni->pathInfoParts['2'];


// Send us back to where we were (add_delete_group.php)
$currentPathInfo = array_slice($harmoni->pathInfoParts, 3);
header("Location: ".MYURL."/".implode("/",$currentPathInfo));



// Delete the given group
//$shared->deleteGroup($groupId);
