<?

/**
* choose_agent.act.php
* This file will allow the user to choose an agent for which to edit authorizations.
* The agents will be listed both by group and by agent.
* The chosen agent information will be submitted to edit_authorizations.act.php via form action.
* 11/10/04 Ryan Richards
* copyright 2004 MIddlebury College
*/


// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$actionRows =& new RowLayout();

// In order to preserve proper nesting on the HTML output
$actionRows->setPreSurroundingText("<form method='post' action='".MYURL."/authorization/edit_authorizations/'>");
$actionRows->setPostSurroundingText("</form>");

$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Users and Groups")));
$actionRows->addComponent($introHeader);

$sharedManager =& Services::getService("Shared");


//$sharedManager->createGroup("Staff", new HarmoniType("Groups", "Middlebury College", "User Status", "Status of the user at Middlebury College"), "Middlebury College Staff.");

// $id =& $sharedManager->getId("200");
// $group =& $sharedManager->getGroup($id);
// $memberId =& $sharedManager->getId("198");
// $member =& $sharedManager->getAgent($memberId);
// $group->add($member);


// Loop through all of the Groups and figure out which ones are childen of
// other groups, so that we can just display the root-groups
$childGroupIds = array();
$groups =& $sharedManager->getGroups();
while ($groups->hasNext()) {
	$group =& $groups->next();
	$childGroups =& $group->getGroups(FALSE);
	while ($childGroups->hasNext()) {
		$group =& $childGroups->next();
		$groupId =& $group->getId();
		$childGroupIds[] =& $groupId->getIdString();
	}
}

// Get all the groups first.
$groupHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$groupHeader->addComponent(new Content(_("Groups")));
$actionRows->addComponent($groupHeader);

$groups =& $sharedManager->getGroups();
while ($groups->hasNext()) {
	$group =& $groups->next();
	$groupId =& $group->getId();
	
	if (!in_array($groupId->getIdString(), $childGroupIds)) {
		
		// Create a layout for this group using the GroupPrinter
		ob_start();
		GroupPrinter::printGroup($group, $harmoni,
										2,
										"printGroup", 
										"printMember");
		$groupLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
		$groupLayout->addComponent(new Content(ob_get_contents()));
		ob_end_clean();
		$actionRows->addComponent($groupLayout);	
	}
}


// Agents
$agentHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$agentHeader->addComponent(new Content(_("Users")));
$actionRows->addComponent($agentHeader);

$expandAgents = ((in_array("allagents", $harmoni->pathInfoParts))?TRUE:FALSE);

// Create a layout for this group using the GroupPrinter
ob_start();

print "\n\n<table>\n\t<tr><td valign='top'>";

print <<<END
<div style='
	border: 1px solid #000; 
	width: 15px; 
	height: 15px;
	text-align: center;
	text-decoration: none;
	font-weight: bold;
'>
END;

// Break the path info into parts for the enviroment and parts that
// designate which nodes to expand.
$environmentInfo = array();
$expandedNodes = array();

for ($i=0; $i<count($harmoni->pathInfoParts); $i++) {
	// If the index equals or is after our starting key
	// it designates an expanded nodeId.
	if ($i >= 2)
		$expandedNodes[] = $harmoni->pathInfoParts[$i];
	else	
		$environmentInfo[] = $harmoni->pathInfoParts[$i];
}
		
if ($expandAgents) {
	$nodesToRemove = array("allagents");
	$newPathInfo = array_merge($environmentInfo, array_diff($expandedNodes,
															$nodesToRemove)); 
	print "<a style='text-decoration: none;' href='";
	print MYURL."/".implode("/", $newPathInfo)."/";
	print "'>-</a>";
} else {
	$newPathInfo = array_merge($environmentInfo, $expandedNodes); 
	print "<a style='text-decoration: none;' href='";
	print MYURL."/".implode("/", $newPathInfo)."/allagents/";
	print "'>+</a>";
}
print "</div>";
print "\n\t</td><td valign='top'>\n\t\t";
print _("All Agents");
print "\n\t</td></tr>\n</table>";

if ($expandAgents) {
	print <<<END
<div style='
	margin-left: 13px; 
	margin-right: 0px; 
	margin-top:0px; 
	padding-left: 10px;
	border-left: 1px solid #000;
'>
END;
	$agents =& $sharedManager->getAgents();
	while ($agents->hasNext()) {
		$agent =& $agents->next();
		printMember($agent);
		print "<br />";
	}
	print "</div>";
}

$agentLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
$agentLayout->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($agentLayout);	

// Return the main layout.
return $mainScreen;


// Functions used for the GroupPrinter
function printGroup(& $group) {
	$id =& $group->getId();
	$groupType =& $group->getType();
	print "<input type='checkbox' name='user' value='".$id->getIdString()."'>";
	print "<a title='".$groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()." - ".$groupType->getDescription()."'>";
	print "<u><strong>".$id->getIdString()." - ".$group->getDisplayName()."</strong></u></a>";	
	print " - <em>".$group->getDescription()."</em>";
}

function printMember(& $member) {
	$id =& $member->getId();
	$memberType =& $member->getType();
	print "<input type='checkbox' name='user' value='".$id->getIdString()."'>";
	print "<a title='".$memberType->getDomain()." :: ".$memberType->getAuthority()." :: ".$memberType->getKeyword()." - ".$memberType->getDescription()."'>";
	print "<u>".$id->getIdString()." - ".$member->getDisplayName()."</u></a>";
}