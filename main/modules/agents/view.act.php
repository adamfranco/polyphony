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

// In order to preserve proper nesting on the HTML output
$actionRows->setPreSurroundingText("<form id='memberform' method='post' action='".MYURL."/agents/add_to_group/".implode("/", $harmoni->pathInfoParts)."'>");
$actionRows->setPostSurroundingText("</form>");

$centerPane->addComponent($pageRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Users and Groups")));
$pageRows->addComponent($introHeader);

$agentManager =& Services::getService("Agent");

// Build a variable to pass around our search terms when expanding
if (count($_GET)) {
		$search = "?";
		foreach ($_GET as $key => $val)
			$search .= "&".urlencode($key)."=".urlencode($val);
}

// $sharedManager->createGroup("Math", new HarmoniType("Groups", "Middlebury College", "Department", "What department the user belongs to at Middlebury College"), "Middlebury College French department.");

// $id =& $sharedManager->getId("192");
// $sharedManager->deleteGroup($id);

// $id =& $sharedManager->getId("205");
// $group =& $sharedManager->getGroup($id);
// $memberId =& $sharedManager->getId("198");
// $member =& $sharedManager->getAgent($memberId);
// $group->add($member);

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
$titleString = _("Search For Users").": ";
print <<<END
<form id='usersearch' action='$self' method='get'>
	<div>
	$titleString
	<input type='text' name='search_criteria' value='$lastCriteria' />
	<br /><select name='search_type'>
END;

$searchTypes =& $agentManager->getAgentSearchTypes();
while ($searchTypes->hasNext()) {
	$type =& $searchTypes->next();
	$typeString = htmlspecialchars($type->getDomain()
						."::".$type->getAuthority()
						."::".$type->getKeyword());
	print "\n\t\t<option value='$typeString'";
	if ($_REQUEST['search_type'] == $typeString)
		print " selected='selected'";
	print ">$typeString</option>";
}

	print "\n\t</select>";
	print "\n\t<br /><input type='submit' value='"._("Search")."' />";
	print "\n\t<a href='".MYURL."/".implode("/", $harmoni->pathInfoParts)."/'>";
	print "\n<input type='button' value='"._("Clear")."' /></a>";
	print "\n</div>";
print "\n</form>";

$agentLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$agentLayout->addComponent(new Content(ob_get_contents()), BOTTOM);
ob_end_clean();
$pageRows->addComponent($agentLayout);
$pageRows->addComponent($actionRows, BOTTOM, CENTER);



/*********************************************************
 * the agent search results
 *********************************************************/
 
if ($_REQUEST['search_criteria'] && $_REQUEST['search_type']) {
	$typeParts = explode("::", html_entity_decode($_REQUEST['search_type'], ENT_COMPAT, UTF-8));
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
	
	$agentLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$agentLayout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	$actionRows->addComponent($agentLayout);	
}



/*********************************************************
 * All the agents
 *********************************************************/
 
$expandAgents = ((in_array("allagents", $harmoni->pathInfoParts))?TRUE:FALSE);

// Create a layout for this group using the GroupPrinter
ob_start();

print "\n\n<table>\n\t<tr><td valign='top'>";

print <<<END
<div style='
	border: 1px solid #000; 
	width: 15px; 
	height: 15px;
	text-align: center;
	text-decoration: none;
	font-weight: bold;
'>
END;

// Break the path info into parts for the enviroment and parts that
// designate which nodes to expand.
$environmentInfo = array();
$expandedNodes = array();

for ($i=0; $i<count($harmoni->pathInfoParts); $i++) {
	// If the index equals or is after our starting key
	// it designates an expanded nodeId.
	if ($i >= 2)
		$expandedNodes[] = $harmoni->pathInfoParts[$i];
	else	
		$environmentInfo[] = $harmoni->pathInfoParts[$i];
}
		
if ($expandAgents) {
	$nodesToRemove = array("allagents");
	$newPathInfo = array_merge($environmentInfo, array_diff($expandedNodes,
															$nodesToRemove)); 
	print "<a style='text-decoration: none;' href='";
	print MYURL."/".implode("/", $newPathInfo)."/".$search;
	print "'>-</a>";
} else {
	$newPathInfo = array_merge($environmentInfo, $expandedNodes); 
	print "<a style='text-decoration: none;' href='";
	print MYURL."/".implode("/", $newPathInfo)."/allagents/".$search;
	print "'>+</a>";
}
print "</div>";
print "\n\t</td><td valign='top'>\n\t\t";
print _("All Agents");
print "\n\t</td></tr>\n</table>";

if ($expandAgents) {
	print <<<END
<div style='
	margin-left: 13px; 
	margin-right: 0px; 
	margin-top:0px; 
	padding-left: 10px;
	border-left: 1px solid #000;
'>
END;
	$agents =& $agentManager->getAgents();
	while ($agents->hasNext()) {
		$agent =& $agents->next();
		printMember($agent);
		print "<br />";
	}
	print "</div>";
}

$agentLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
$agentLayout->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($agentLayout);



/*********************************************************
 * Groups
 *********************************************************/

// Loop through all of the Groups and figure out which ones are childen of
// other groups, so that we can just display the root-groups
$childGroupIds = array();
$groups =& $agentManager->getGroups();
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
$groupHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$groupHeader->addComponent(new Content(_("Groups")));
$actionRows->addComponent($groupHeader);

$groups =& $agentManager->getGroups();
while ($groups->hasNext()) {
	$group =& $groups->next();
	$groupId =& $group->getId();
	
	if (!in_array($groupId->getIdString(), $childGroupIds)) {
		
		// Create a layout for this group using the GroupPrinter
		ob_start();
		
		// Print out a Javascript function for submitting our groups choices
		print <<<END

<script type='text/javascript'>
//<![CDATA[ 

	// Validate ancestory and submit
	function submitCheckedToGroup ( destGroupId ) {
		var f;		
		for (i = 0; i < document.forms.length; i++) {
			f = document.forms[i];			
			if (f.id == 'memberform') {
				var form = f;
				break;
			}
		}
		
		var elements = form.elements;
				
		for (i = 0; i < elements.length; i++) {
			var element = elements[i];
			
			if (element.type == 'checkbox' && element.checked == true) {
				
				if (element.value == 'group') {
					// Check that the destination is not the new member
					if ( element.name == destGroupId ) {
						alert ("Cannot add group " + element.name + " to itself. Deselecting...");
						element.checked = false;
					}
					
					// Check that the destination is not a child of the new member
					if ( eval("hasDescendent" + element.name + "('" + destGroupId + "')") ) {
						alert ("Cannot add group " + element.name + " to its own descendent.  Deselecting...");
						element.checked = false;
					}
					
					// Check that the new member is not already a child of the destination
					if ( eval("hasChildGroup" + destGroupId + "('" + element.name + "')") ) {
						alert ("Group " + element.name + " is alread in this group.  Deselecting...");
						element.checked = false;
					}
				} else {
					// Check that the new member is not already a child of the destination
					if ( eval("hasChildMember" + destGroupId + "('" + element.name + "')") ) {
						alert ("Agent " + element.name + " is alread in this group.  Deselecting...");
						element.checked = false;
					}
				}
 			}
		}
		
		if (confirm("Are you sure that you wish to add the selected groups and Agents to Group " + destGroupId + "?")) 
		{
			form.submit();
		}
	}

	// Check for proper ancestory
	function inAncestor( id ) {
		
	}

//]]> 	
</script>

END;
		
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


/*********************************************************
 * Return the main layout.
 *********************************************************/
return $mainScreen;


/*********************************************************
 * Functions used for the GroupPrinter
 *********************************************************/
 function printGroup(& $group) {
	$id =& $group->getId();
	$groupType =& $group->getType();
	
	print "\n<input type='checkbox' name='".$id->getIdString()."' value='group' />";
	print "\n<a title='".htmlspecialchars($groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()." - ".$groupType->getDescription())."'>";
	print "\n<span style='text-decoration: underline; font-weight: bold;'>".$id->getIdString()." - ".htmlspecialchars($group->getDisplayName())."</span></a>";
	
	print "\n <input type='button' value='"._("Add checked to this Group")."'";
	print " onclick='Javascript:submitCheckedToGroup(\"".$id->getIdString()."\")'";
	print " />";
	
	print "\n - <em>".htmlspecialchars($group->getDescription())."</em>";

	// print the children of the groups so that our Javascript function can check ancestory.
	$idString = $id->getIdString();
	print <<<END


<script type='text/javascript'>
//<![CDATA[ 

	// Function for deciding if this parent has the specified child
	function hasDescendent$idString ( childId ) {
		var children = new Array (
END;

	$groups =& $group->getGroups(TRUE);
	$i = 0;
	while($groups->hasNext()) {
		$child =& $groups->next();
		$childId =& $child->getId();
		print (($i)?", ":"")."'".$childId->getIdString()."'";
		$i++;
	}

	print <<<END
);
		var i;
		
		for (i = 0; i < children.length; i++) {
			if (children[i] == childId) {
				return true;
			}
		}
		
		return false;
	}
	
	// Function for deciding if this parent has the specified child
	function hasChildGroup$idString ( childId ) {
		var children = new Array (
END;

	$groups =& $group->getGroups(FALSE);
	$i = 0;
	while($groups->hasNext()) {
		$child =& $groups->next();
		$childId =& $child->getId();
		print (($i)?", ":"")."'".$childId->getIdString()."'";
		$i++;
	}

	print <<<END
);
		var i;
		
		for (i = 0; i < children.length; i++) {
			if (children[i] == childId) {
				return true;
			}
		}
		
		return false;
	}
	
	// Function for deciding if this parent has the specified child
	function hasChildMember$idString ( childId ) {
		var children = new Array (
END;

	$agents =& $group->getMembers(FALSE);
	$i = 0;
	while($agents->hasNext()) {
		$child =& $agents->next();
		$childId =& $child->getId();
		print (($i)?", ":"")."'".$childId->getIdString()."'";
		$i++;
	}

	print <<<END
);
		var i;
		
		for (i = 0; i < children.length; i++) {
			if (children[i] == childId) {
				return true;
			}
		}
		
		return false;
	}
	
//]]> 
</script>

END;
}

function printMember(& $member) {
	$id =& $member->getId();
	$memberType =& $member->getType();
	print "<input type='checkbox' name='".$id->getIdString()."' value='agent' />";
	print "<a title='".htmlspecialchars($memberType->getDomain()." :: ".$memberType->getAuthority()." :: ".$memberType->getKeyword()." - ".$memberType->getDescription())."'>";
	print "<span style='text-decoration: underline;'>".$id->getIdString()." - ".htmlspecialchars($member->getDisplayName())."</span></a>";
}