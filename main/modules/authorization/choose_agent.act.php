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
$pageRows =& new RowLayout();
$actionRows =& new RowLayout();

// In order to preserve proper nesting on the HTML output put the form
// around the row layout
ob_start();

$errorString = _("You must select a User or Group.");
print<<<END

<script type='text/javascript'>

	// Make sure a selection has been made and submit if it has.
	function submitAgentChoice() {
		var radioArray = document.chooseform.agent;
		var isChecked = false;
		
		for (i=0; i<radioArray.length; i++) {
			if (radioArray[i].checked) {
				isChecked = true;
			}
		}
		
		if (isChecked) {
			document.chooseform.submit();
		} else {
			alert("$errorString");
		}
	}

</script>

END;
print "<form name='chooseform' id='chooseform' method='get' action='".MYURL."/authorization/edit_authorizations/'>";

$actionRows->setPreSurroundingText(ob_get_contents());
ob_end_clean();
$actionRows->setPostSurroundingText("</form>");

$centerPane->addComponent($pageRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Edit Authorizations for which User/Group?")));
$pageRows->addComponent($introHeader);

ob_start();
print "<table width='100%'><tr><td align='left'>";
print "<a href='".MYURL."/admin/main'><button>"._("Return to the Admin Tools")."</button></a>";
print "</td><td align='right'>";
print "<input type='button'";
print " onclick='Javascript:submitAgentChoice()'";
print " value='"._("Edit Authorizations for the selected User/Group")." -->'>";
print "</td></tr></table>";

$submit = new Content(ob_get_contents());
ob_end_clean();
$pageRows->addComponent($submit, MIDDLE);

$sharedManager =& Services::getService("Shared");



// Users header
$agentHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$agentHeader->addComponent(new Content(_("Users")));
$pageRows->addComponent($agentHeader);

/*********************************************************
 * the agent search form
 *********************************************************/
ob_start();

$self = $_SERVER['PHP_SELF'];
$lastCriteria = $_REQUEST['search_criteria'];
print _("Search For Users").": ";
print <<<END
<form action='$self' method='get'>
	<input type='text' name='search_criteria' value='$lastCriteria' />
	<br /><select name='search_type'>
END;

$searchTypes =& $sharedManager->getAgentSearchTypes();
while ($searchTypes->hasNext()) {
	$type =& $searchTypes->next();
	$typeString = $type->getDomain()
						."::".$type->getAuthority()
						."::".$type->getKeyword();
	print "\n\t\t<option value='$typeString'";
	if ($_REQUEST['search_type'] == $typeString)
		print " selected='selected'";
	print ">$typeString</option>";
}

	print "\n\t</select>";
	print "\n\t<br /><input type='submit' value='"._("Search")."' />";
	print "\n\t<a href='".MYURL."/".implode("/", $harmoni->pathInfoParts)."/'>";
	print "<input type='button' value='"._("Clear")."' /></a>";
print "</form>";

$agentLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$agentLayout->addComponent(new Content(ob_get_contents()), BOTTOM);
ob_end_clean();
$pageRows->addComponent($agentLayout);
$pageRows->addComponent($actionRows, BOTTOM, CENTER);

/*********************************************************
 * the agent search results
 *********************************************************/
 
if ($_REQUEST['search_criteria'] && $_REQUEST['search_type']) {
	$typeParts = explode("::", $_REQUEST['search_type']);
	$searchType =& new HarmoniType($typeParts[0], $typeParts[1], $typeParts[2]);
	$agents =& $sharedManager->getAgentsBySearch($_REQUEST['search_criteria'], $searchType);
	
	print <<<END


<table>
	<tr>
		<td valign='top'>
			<div style='
				border: 1px solid #000; 
				width: 15px; 
				height: 15px;
				text-align: center;
				text-decoration: none;
				font-weight: bold;
			'>
				-
			</div>
		</td>
		<td>
END;
	print "\n\t\t\t"._("Search Results");
	print <<<END
		</td>
	</tr>
</table>
<div style='
	margin-left: 13px; 
	margin-right: 0px; 
	margin-top:0px; 
	padding-left: 10px;
	border-left: 1px solid #000;
'>
END;
	while ($agents->hasNext()) {
		$agent =& $agents->next();
		printMember($agent);
		print "<br />";
	}
	print "\n</div>";
	
	$agentLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$agentLayout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	$actionRows->addComponent($agentLayout);	
}


/*********************************************************
 * Groups
 *********************************************************/

// Users header
$agentHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$agentHeader->addComponent(new Content(_("Groups")));
$actionRows->addComponent($agentHeader);

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
$groups =& $sharedManager->getGroups();  // Groups ARE agents
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
$actionRows->addComponent($submit, MIDDLE);


// Return the main layout.
return $mainScreen;


// Functions used for the GroupPrinter

function printGroup(& $group) {
	$id =& $group->getId();
	$groupType =& $group->getType();
	print "<input type='radio' name='agent' value='".$id->getIdString()."' />";
	print "<a title='".$groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()."'>";
	print "<span style='text-decoration: underline; font-weight: bold;'>".$id->getIdString()." - ".$group->getDisplayName()."</span></a>";	
	print " - <em>".$groupType->getDescription()."</em>";
}

function printMember(& $member) {
	$id =& $member->getId();
	$memberType =& $member->getType();
	print "<input type='radio' name='agent' value='".$id->getIdString()."' />";
	print "<a title='".$memberType->getAuthority()." :: ".$memberType->getDomain()." :: ".$memberType->getKeyword()."'>";
	print "<span style='text-decoration: underline;'>".$id->getIdString()." - ".$member->getDisplayName()."</span></a>";
	print " - <em>".$memberType->getDescription()."</em>";
}

