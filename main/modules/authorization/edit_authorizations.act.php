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
$intro =& new Content("&nbsp &nbsp "._("Check or uncheck authorization(s) for the section(s) of your choice.")."<br />
			&nbsp &nbsp "._("After each check/uncheck, the changes are saved automatically.")."<br /><br />");


// Get the id of the selected agent using $_REQUEST
 $id = $_REQUEST["agent"];
 $idObject =& $sharedManager->getId($id);
 $GLOBALS["agentId"] =& $idObject;
 $GLOBALS["harmoni"] =& $harmoni;


 if ($sharedManager->isGroup($idObject)) {
 	$agent =& $sharedManager->getGroup($idObject);
 	$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
 	$introHeader->addComponent(new Content(_("Edit Which Authorizations for Group").": <em> "
 											.$agent->getDisplayName()."</em>?"));
 } else if ($sharedManager->isAgent($idObject)) {
 	$agent =& $sharedManager->getAgent($idObject);
 	$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
 	$introHeader->addComponent(new Content(_("Edit Which Authorizations for User").": <em> "
 											.$agent->getDisplayName()."</em>?"));
 } else {
 	$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
 	$introHeader->addComponent(new Content(_("Edit Which Authorizations for the User/Group Id").": <em> "
 											.$idObject->getIdString()."</em>?"));
 }

 $actionRows->addComponent($introHeader);
 $actionRows->addComponent($intro);
 
// Buttons to go back to edit auths for a different user, or to go home
ob_start();
print "<table width='100%'><tr><td align='left'>";
print "<a href='".MYURL."/authorization/choose_agent'><button><-- "._("Choose a different User/Group to edit")."</button></a>";
print "</td><td align='right'>";
print "<a href='".MYURL."/admin/main'><button>"._("Return to the Admin Tools")."</button></a>";
print "</td></tr></table>";

$nav =& new Content(ob_get_contents());
$actionRows->addComponent($nav, MIDDLE);
ob_end_clean();

// Get all hierarchies and their root qualifiers
$hierarchyIds =& $authZManager->getQualifierHierarchies();
$hierarchyManager =& Services::getService("Hierarchy");
while ($hierarchyIds->hasNext()) {
	$hierarchyId =& $hierarchyIds->next();
	
	$hierarchy =& $hierarchyManager->getHierarchy($hierarchyId);
	$header =& new SingleContentLayout(HEADING_WIDGET, 2);
	$header->addComponent(new Content($hierarchy->getDisplayName()." - <em>".$hierarchy->getDescription()."</em>"));
	$actionRows->addComponent($header);

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
										"getChildQualifiers",
										new HTMLColor("#ddd")
									);
		$qualifierLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
		$qualifierLayout->addComponent(new Content(ob_get_contents()));
		ob_end_clean();
		$actionRows->addComponent($qualifierLayout);


	}
}

// Buttons to go back to edit auths for a different user, or to go home
$actionRows->addComponent($nav, MIDDLE);

return $mainScreen;





// Qualifier printing functions:
function printQualifier(& $qualifier) {
	$id =& $qualifier->getId();
	$type =& $qualifier->getQualifierType();
	
	$title = _("Id: ").$id->getIdString()." ";
	$title .= _("Type: ").$type->getDomain()."::".$type->getAuthority()."::".$type->getKeyword();

	print "\n<a title='$title'><strong>".$qualifier->getDisplayName()."</strong></a>";
	print "\n<div style='margin-left: 10px;'>";
	printEditOptions($qualifier);
	print "\n</div>";
}

function hasChildQualifiers(& $qualifier) {
	return $qualifier->isParent();
}

