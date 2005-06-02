<?php

/**
 * choose_agent.act.php
 * This file will allow the user to choose an agent for which to edit authorizations.
 * The agents will be listed both by group and by agent.
 * The chosen agent information will be submitted to edit_authorizations.act.php via form action.
 * @since 11/10/04 
 * @author Ryan Richards
 *
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.23 2005/06/02 18:09:00 gabeschine Exp $
 */

// start our namespace
$harmoni->request->startNamespace("polyphony-agents");

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$yLayout =& new YLayout();
$pageRows =& new Container($yLayout, OTHER, 1);
$preActionRows =& new Container($yLayout, OTHER, 1);
$actionRows =& new Container($yLayout, OTHER, 1);
$postActionRows =& new Container($yLayout, OTHER, 1);

// In order to preserve proper nesting on the HTML output
$preActionRows->add(new Block("<form id='memberform' method='post' action='".$harmoni->request->quickURL("agents","add_to_group")."'>",2), null, null, CENTER, CENTER);
$postActionRows->add(new Block("</form>",2), null, null, CENTER, CENTER);

$centerPane->add($pageRows, null, null,CENTER, CENTER);

// Intro
$introHeader =& new Heading(_("Users and Groups"), 2);
$pageRows->add($introHeader, "100%", null, LEFT, CENTER);

$agentManager =& Services::getService("Agent");

// pass around our search terms when expanding
$harmoni->request->passthrough();

// Users header
$agentHeader =& new Heading(_("Users"), 2);
$pageRows->addComponent($agentHeader, "100%", null, LEFT, CENTER);



/*********************************************************
 * the agent search form
 *********************************************************/
ob_start();

$self = $harmoni->request->quickURL();
$lastCriteria = $_REQUEST['search_criteria'];
$titleString = _("Search For Users").": ";
$search_criteria_name = _n("search_criteria");
$search_type_name = _n("search_type");
print <<<END
<form id='usersearch' action='$self' method='get'>
	<div>
	$titleString
	<input type='text' name='$search_criteria_name' value='$lastCriteria' />
	<br /><select name='$search_type_name'>
END;

$searchTypes =& $agentManager->getAgentSearchTypes();
while ($searchTypes->hasNext()) {
	$type =& $searchTypes->next();
	$typeString = htmlspecialchars($type->getDomain()
						."::".$type->getAuthority()
						."::".$type->getKeyword());
	print "\n\t\t<option value='$typeString'";
	if ($harmoni->request->get('search_type') == $typeString)
		print " selected='selected'";
	print ">$typeString</option>";
}

	print "\n\t</select>";
	print "\n\t<br /><input type='submit' value='"._("Search")."' />";
	print "\n\t<a href='".$harmoni->request->quickURL()."'>";
	print "\n<input type='button' value='"._("Clear")."' /></a>";
	print "\n</div>";
print "\n</form>";

$agentLayout =& new Block(ob_get_contents(), 2);
ob_end_clean();
$pageRows->add($agentLayout, "100%", null, LEFT, CENTER);
$pageRows->add($preActionRows, null, null,CENTER, CENTER);
$pageRows->add($actionRows, null, null,CENTER, CENTER);
$pageRows->add($postActionRows, null, null,CENTER, CENTER);


/*********************************************************
 * the agent search results
 *********************************************************/
 
if (($search_critera = $harmoni->request->get("search_criteria") && ($search_type = $harmoni->request->get("search_type")) {
	$typeParts = explode("::", html_entity_decode($search_type, ENT_COMPAT, UTF-8));
	$searchType =& new HarmoniType($typeParts[0], $typeParts[1], $typeParts[2]);
	$agents =& $agentManager->getAgentsBySearch($search_criteria, $searchType);
	
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
	
	$agentLayout =& new Block(ob_get_contents(), 4);
	ob_end_clean();
	$actionRows->add($agentLayout, "100%", null, LEFT, CENTER);	
}



/*********************************************************
 * All the agents
 *********************************************************/

// extract the info on which nodes to expand
$expandedNodes = explode(",", $harmoni->request->get("expandedNodes"));

$expandAgents = (in_array("allagents", $expandedNodes)?TRUE:FALSE);

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

/*
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
*/
	
if ($expandAgents) {
	$nodesToRemove = array("allagents");
	$newNodes = array_diff($expandedNodes, $nodesToRemove);
	$url =& $harmoni->request->mkURL();
	$url->set("expandedNodes", implode(",", $newNodes));
	print "<a style='text-decoration: none;' href='";
	print $url->write();
	print "'>-</a>";
} else {
	$newNodes = $expandedNodes;
	$newNodes[] = "allagents";
	$url =& $harmoni->request->mkURL();
	$url->set("expandedNodes", implode(",",$newNodes));
	print "<a style='text-decoration: none;' href='";
	print $url->write();
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

$agentLayout =& new Block(ob_get_contents(), 4);
ob_end_clean();
$actionRows->add($agentLayout, "100%", null, LEFT, CENTER);



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
$groupHeader =& new Heading(_("Groups"), 2);
$actionRows->add($groupHeader, "100%", null, LEFT, CENTER);

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
		$groupLayout =& new Block(ob_get_contents(), 4);
		ob_end_clean();
		$actionRows->add($groupLayout, "100%", null, LEFT, CENTER);	
	}
}


/*********************************************************
 * Return the main layout.
 *********************************************************/

$harmoni->request->endNamespace();
 
return $mainScreen;


/*********************************************************
 * Functions used for the GroupPrinter
 *********************************************************/
 
 
 /**
  * Callback function for printing a group and its children.
  * 
  * @param object Group $group
  * @return void
  * @access public
 * @ignore
  * @since 2/4/05
  */
 function printGroup(& $group) {
	$id =& $group->getId();
	$groupType =& $group->getType();
	
	print "\n<input type='checkbox' name='"._n($id->getIdString())."' value='group' />";
	print "\n<a title='".htmlspecialchars($groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()." - ".$groupType->getDescription())."'>";
	print "\n<span style='text-decoration: underline; font-weight: bold;'>".$id->getIdString()." - ".htmlspecialchars($group->getDisplayName())."</span></a>";
	
	print "\n <input type='button' value='"._("Add checked to this Group")."'";
	print " onclick='Javascript:submitCheckedToGroup(\""._n($id->getIdString())."\")'";
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
		print (($i)?", ":"")."'"._n($childId->getIdString())."'";
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
		print (($i)?", ":"")."'"._n($childId->getIdString())."'";
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
		print (($i)?", ":"")."'"._n($childId->getIdString())."'";
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

/**
 * Callback function for printing a member of a group.
 * 
 * @param object Agent $member
 * @return void
 * @access public
 * @ignore
 */
function printMember(& $member) {
	$id =& $member->getId();
	$memberType =& $member->getType();
	print "<input type='checkbox' name='"._n($id->getIdString())."' value='agent' />";
	print "<a title='".htmlspecialchars($memberType->getDomain()." :: ".$memberType->getAuthority()." :: ".$memberType->getKeyword()." - ".$memberType->getDescription())."'>";
	print "<span style='text-decoration: underline;'>".$id->getIdString()." - ".htmlspecialchars($member->getDisplayName())."</span></a>";
}
