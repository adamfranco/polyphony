<?php

/**
 * choose_agent.act.php
 * This file will allow the user to choose an agent for which to edit authorizations.
 * The agents will be listed both by group and by agent.
 * The chosen agent information will be submitted to edit_authorizations.act.php via form action.
 * 11/10/04 Ryan Richards
 * @copyright 2004 MIddlebury College
 * @package polyphony.modules.authorization
 */


// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');


$agentManager =& Services::getService("Agent");
$idManager = Services::getService("Id");
$everyoneId =& $idManager->getId("-1");

// Our
$yLayout =& new YLayout();
$pageRows =& new Container($yLayout, OTHER, 1);
$preActionRows =& new Container($yLayout, OTHER, 1);
$actionRows =& new Container($yLayout, OTHER, 1);
$postActionRows =& new Container($yLayout, OTHER, 1);

// In order to preserve proper nesting on the HTML output put the form
// around the row layout
ob_start();

$errorString = _("You must select a User or Group.");
print<<<END

<script type='text/javascript'>
//<![CDATA[ 

	// Make sure a selection has been made and submit if it has.
	function submitAgentChoice() {
		var f;		
		for (i = 0; i < document.forms.length; i++) {
			f = document.forms[i];			
			if (f.id == 'chooseform') {
				var form = f;
				break;
			}
		}
		
		var radioArray = form.agent;
		var isChecked = false;
		
		for (i=0; i<radioArray.length; i++) {
			if (radioArray[i].checked) {
				isChecked = true;
			}
		}
		
		if (isChecked) {
			form.submit();
		} else {
			alert("$errorString");
		}
	}
	
//]]> 
</script>

END;
print "<form id='chooseform' method='get' action='".MYURL."/authorization/edit_authorizations/'>";

$preActionRows->add(new Block(ob_get_contents(),2), null, null, CENTER, CENTER);
ob_end_clean();
$postActionRows->add(new Block("</form>",2),null,null,CENTER, CENTER);

$centerPane->add($pageRows, null, null, CENTER, TOP);

// Intro
$introHeader =& new Heading("Edit Authorizations for which User/Group?", 2);
$pageRows->add($introHeader, "100%", null, LEFT, CENTER);

ob_start();
print "<table width='100%'><tr><td align='left'>";
print "<a href='".MYURL."/admin/main'><button>"._("Return to the Admin Tools")."</button></a>";
print "</td><td align='right'>";
print "<input type='button'";
print " onclick='Javascript:submitAgentChoice()'";
print " value='"._("Edit Authorizations for the selected User/Group")." --&gt;' />";
print "</td></tr></table>";

$submit = new Block(ob_get_contents(),2);
ob_end_clean();
$pageRows->add($submit, "100%", null, LEFT, CENTER);




// Users header
$agentHeader =& new Heading("Users", 2);
$pageRows->add($agentHeader, "100%", null, LEFT, CENTER);

/*********************************************************
 * the agent search form
 *********************************************************/
ob_start();

$self = $_SERVER['PHP_SELF'];
$lastCriteria = $_REQUEST['search_criteria'];
print _("Search For Users").": ";
print <<<END
<form action='$self' method='get'>
	<div>
	<input type='text' name='search_criteria' value='$lastCriteria' />
	<br /><select name='search_type'>
END;

$searchTypes =& $agentManager->getAgentSearchTypes();
while ($searchTypes->hasNext()) {
	$type =& $searchTypes->next();
	$typeString = $type->getDomain()
						."::".$type->getAuthority()
						."::".$type->getKeyword();
	print "\n\t\t<option value='".htmlentities($typeString, ENT_QUOTES)."'";
	if ($_REQUEST['search_type'] == $typeString)
		print " selected='selected'";
	print ">".htmlentities($typeString)."</option>";
}

	print "\n\t</select>";
	print "\n\t<br /><input type='submit' value='"._("Search")."' />";
	print "\n\t<a href='".MYURL."/".implode("/", $harmoni->pathInfoParts)."/'>";
	print "\n\t\t<input type='button' value='"._("Clear")."' />\n\t</a>";
print "\n</div>\n</form>";

$agentLayout =& new Block(ob_get_contents(), 3);
ob_end_clean();
$pageRows->add($agentLayout, "100%", null, LEFT, CENTER);
$pageRows->add($preActionRows, null, null, CENTER, CENTER);
$pageRows->add($actionRows, "100%", null, LEFT, CENTER);
$pageRows->add($postActionRows, null, null, CENTER, CENTER);
/*********************************************************
 * the agent search results
 *********************************************************/
 
if ($_REQUEST['search_criteria'] && $_REQUEST['search_type']) {
	$typeParts = explode("::", $_REQUEST['search_type']);
	$searchType =& new HarmoniType($typeParts[0], $typeParts[1], $typeParts[2]);
	$agents =& $agentManager->getAgentsBySearch($_REQUEST['search_criteria'], $searchType);
	
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
	
	$agentLayout =& new Block(ob_get_contents(), 2);
	ob_end_clean();
	$actionRows->add($agentLayout, "100%", null, LEFT, CENTER);	
}


/*********************************************************
 * Groups
 *********************************************************/

// Users header
$agentHeader =& new Heading("Groups", 2);
$actionRows->add($agentHeader, "100%", null, LEFT, CENTER);

// Loop through all of the Groups and figure out which ones are childen of
// other groups, so that we can just display the root-groups
$childGroupIds = array();
$groups =& $agentManager->getGroups();
while ($groups->hasNext()) {
	$group =& $groups->next();
	if (!$everyoneId->isEqual($group->getId())) {
		$childGroups =& $group->getGroups(FALSE);
		while ($childGroups->hasNext()) {
			$group =& $childGroups->next();
			$groupId =& $group->getId();
			$childGroupIds[] =& $groupId->getIdString();
		}
	}
}

// Get all the groups first.
$groups =& $agentManager->getGroups();  // Groups ARE agents
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
		$groupLayout =& new Block(ob_get_contents(), 4);
		ob_end_clean();
		$actionRows->add($groupLayout, "100%", null, LEFT, CENTER);	
	}
}
$actionRows->add($submit, "100%", null, LEFT, CENTER);


// Return the main layout.
return $mainScreen;


// Functions used for the GroupPrinter
/**
 * Callback function for printing a group
 * 
 * @param object Group $group
 * @return void
 * @access public
 * @ignore
 */
function printGroup(& $group) {
	$id =& $group->getId();
	$groupType =& $group->getType();
	print "<input type='radio' name='agent' value='".$id->getIdString()."' />";
	print "<a title='".$groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()."'>";
	print "<span style='text-decoration: underline; font-weight: bold;'>".$id->getIdString()." - ".$group->getDisplayName()."</span></a>";	
	print " - <em>".$groupType->getDescription()."</em>";
}

/**
 * Callback function for printing an agent
 * 
 * @param object Agent $member
 * @return void
 * @access public
 * @ignore
 */
function printMember(& $member) {
	$id =& $member->getId();
	$memberType =& $member->getType();
	print "<input type='radio' name='agent' value='".$id->getIdString()."' />";
	print "<a title='".$memberType->getAuthority()." :: ".$memberType->getDomain()." :: ".$memberType->getKeyword()."'>";
	print "<span style='text-decoration: underline;'>".$id->getIdString()." - ".$member->getDisplayName()."</span></a>";
	print " - <em>".$memberType->getDescription()."</em>";
}

