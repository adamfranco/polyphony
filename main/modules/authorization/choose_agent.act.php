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
$actionRows->setPreSurroundingText("<form method='post' action='http://devo/rrichards/concerto/index.php/authorization/edit_authorizations.act.php'>");
$actionRows->setPostSurroundingText("</form>");

$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Edit Authorizations for which Group/User?")));
$actionRows->addComponent($introHeader);

$submit = new Content("<input type='submit' value='"._("Edit Authorizations for the selected Group/User")." ---->'>");
$actionRows->addComponent($submit, MIDDLE, RIGHT);

$sharedManager =& Services::getService("Shared");
//
//$id =& $sharedManager->getId("181");
//$agent =& $sharedManager->getAgent($id);
//$id =& $sharedManager->getId("190");
//$group =& $sharedManager->getGroup($id);
//
//$group->add($agent);
//exit;
////
//$id =& $sharedManager->getId("182");
//$agent =& $sharedManager->getAgent($id);
//$id =& $sharedManager->getId("190");
//$group =& $sharedManager->getGroup($id);
//
//$group->add($agent);


 //$testType1 =& new HarmoniType("Groups", "Middlebury College", "User Status", "Status of the user at Middlebury College");
// $testType2 =& new HarmoniType("Groups", "Middlebury College", "Department", "What department the user belongs to at Middlebury College");
// $sharedManager->createGroup("Student", $testType1, "Middlebury College Student");
// $sharedManager->createGroup("English", $testType2, "Middlebury College English Department");



// $sharedManager =& Services::getService("Shared");
// 
// $qualifierHierarchyId =& $sharedManager->getId("673");
// $functionId =& $sharedManager->createId();
// $functionType =& new HarmoniType("Concerto", "Midd", "Use", "Functions for viewing and using shiznat.");
// $authZManager->createFunction($functionId, "Comment", "Add comments to this thing.", $functionType, $qualifierHierarchyId);
// exit;


// Get all the groups first.
$groups =& $sharedManager->getGroups();  // Groups ARE agents
while ($groups->hasNext()) {
	$group =& $groups->next();
	
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
$actionRows->addComponent($submit, MIDDLE, RIGHT);

// Return the main layout.
return $mainScreen;


// Functions used for the GroupPrinter

function printGroup(& $group) {
	$id =& $group->getId();
	$groupType =& $group->getType();
	print "<input type='radio' name='user' value='".$id->getIdString()."'>";
	print "<a title='".$groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()."'>";
	print "<u><strong>".$id->getIdString()." - ".$group->getDisplayName()."</strong></u></a>";	
	print " - <em>".$groupType->getDescription()."</em>";
}

function printMember(& $member) {
	$id =& $member->getId();
	$memberType =& $member->getType();
	print "<input type='radio' name='user' value='".$id->getIdString()."'>";
	print "<a title='".$memberType->getAuthority()." :: ".$memberType->getDomain()." :: ".$memberType->getKeyword()."'>";
	print "<u>".$id->getIdString()." - ".$member->getDisplayName()."</u></a>";
	print " - <em>".$memberType->getDescription()."</em>";
}

