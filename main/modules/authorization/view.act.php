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
$introHeader->addComponent(new Content(_("View Authorizations")));
$actionRows->addComponent($introHeader);

$authZManager =& Services::getService("AuthZ");

// Get all hierarchies and their root qualifiers
$hierarchyIds =& $authZManager->getQualifierHierarchies();
while ($hierarchyIds->hasNext()) {
	$hierarchyId =& $hierarchyIds->next();
	
	// Get the root qualifiers for the Hierarchy
	$qualifiers =& $authZManager->getRootQualifiers($hierarchyId);
	while ($qualifiers->hasNext()) {
		$qualifier =& $qualifiers->next();
		
		// Create a layout for this qualifier
		ob_start();
		HierarchyPrinter::printNode($qualifier, $harmoni,
										2,
										"printQualifier", 
										"hasChildQualifiers", 
										"getChildQualifiers");
		$qualifierLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
		$qualifierLayout->addComponent(new Content(ob_get_contents()));
		ob_end_clean();
		$actionRows->addComponent($qualifierLayout);
	}
}

// return the main layout.
return $mainScreen;


// Qualifier printing functions:
function printQualifier(& $qualifier) {
	print "<strong>".$qualifier->getDisplayName()."</strong>\n ";
//	print "<br/ >".$qualifier->getDescription()."\n<br />\n";
}

function hasChildQualifiers(& $qualifier) {
	return $qualifier->isParent();
}

function getChildQualifiers(& $qualifier) {
	$array = array();
	$iterator =& $qualifier->getChildren();
	while ($iterator->hasNext()) {
		$array[] =& $iterator->next();
	}
	return $array;
}