function &getChildQualifiers(& $qualifier) {
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
	$shared =& Services::getService("Shared");
	
	$functionTypes =& $authZManager->getFunctionTypes();
	print "\n<table>";
	while ($functionTypes->hasNext()) {
		print "\n<tr>";
		$functionType =& $functionTypes->next();
		$functions =& $authZManager->getFunctions($functionType);
		
		$title = _("Functions for")." ";
		$title .= $functionType->getKeyword();
		
		print "\n\t<th style='margin-bottom: 3px'>";
		print "\n\t\t<a";
		print " title='".$title."'";
		print " href=\"Javascript:window.alert('".$title."')\"";
		print ">?</a>";
		print "\n\t</th>";
		
		$numFunctions = 0;
		while ($functions->hasNext()) {
			$numFunctions++;
			$function =& $functions->next();
			$functionId =& $function->getId();
			
			// IF an authorization exists for the user on this qualifier, 
			// make checkbox already checked
			$hasExplicit = FALSE;
			$implicitAZs = array();
			$allAZs =& $authZManager->getAllAZs($agentId, $functionId, $qualifierId, FALSE);
			while ($allAZs->hasNext()) {
				$az =& $allAZs->next();
				if ($az->isExplicit()) {
					$hasExplicit = TRUE;
				} else {
					$implicitAZs[] =& $az;
				}
			}
			// Store values for display output
			if ($authZManager->isAuthorized($agentId, $functionId, $qualifierId))
				$borderColor = "green";
			else
				$borderColor = "red";


			if ($hasExplicit) {
				$explicitChecked = "checked='checked'";
				$toggleOperation = "delete";
			} else {
				$explicitChecked = "";
				$toggleOperation = "create";
			}
			
			print "\n\t<td style='border: 1px solid ".$borderColor.";' align='right'>";
			print "\n\t\t\t\t<table>";
			print "\n\t\t\t\t<tr>";
			
			// Print out a disabled checkbox for each implicit Auth.
			for ($i=0; $i < count($implicitAZs); $i++) {
				// Built info about the explicit AZs that cause this implictAZ
				$implicitAZ =& $implicitAZs[$i];
 				$explicitAZs =& $authZManager->getExplicitUserAZsForImplicitAZ($implicitAZ);
 				$title = "";
				while ($explicitAZs->hasNext()) {
					$explicitAZ =& $explicitAZs->next();
					$explicitAgentId =& $explicitAZ->getAgentId();
					$explicitQualifier =& $explicitAZ->getQualifier();
					$explicitQualifierId =& $explicitQualifier->getId();
				
					// get the agent/group for the AZ
					if ($shared->isAgent($explicitAgentId)) {
						$explicitAgent =& $shared->getAgent($explicitAgentId);
						$title = _("User").": ".$explicitAgent->getDisplayName();
					} else if ($shared->isGroup($explicitAgentId)) {
						$explicitGroup =& $shared->getGroup($explicitAgentId);
						$title = _("Group").": ".$explicitGroup->getDisplayName();
					} else {
						$title = _("User/Group").": ".$explicitAgentId->getIdString();
					}
					$title .= ", "._("Location").": ".$explicitQualifier->getDisplayName();
					if ($explicitAZs->hasNext())
						$title .= "; ";
				}
				
				// print out a checkbox for the implicit AZ
				print "\n\t\t\t\t\t<td><nobr>";
				print "\n\t\t\t\t\t\t<input type='checkbox' name='blah' value='blah'";
				print " title='".$title."'";
				print " checked='checked' disabled='disabled'>";
				print "\n\t\t\t\t\t\t<a";
// 				print " id='".$explicitAgentId->getIdString()
// 						."-".$functionId->getIdString()
// 						."-".$explicitQualifierId->getIdString()."'";
				print " title='".$title."'";
 				print " href=\"Javascript:window.alert('".$title."')\"";
				print ">?</a>";
				print "\n\t\t\t\t\t</nobr></td>";
			}
			
			print "\n\t\t\t\t\t<td>";
			// print an extra space
			if (count($implicitAZs))
				print "&nbsp;";
				
			print "\n\t\t\t\t\t\t<input type='checkbox' name='blah' value='blah' ";
			print $explicitChecked;

			// The checkbox is really just for show, the link is where we send
			// to our processing to toggle the state of the authorization.
			$toggleURL = MYURL."/authorization/process_authorizations/"
				.$toggleOperation."/".$agentId->getIdString()."/"
				.$functionId->getIdString()."/".$qualifierId->getIdString()
				."/".implode("/", $harmoni->pathInfoParts)
				."?agent=".$_GET['agent'];

			print " onClick=\"Javascript:window.location='".$toggleURL."'\"></td>";

			print "\n\t\t\t\t\t<td><nobr>".$function->getReferenceName()."</nobr></td>";
			print "\n\t\t\t\t</tr>";
			print "\n\t\t\t\t</table>";
			print "\n\t</td>";
			
			// If we are up to six and we have more, start a new row.
			if ($numFunctions % 5 == 0 && $functions->hasNext())
				print "\n</tr>\n<tr>\n\t<th>\n\t\t&nbsp;\n\t</th>";
		}
		print "\n</tr>";
	}
	print"\n</table>";

}

/** Sort the AZs

*/







?>
