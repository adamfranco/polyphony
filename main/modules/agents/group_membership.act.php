<?php

/**
 * group_membership.act.php
 * This action will allow for the modification of group Membership.
 * @since 11/10/04 
 * @author Adam Franco
 *
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: group_membership.act.php,v 1.24 2005/06/02 21:32:01 adamfranco Exp $
 */

// Check for our authorization function definitions
if (!defined("AZ_CREATE_GROUPS"))
	throwError(new Error("You must define an id for AZ_CREATE_GROUPS", "polyphony.authorizations", true));
if (!defined("AZ_MODIFY_GROUPS"))
	throwError(new Error("You must define an id for AZ_MODIFY_GROUPS", "polyphony.authorizations", true));
if (!defined("AZ_DELETE_GROUPS"))
	throwError(new Error("You must define an id for AZ_DELETE_GROUPS", "polyphony.authorizations", true));
if (!defined("AZ_ROOT_NODE"))
	throwError(new Error("You must define an id for AZ_ROOT_NODE", "polyphony.authorizations", true));

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

$centerPane->add($pageRows, null, null, CENTER , CENTER);

// Intro
$introHeader =& new Heading(_("Manage Group Membership"), 2);
$pageRows->add($introHeader, "100%", null, LEFT, CENTER);


// Check for authorization
$authZManager =& Services::getService("AuthZ");
$idManager =& Services::getService("IdManager");
if (!$authZManager->isUserAuthorized(
			$idManager->getId(AZ_MODIFY_GROUPS),
			$idManager->getId(AZ_ROOT_NODE)))
{
	$errorLayout =& new Block(
		_("You are not authorized to modify group membership."), 3);
	$pageRows->add($errorLayout, "100%", null, LEFT, CENTER);
	
	return $mainScreen;
}


$agentManager =& Services::getService("Agent");
$idManager = Services::getService("Id");
$everyoneId =& $idManager->getId("-1");

// Build a variable to pass around our search terms when expanding
if (count($_GET)) {
		$search = "?";
		foreach ($_GET as $key => $val)
			$search .= "&".urlencode($key)."=".urlencode($val);
}

// Users header
$agentHeader =& new Heading(_("Users"), 2);
$pageRows->add($agentHeader, "100%", null, LEFT, CENTER);



/*********************************************************
 * the agent search form
 *********************************************************/
ob_start();

$self = $harmoni->request->quickURL();
$lastCriteria = $harmoni->request->get("search_criteria");
$search_criteria_name = RequestContext::name("search_criteria");
$search_type_name = RequestContext::name("search_type");
print _("Search For Users").": ";
print <<<END
<form action='$self' method='get'>
	<div>
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
	if ($harmoni->request->get("search_type") == $typeString)
		print " selected='selected'";
	print ">$typeString</option>";
}

	print "\n\t</select>";
	print "\n\t<br /><input type='submit' value='"._("Search")."' />";
	print "\n\t<a href='".MYURL."/".implode("/", $harmoni->pathInfoParts)."/'>";
	print "<input type='button' value='"._("Clear")."' /></a>";
print "\n</div>\n</form>";

$agentLayout =& new Block(ob_get_contents(), 3);
ob_end_clean();
$pageRows->add($agentLayout, "100%", null, LEFT, CENTER);
$pageRows->add($preActionRows, null, null,CENTER, CENTER);
$pageRows->add($actionRows, null, null,CENTER, CENTER);
$pageRows->add($postActionRows, null, null,CENTER, CENTER);


/*********************************************************
 * the agent search results
 *********************************************************/
 
if (($search_criteria = $harmoni->request->get('search_criteria')) && ($search_type = $harmoni->request('search_type'))) {
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
	
	$agentLayout =& new Block(ob_get_contents(), 3);
	ob_end_clean();
	$actionRows->add($agentLayout, "100%", null, LEFT, CENTER);	
}



/*********************************************************
 * Groups
 *********************************************************/

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

ob_start();

print _("Groups");

