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

// Layout
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$sharedManager =& Services::getService("Shared");
$authZManager =& Services::getService("AuthZ");

// Intro message
$intro =new Content("&nbsp &nbsp Check or uncheck authorization(s) for the section(s) of your choice.<br />
			&nbsp &nbsp After each check/uncheck, the changes are saved automatically.<br /><br />");


// Get the id and type (group/member) of the selected agent using $_REQUEST
 $selection =& $_REQUEST["selection"];
 $pieces =& explode(":", $selection);
 $groupOrMember = $pieces[0];
 $id = $pieces[1];
 $idObject =& $sharedManager->getId($id);
 $GLOBALS["agentId"] =& $idObject;
 $GLOBALS["harmoni"] =& $harmoni;


 if ($groupOrMember == "group") {
 	$agent =& $sharedManager->getGroup($idObject);
 	$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
 	$introHeader->addComponent(new Content(_("Edit Which Authorizations for Group: <em> "
 											.$agentId.$agent->getDisplayName()."</em>?")));
 } else {
 	$agent =& $sharedManager->getAgent($idObject);
 	$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
 	$introHeader->addComponent(new Content(_("Edit Which Authorizations for User: <em> "
 											.$agentId.$agent->getDisplayName()."</em>?")));
 }

 $actionRows->addComponent($introHeader);
 $actionRows->addComponent($intro);
 
// Buttons to go back to edit auths for a different user, or to go home
ob_start();
print "<table><tr><td>";
print "<a href='".MYURL."/authorization/choose_agent'><button>Choose a different Group/Member to edit</button></a></td>";
print "<td><a href='".MYURL."'><button>Return to Concerto Home</button></a></td></tr></table>";
$nav = new Content(ob_get_contents());
$actionRows->addComponent($nav, MIDDLE, LEFT);
ob_end_clean();
 

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


	}
}
print"</table>";

// Buttons to go back to edit auths for a different user, or to go home
ob_start();
print "<table><tr><td>";
print "<a href='".MYURL."/authorization/choose_agent'><button>Choose a different Group/Member to edit</button></a></td>";
print "<td><a href='".MYURL."'><button>Return to Concerto Home</button></a></td></tr></table>";
$nav = new Content(ob_get_contents());
$actionRows->addComponent($nav, MIDDLE, LEFT);
ob_end_clean();

return $mainScreen;





// Qualifier printing functions:
function printQualifier(& $qualifier) {

	print "<table><tr>"; // Each table row will consist of the displayName and a table with edit options
	print "<td valign='top'><strong>".$qualifier->getDisplayName()."</strong></td>";
	print "<td>";
	printEditOptions($qualifier);
	print "</td></tr></table><br />";
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
function printEditOptions(& $qualifier) {
	$qualifierId =& $qualifier->getId();
	$agentId =& $GLOBALS["agentId"];
	$harmoni =& $GLOBALS["harmoni"];
	$authZManager =& Services::getService("AuthZ");
	
	$functionTypes =& $authZManager->getFunctionTypes();
	print "\n<table><tr>";
	while ($functionTypes->hasNext()) {
		print "\n\t<td><table>";
		$functionType =& $functionTypes->next();
		$functions =& $authZManager->getFunctions($functionType);
		while ($functions->hasNext()) {
			$function =& $functions->next();
			$functionId =& $function->getId();

			// IF an authorization exists for the user on this qualifier, 
			// make checkbox already checked
			if ($authZManager->isAuthorized($agentId, $functionId, $qualifierId)) {
				$toggleOperation = "delete";
				print "<tr><td>";
				print "<table border='2' bordercolor='green'><tr>";
				print "<td><input type='checkbox' name='blah' value='blah'";
				print " checked='checked'";

			} else {
				$toggleOperation = "create";
				print "<tr><td>";
				print "<table border='2' bordercolor='red'><tr>";
				print "<td><input type='checkbox' name='blah' value='blah'";
			}

			// The checkbox is really just for show, the link is where we send
			// to our processing to toggle the state of the authorization.
			$toggleURL = MYURL."/authorization/process_authorizations/"
				.$toggleOperation."/".$agentId->getIdString()."/"
				.$functionId->getIdString()."/".$qualifierId->getIdString()
				."/".implode("/", $harmoni->pathInfoParts)
				."?selection=".$_GET['selection'];

			print "onClick=\"Javascript:window.location='".$toggleURL."'\">";

			print "<td>".$function->getReferenceName()."</td></tr></table></td></tr>";

		}
		print "\n\t</table></td>";
		
	}
	print"\n\t</tr></table>";
	

}









?>
