<?

/**
* delete_group.act.php
* This action will delete the group as specified by add_delete_group.act.php
* 11/29/04 Ryan Richards
* copyright 2004 MIddlebury College
*/

// Get services
$agentManager =& Services::getService("Agent");
$authZ =& Services::getService("AuthZ");

// Get info passed to this action via the URL
$idString = $harmoni->pathInfoParts['2'];

// Get the groupId from the Id string
//$groupId = $shared->getId($idString);


// Send us back to where we were (add_delete_group.php)
$currentPathInfo = array_slice($harmoni->pathInfoParts, 3);
header("Location: ".MYURL."/".implode("/",$currentPathInfo));



// Delete the given group
//$shared->deleteGroup($groupId);