// Create translated errorstrings
$cannotAddGroup = _("Cannot add group");
$toItsself = _("to itself");
$deselecting = _("Deselecting");
$toOwnDesc = _("to its own descendent");
$groupString = _("Group");
$isAlreadyInGroup = _("is alread in this group");
$agentString = _("Agent");
$fromItsself = _("from itself");
$cannotRemoveGroup = _("Cannot remove group");
$notInGroup = _("is not in this group");
$confirmAdd = _("Are you sure that you wish to add the selected Groups and Agents to Group");
$confirmRemove = _("Are you sure that you wish to remove the selected Groups and Agents from Group");

// Print out a Javascript function for submitting our groups choices
print <<<END

<script type='text/javascript'>
//<![CDATA[ 

	// Validate ancestory and submit to add checked to the group
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
		var i;
		var numToAdd = 0;
				
		for (i = 0; i < elements.length; i++) {
			var element = elements[i];
			
			if (element.type == 'checkbox' && element.checked == true) {
				
				if (element.value == 'group') {
					// Check that the destination is not the new member
					if ( element.name == destGroupId ) {
						alert ("$cannotAddGroup " + element.name + " $toItsself. $deselecting...");
						element.checked = false;
						continue;
					}
					
					// Check that the destination is not a child of the new member
					if ( eval("hasDescendent" + element.name + "('" + destGroupId + "')") ) {
						alert ("$cannotAddGroup " + element.name + " $toOwnDesc.  $deselecting...");
						element.checked = false;
						continue;
					}
					
					// Check that the new member is not already a child of the destination
					if ( eval("hasChildGroup" + destGroupId + "('" + element.name + "')") ) {
						alert ("$groupString " + element.name + " $isAlreadyInGroup.  $deselecting...");
						element.checked = false;
						continue;
					}
				} else {
					// Check that the new member is not already a child of the destination
					if ( eval("hasChildMember" + destGroupId + "('" + element.name + "')") ) {
						alert ("$agentString " + element.name + " $isAlreadyInGroup.  $deselecting...");
						element.checked = false;
						continue;
					}
				}
				
				// If we haven't skipped back to the top of the loop yet, increment our ticker.
				numToAdd++;
 			}
		}
		
		
		if (numToAdd && confirm("$confirmAdd " + destGroupId + "?")) {
			form.destinationgroup.value = (destGroupId);
			form.submit();
		}
	}
	
	// Validate that the check are children and submit to remove them from the group
	function submitCheckedFromGroup ( destGroupId ) {
		var f;		
		for (i = 0; i < document.forms.length; i++) {
			f = document.forms[i];			
			if (f.id == 'memberform') {
				var form = f;
				break;
			}
		}		
		
		var elements = form.elements;
		var i;
		var numToAdd = 0;
				
		
		
		for (i = 0; i < elements.length; i++) {
			var element = elements[i];
			
			if (element.type == 'checkbox' && element.checked == true) {
				// Check that the destination is not the new member
				if ( element.name == destGroupId ) {
					alert ("$cannotRemoveGroup " + element.name + " $fromItsself. $deselecting...");
					element.checked = false;
					continue;
				}
				
				if (element.value == 'group') {					
					// Check that the new member is not already a child of the destination
					if ( ! eval("hasChildGroup" + destGroupId + "('" + element.name + "')") ) {
						alert ("$groupString " + element.name + " $notInGroup.  $deselecting...");
						element.checked = false;
						continue;
					}
				} else {
					// Check that the new member is not already a child of the destination
					if ( ! eval("hasChildMember" + destGroupId + "('" + element.name + "')") ) {
						alert ("$agentString " + element.name + " $notInGroup.  $deselecting...");
						element.checked = false;
						continue;
					}
				}
				
				// If we haven't skipped back to the top of the loop yet, increment our ticker.
				numToAdd++;
 			}
		}
		
		if (numToAdd && confirm("$confirmRemove " + destGroupId + "?")) {
			form.destinationgroup.value = (destGroupId);
			form.action = form.action.replace('add_to_group', 'remove_from_group');
			form.submit();
		}
	}
	
//]]> 
</script>

<input type='hidden' name='destinationgroup' value='25' />

END;
$groupHeader =& new Heading(ob_get_contents(), 2);
ob_end_clean();
$actionRows->add($groupHeader, "100%", null, LEFT, CENTER);

$groups =& $agentManager->getGroups();
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


/*********************************************************
 * Return the main layout.
 *********************************************************/
return $mainScreen;


