<?

/**
* edit_authorizations.act.php
* This file will allow the user to edit authorizations for a given user.
* The chosen user information will have been passed from choose_agents.act.php via FORM action.
* 11/11/04 Ryan Richards
* copyright 2004 Middlebury College
*/

// Check for our authorization function definitions
if (!defined("AZ_VIEW_AZS"))
	throwError(new Error("You must define an id for AZ_VIEW_AZS", "polyphony.authorizations", true));
if (!defined("AZ_MODIFY_AZS"))
	throwError(new Error("You must define an id for AZ_MODIFY_AZS", "polyphony.authorizations", true));


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
$idManager =& Services::getService("Id");
$agentManager =& Services::getService("Agent");

$authZManager =& Services::getService("AuthZ");

// Intro message
$intro =& new Content("&nbsp; &nbsp; "._("Check or uncheck authorization(s) for the section(s) of your choice.")."<br />
		&nbsp; &nbsp; "._("After each check/uncheck, the changes are saved automatically.")."<br /><br />");


// Get the id of the selected agent using $_REQUEST
$id = $_REQUEST["agent"];
$idObject =& $idManager->getId($id);
$GLOBALS["agentId"] =& $idObject;
$GLOBALS["harmoniAuthType"] =& new HarmoniAuthenticationType;
$GLOBALS["harmoni"] =& $harmoni;


if ($agentManager->isGroup($idObject)) {
$agent =& $agentManager->getGroup($idObject);
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Edit Which Authorizations for Group").": <em> "
										.$agent->getDisplayName()."</em>?"));
} else if ($agentManager->isAgent($idObject)) {
$agent =& $agentManager->getAgent($idObject);
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
print "<a href='".MYURL."/authorization/choose_agent'><button>&lt;-- "._("Choose a different User/Group to edit")."</button></a>";
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

	print "\n<a title='".htmlentities($title, ENT_QUOTES)."'><strong>".htmlentities($qualifier->getReferenceName(), ENT_QUOTES)."</strong></a>";
	
	// Check that the current user is authorized to see the authorizations.
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	$authN =& Services::getService("AuthN");
	$agentId =& $GLOBALS["agentId"];
	$harmoniAuthType =& $GLOBALS["harmoniAuthType"];
	// They are authorized if they have explicit authorization,
	// or if they are looking at their own authorizations,
	// or if they are looking at one of their groups' authorizations.
	if ($authZ->isUserAuthorized(
				$idManager->getId(AZ_VIEW_AZS),
				$id)
		|| $agentId->isEqual($authN->getUserId($harmoniAuthType))
	) {
		print "\n<div style='margin-left: 10px;'>";
		printEditOptions($qualifier);
		print "\n</div>";
	}
	// If they are not authorized to view the AZs, notify
	else {
		print " <em>"._("You are not authorized to view authorizations here.")."<em>";
	}
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
	$agentManager =& Services::getService("Agent");
	
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
		print " title='".htmlentities($title, ENT_QUOTES)."'";
		print " href=\"Javascript:window.alert('".htmlentities($title, ENT_QUOTES)."')\"";
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
					if ($agentManager->isAgent($explicitAgentId)) {
						$explicitAgent =& $agentManager->getAgent($explicitAgentId);
						$title = _("User").": ".$explicitAgent->getDisplayName();
					} else if ($agentManager->isGroup($explicitAgentId)) {
						$explicitGroup =& $agentManager->getGroup($explicitAgentId);
						$title = _("Group").": ".$explicitGroup->getDisplayName();
					} else {
						$title = _("User/Group").": ".$explicitAgentId->getIdString();
					}
					$title .= ", "._("Location").": ".$explicitQualifier->getReferenceName();
					if ($explicitAZs->hasNext())
						$title .= "; ";
				}
				
				// print out a checkbox for the implicit AZ
				print "\n\t\t\t\t\t<td><span style='white-space: nowrap'>";
				print "\n\t\t\t\t\t\t<input type='checkbox' name='blah' value='blah'";
				print " title='".htmlentities($title, ENT_QUOTES)."'";
				print " checked='checked' disabled='disabled' />";
				print "\n\t\t\t\t\t\t<a";
// 				print " id='".$explicitAgentId->getIdString()
// 						."-".$functionId->getIdString()
// 						."-".$explicitQualifierId->getIdString()."'";
				print " title='".htmlentities($title, ENT_QUOTES)."'";
 				print " href=\"Javascript:window.alert('".htmlentities($title, ENT_QUOTES)."')\"";
				print ">?</a>";
				print "\n\t\t\t\t\t</span></td>";
			}
			
			print "\n\t\t\t\t\t<td>";
			// print an extra space
			if (count($implicitAZs))
				print "&nbsp;";
				
			print "\n\t\t\t\t\t\t<input type='checkbox' name='blah' value='blah' ";
			print $explicitChecked;
			
			// Check that the current user is authorized to modify the authorizations.
			$authZ =& Services::getService("AuthZ");
			$idManager =& Services::getService("Id");
			if ($authZ->isUserAuthorized(
						$idManager->getId(AZ_MODIFY_AZS),
						$qualifierId))
			{
				// The checkbox is really just for show, the link is where we send
				// to our processing to toggle the state of the authorization.
				$toggleURL = MYURL."/authorization/process_authorizations/"
					.$toggleOperation."/".$agentId->getIdString()."/"
					.$functionId->getIdString()."/".$qualifierId->getIdString()
					."/".implode("/", $harmoni->pathInfoParts)
					."?agent=".$_GET['agent'];
	
				print " onclick=\"Javascript:window.location='".htmlentities($toggleURL, ENT_QUOTES)."'\"";
			}
			// If they are not authorized to view the AZs, disable the checkbox
			else {
				print " disabled='disabled'";
			}
			print " /></td>";

			print "\n\t\t\t\t\t<td><span style='white-space: nowrap'>".htmlentities($function->getReferenceName())."</span></td>";
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
