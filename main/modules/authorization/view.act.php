<?
/**
 * @package polyphony.modules.authorization
 */

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);
$centerPane->add($actionRows, null, null, CENTER, CENTER);

// Intro
$introHeader =& new Heading("View Authorizations", 2);
$actionRows->addComponent($introHeader, "100%", null, LEFT, CENTER);

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
		$qualifierLayout =& new Block(ob_get_contents(), 4);
		ob_end_clean();
		$actionRows->add($qualifierLayout, "100%", null, LEFT, CENTER);
	}
}

// return the main layout.
return $mainScreen;


/**
 * Callback function for printing a qualifier.  
 * To be used for each qualifier in the hierarchy.
 * 
 * @param object Qualifier $qualifier
 * @return void
 * @access public
 * @ignore
 */
function printQualifier(& $qualifier) {
	print "<strong>".$qualifier->getDisplayName()."</strong>\n ";
//	print "<br/ >".$qualifier->getDescription()."\n<br />\n";
}

/**
 * Callback function for determining if a qualifier has children.
 * 
 * @param object Qualifier $qualifier
 * @return boolean
 * @access public
 * @ignore
 */
function hasChildQualifiers(& $qualifier) {
	return $qualifier->isParent();
}

/**
 * Callback function for fetching the children of a qualifier.
 * 
 * @param object Qualifier $qualifier
 * @return array
 * @access public
 * @ignore
 */
function getChildQualifiers(& $qualifier) {
	$array = array();
	$iterator =& $qualifier->getChildren();
	while ($iterator->hasNext()) {
		$array[] =& $iterator->next();
	}
	return $array;
}