/*********************************************************
 * Functions used for the GroupPrinter
 *********************************************************/
/**
 * Callback function for printing a group
 * 
 * @param object Group $group
 * @return void
 * @access public
 * @ignore
 */
function printGroup(& $group) {
	$idManager = Services::getService("Id");
	$everyoneId =& $idManager->getId("-1");
	
	$id =& $group->getId();
	$groupType =& $group->getType();
	
	if ($id->isEqual($everyoneId))
		print "\n&nbsp; &nbsp; &nbsp;";
	else
		print "\n<input type='checkbox' name='".RequestContext::name($id->getIdString())."' value='group' />";
	
	print "\n<a title='".htmlspecialchars($groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()." - ".$groupType->getDescription())."'>";
	print "\n<span style='text-decoration: underline; font-weight: bold;'>".$id->getIdString()." - ".htmlspecialchars($group->getDisplayName())."</span></a>";
	
	if (!$id->isEqual($everyoneId)) {
		print "\n <input type='button' value='"._("Add checked")."'";
		print " onclick='Javascript:submitCheckedToGroup(\"".$id->getIdString()."\")'";
		print " />";
		print "\n <input type='button' value='"._("Remove checked")."'";
		print " onclick='Javascript:submitCheckedFromGroup(\"".$id->getIdString()."\")'";
		print " />";
	}
	
	print "\n - <em>".htmlspecialchars($group->getDescription())."</em>";
	
	// print out the properties of the Agent
	print "\n<em>";
	$propertiesIterator =& $group->getProperties();
	
	while($propertiesIterator->hasNext()) {
		$properties =& $propertiesIterator->next();
		$propertiesType =& $properties->getType();
		print "\n\t(<a title='".htmlspecialchars($propertiesType->getDomain()." :: ".$propertiesType->getAuthority()." :: ".$propertiesType->getKeyword()." - ".$propertiesType->getDescription())."'>";
		
		$keys =& $properties->getKeys();
		$i = 0;
		
		while ($keys->hasNext()) {
			$key =& $keys->next();			
			print htmlspecialchars("\n\t\t".(($i)?", ":"").$key.": ".$properties->getProperty($key));
			$i++;
		}
		
		print "\n\t</a>)";
	}
	print "\n</em>";

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
	while($agents->hasNextAgent()) {
		$child =& $agents->nextAgent();
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
	print "\n<input type='checkbox' name='".RequestContext::name($id->getIdString())."' value='agent' />";
//	print "\n<a href='".MYURL."/agents/edit_agent_details/".$id->getIdString()."?callingFrom=group_membership' title='".htmlspecialchars($memberType->getDomain()." :: ".$memberType->getAuthority()." :: ".$memberType->getKeyword()." - ".$memberType->getDescription())."'>";
	
//	$harmoni->history->markReturnURL("polyphony/agents/edit_agent_details");
	
	print "\n<a href='".$harmoni->request->quickURL("agents","edit_agent_details", array("agent_id"=>$id->getIdString(), "callingFrom"=>"group_membership"))."' title='".htmlspecialchars($memberType->getDomain()." :: ".$memberType->getAuthority()." :: ".$memberType->getKeyword()." - ".$memberType->getDescription())."'>";
	print "\n<span style='text-decoration: none;'>".$id->getIdString()." - ".htmlspecialchars($member->getDisplayName())."</span></a>";
	
	// print out the properties of the Agent
	print "\n<em>";
	$propertiesIterator = NULL;
	$propertiesIterator =& $member->getProperties();
	while($propertiesIterator->hasNext()) {
		$properties = NULL;
		$properties =& $propertiesIterator->next();
		
		$propertiesType =& $properties->getType();
		print "\n\t(<a title='".htmlspecialchars($propertiesType->getDomain()." :: ".$propertiesType->getAuthority()." :: ".$propertiesType->getKeyword()." - ".$propertiesType->getDescription())."'>";
		
		$keys =& $properties->getKeys();
		$i = 0;
		while ($keys->hasNext()) {
			$key =& $keys->next();			
			print htmlspecialchars("\n\t\t".(($i)?", ":"").$key.": ".$properties->getProperty($key));
			$i++;
		}
		
		print "\n\t</a>)";
	}
	print "\n</em>";
}