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

// Intro
$idManager =& Services::getService("Id");
$authZManager =& Services::getService("AuthZ");
$GLOBALS["harmoni"] =& $harmoni;

// Layout
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);


// Intro message
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
 $introHeader->addComponent(new Content(_("Browse Authorizations")));
 											
$intro =& new Content("&nbsp &nbsp "._("Below is a listing of all of the Users/Groups who are authorized to do various functions in the system. Click on a name to edit the authorizations for that User/Group")."<br /><br />");

 $actionRows->addComponent($introHeader);
 $actionRows->addComponent($intro);
 
// Buttons to go back to edit auths for a different user, or to go home
ob_start();
print "<table width='100%'><tr><td align='left'>";
// print "<a href='".MYURL."/authorization/choose_agent'><button><-- "._("Choose a different User/Group to edit")."</button></a>";
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

	print "\n<a title='$title'><strong>".$qualifier->getReferenceName()."</strong></a>";

	// Check that the current user is authorized to see the authorizations.
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	if ($authZ->isUserAuthorized(
				$idManager->getId(AZ_VIEW_AZS),
				$id))
	{
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
	$harmoni =& $GLOBALS["harmoni"];
	$authZManager =& Services::getService("AuthZ");
	$agentManager =& Services::getService("Agent");
	
	$expandedNodes = array_slice($harmoni->pathInfoParts, 2);
	if (count ($expandedNodes)) 
		$additionalPathInfo = implode("/", $expandedNodes)."/";
	else
		$additionalPathInfo = "";
	
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
			
			print "\n\t<td style='border: 1px solid #000; border-right: 0px solid #000; font-weight: bold' align='right' valign='top'>";
			print "\n\t<span style='white-space: nowrap'>".$function->getReferenceName().":</span>";
			print "\n\t</td>";
			
			print "\n\t<td style='border: 1px solid #000; border-left: 0px solid #000;' valign='top'>";
			
			$agentsThatCanDo =& $authZManager->getWhoCanDo($functionId, $qualifierId, TRUE);
			while ($agentsThatCanDo->hasNext()) {
				$agentId =& $agentsThatCanDo->next();
								
				print "<span style='white-space: nowrap'>";
				
				print "<a href='".MYURL."/authorization/edit_authorizations/";
				print $additionalPathInfo;
				print "?agent=".$agentId->getIdString();
				print "' title='Edit Authorizations for this User/Group'>";
				
				if ($agentManager->isAgent($agentId)) {
					$agent =& $agentManager->getAgent($agentId);
					print $agent->getDisplayName();
				} else if ($agentManager->isGroup($agentId)) {
					$group =& $agentManager->getGroup($agentId);
					print $group->getDisplayName();
				} else {
					print "Agent/Group Id ".$agentId->getIdString()."";
				}
				print "</a>";
				
				if ($agentsThatCanDo->hasNext())
					print ", ";
				print "</span>";
			}

			print "\n\t</td>";
			
			// If we are up to six and we have more, start a new row.
			if ($numFunctions % 3 == 0 && $functions->hasNext())
				print "\n</tr>\n<tr>\n\t<th>\n\t\t&nbsp;\n\t</th>";
		}
		print "\n</tr>";
	}
	print"\n</table>";

}

/** Sort the AZs

*/







?>