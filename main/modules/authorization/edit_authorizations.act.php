<?

/**
* edit_authorizations.act.php
* This file will allow the user to edit authorizations for a given user.
* The chosen user information will have been passed from choose_agents.act.php via FORM action.
* 11/11/04 Ryan Richards
* copyright 2004 Middlebury College
*/


// Get the Layout components. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');

// Our
$actionRows =& new RowLayout();


$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$sharedManager =& Services::getService("Shared");
$authZManager =& Services::getService("AuthZ");

// In order to preserve proper nesting on the HTML output
$actionRows->setPreSurroundingText("<form method='post' action='".MYURL."/".implode("/", $harmoni->pathInfoParts)."'>");
$actionRows->setPostSurroundingText("</form>");
printpre ($harmoni->pathInfoParts);

/** NOTE: $selection is empty after attempting to collapse/expand the hierarchy.  Need to fix this as an error results.
 * Display which user/group's authorizations are being edited.
 *$selection =& $_REQUEST["selection"];
 *$pieces =& explode(":", $selection);
 *$groupOrMember =& $pieces[0];
 *$id =& $pieces[1];
 *$idObject =& $sharedManager->getId($id);
 *
 *if ($groupOrMember == "group") {
 *	$agent =& $sharedManager->getGroup($idObject);
 *	$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
 *	$introHeader->addComponent(new Content(_("Edit Which Authorizations for Group: <em> "
 *											.$agentId.$agent->getDisplayName()."</em>?")));
 *} else {
 *	$agent =& $sharedManager->getAgent($idObject);
 *	$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
 *	$introHeader->addComponent(new Content(_("Edit Which Authorizations for User: <em> "
 *											.$agentId.$agent->getDisplayName()."</em>?")));
 *}
 *
 *$actionRows->addComponent($introHeader);
 */


// Get all hierarchies and their root qualifiers
print "<table>";
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

		//Print an HTML table of authorizations for the current qualifier.
		// Each authorization type will be its own table column.
		// Each item in the table will have a checkbox to allow editing.  Save on each click (change).

// 		$functionTypes =& $authZManager->getFunctionTypes();
// 		ob_start();
// 		print "<table><tr>";
// 		while ($functionTypes->hasNext()) {
// 		  print "<td><table>";
// 		  $functionType =& $functionTypes->next();
// 		  $functions =& $authZManager->getFunctions($functionType);
// 		  while ($functions->hasNext()) {
// 		    print"<tr><td>";
// 		    $function =& $functions->next();
// 		    $id =& $function->getId();
// 		    //print $id->getIdString()." - ";
// 		    print $function->getReferenceName()."</td></tr>";
// 
// 		  }
// 		  print "</table></td>";
// 
// 		} // end outer while
//                 print" </tr></table>";
//                 $functionLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
//                 $functionLayout->addComponent(new Content(ob_get_contents()));
//                 ob_end_clean();
//                 $actionRows->addComponent($functionLayout);
	}
}
print"</table>";
return $mainScreen;




// TO DO: Alter to include checkboxes for authorizations next to EACH qualifier.
//			Use a table and JS to save changes after each click.

// Qualifier printing functions:
function printQualifier(& $qualifier) {

	print "<table><tr>"; // Each table row will consist of the displayName and a table with edit options
	print "<td valign='top'><strong>".$qualifier->getDisplayName()."</strong></td>";
	print "<td>";
	printEditOptions();
	print "</td></tr></table><br />";
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

// Prints a table of all functions.  To be used for each qualifier in the hierarchy.
function printEditOptions(& $functionTypes) {
	$authZManager =& Services::getService("AuthZ");
	$functionTypes =& $authZManager->getFunctionTypes();
	//ob_start();
	print "<table><tr>";
	while ($functionTypes->hasNext()) {
	  print "<td><table>";
	  $functionType =& $functionTypes->next();
	  $functions =& $authZManager->getFunctions($functionType);
	  while ($functions->hasNext()) {
	  	$function =& $functions->next();
	  	$id =& $function->getId();
		
		// IF an authorization exists for the user on this qualifier, make checkbox already checked
		//  Remember to actually create or remove authorization tripletts!!!

	    print "<tr><td>";
	    print "<input type='checkbox' name='authOption' value='".$id->getIdString()."'";
	    print " onClick='Javascript:submit()'>";

	    print $function->getReferenceName()."</td></tr>";
	  }
	  print "</table></td>";

	}
 	print" </tr></table>";
 //               $functionLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
   //             $functionLayout->addComponent(new Content(ob_get_contents()));
     //           ob_end_clean();
       //         $actionRows->addComponent($functionLayout);

}

/**(16:46:09) Adam Franco: yep, just make sure that you keep all the path info (that makes up the list of expanded nodes) at the end of the form action.
(16:50:14) Adam Franco: something like:

$actionRows->setPreSurroundingText("<form method='post' action='".MYURL."/authorization/edit_authorizations/".implode("/", $harmoni->pathInfoParts)."'>");
*/









?>
