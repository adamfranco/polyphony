<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("View Agents")));
$actionRows->addComponent($introHeader);

$agentManager =& Services::getService("Agent");

// $testType =& new HarmoniType("AuthorityX", "domainX", "KeywordX", "This is a really great description");
// $sharedManager->createAgent("That guy in camo", $testType);


// $sharedManager =& Services::getService("Shared");
// 
// $qualifierHierarchyId =& $sharedManager->getId("673");
// $functionId =& $sharedManager->createId();
// $functionType =& new HarmoniType("Concerto", "Midd", "Use", "Functions for viewing and using shiznat.");
// $authZManager->createFunction($functionId, "Comment", "Add comments to this thing.", $functionType, $qualifierHierarchyId);
// exit;



// Get all the agents.
$agents =& $agentManager->getAgents();
while ($agents->hasNext()) {
	$agent =& $agents->next();
	ob_start();
	$id =& $agent->getId();
	print "<strong>".$id->getIdString()." - ";
	print $agent->getDisplayName()."</strong>";
	$agentLayout =& new SingleContentLayout(HEADING_WIDGET, 2);
	$agentLayout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	
	// Also display type information for that agent
	
	ob_start();
	$agentType =& $agent->getType();
	print "<em>Authority: </em>".$agentType->getAuthority();
	print "<em><br />Domain: </em>".$agentType->getDomain();
	print "<em><br />Keyword: </em>".$agentType->getKeyword();
	$agentInfoLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
	$agentInfoLayout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	
	ob_start();
	print "<em>Description: </em>".$agentType->getDescription();
	$agentDescriptionLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 1);
	$agentDescriptionLayout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
		
	$actionRows->addComponent($agentLayout);
	$actionRows->addcomponent($agentInfoLayout);
	$actionRows->addComponent($agentDescriptionLayout);
	
	$actionRows->addComponent(new Content(" &nbsp; <br /> &nbsp; <br /> &nbsp;"));
	
/// / 	Now get all the functions of the specified type
// 	$functions =& $authZManager->getFunctions($functionType);
// 	while ($functions->hasNext()) {
// 		$function =& $functions->next();
// 		
// 	//	Store function info and create layouts.
// 	//	Types consist of {domain :: authority :: type :: description}
// 	//	Functions consist of {Id :: displayName :: description}
// 		
// 		ob_start();
// 		$id =& $function->getId();
// 		print $id->getIdString()." - ";
// 		print "<strong>".$function->getReferenceName()." - ";
// 		print "</strong>".$function->getDescription();
// 		
// 		$functionLayout->addComponent(new Content(ob_get_contents()));
// 		ob_end_clean();
//         
// 	}
}



// Return the main layout.
return $mainScreen;