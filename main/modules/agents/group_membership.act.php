<?php

/**
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: group_membership.act.php,v 1.31 2005/07/20 14:54:25 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");

/**
 * This action will allow for the modification of group Membership.
 *
 * @since 11/10/04 
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: group_membership.act.php,v 1.31 2005/07/20 14:54:25 adamfranco Exp $
 */
class group_membershipAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check for authorization
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		if ($authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify_groups"),
					$idManager->getId("edu.middlebury.authorization.root")))
		{
			return TRUE;
		} else
			return FALSE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "Manage Group Membership");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$defaultTextDomain = textdomain("polyphony");
		
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough();

		// register this action as the return-point for the following operations:
		$harmoni->history->markReturnURL("polyphony/agents/add_to_group");
		$harmoni->history->markReturnURL("polyphony/agents/remove_from_group");

		$agentManager =& Services::getService("Agent");
		$idManager = Services::getService("Id");
		$everyoneId =& $idManager->getId("-1");
		$usersId =& $idManager->getId("-2");
		
		
		/*********************************************************
		 * the agent search form
		 *********************************************************/
		// Users header
		$actionRows->add(new Heading(_("Users"), 2), "100%", null, LEFT, CENTER);
		
		ob_start();
		$self = $harmoni->request->quickURL();
		$lastCriteria = $harmoni->request->get("search_criteria");
		$search_criteria_name = RequestContext::name("search_criteria");
		$search_type_name = RequestContext::name("search_type");
		print _("Search For Users").": ";
		print <<<END
		<form action='$self' method='post'>
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
			print "\n\t<a href='".$harmoni->request->quickURL()."'>";
			print "<input type='button' value='"._("Clear")."' /></a>";
		print "\n</div>\n</form>";
		
		$actionRows->add(new Block(ob_get_contents(), 3), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		
		/*********************************************************
		 * the agent search results
		 *********************************************************/
		ob_start();
		if (($search_criteria = $harmoni->request->get('search_criteria')) && ($search_type = $harmoni->request->get('search_type'))) {
			$typeParts = explode("::", @html_entity_decode($search_type, ENT_COMPAT, 'UTF-8'));
			$searchType =& new HarmoniType($typeParts[0], $typeParts[1], $typeParts[2]);
			$agents =& $agentManager->getAgentsBySearch($search_criteria, $searchType);
			print "search: " . $search_criteria;
			
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
				group_membershipAction::printMember($agent);
				print "<br />";
			}
			print "\n</div>";
			
			$pageRows->add(new Block(ob_get_contents(), 3), "100%", null, LEFT, CENTER);	
			ob_end_clean();
		}
		
		
		
		/*********************************************************
		 * Groups
		 *********************************************************/
 		$pageRows->add(new Heading(_("Groups"), 2), "100%", null, LEFT, CENTER);

		
		// Define some global variables to store javascript array definitions
		// for validating adding/removing inputs.
		$GLOBALS['child_groups_string'] = "";
		$GLOBALS['child_agents_string'] = "";
		
		// Loop through all of the Groups and figure out which ones are childen of
		// other groups, so that we can just display the root-groups
		$childGroupIds = array();
		$groups =& $agentManager->getGroups();
		while ($groups->hasNext()) {
			$group =& $groups->next();
			if (!$everyoneId->isEqual($group->getId()) && !$usersId->isEqual($group->getId())) {
				$childGroups =& $group->getGroups(FALSE);
				while ($childGroups->hasNext()) {
					$group =& $childGroups->next();
					$groupId =& $group->getId();
					$childGroupIds[] =& $groupId->getIdString();
				}
			}
		}
		
		$groups =& $agentManager->getGroups();
		while ($groups->hasNext()) {
			$group =& $groups->next();
			$groupId =& $group->getId();
			
			if (!in_array($groupId->getIdString(), $childGroupIds)) {
				
				// Create a layout for this group using the GroupPrinter
				ob_start();
				
				GroupPrinter::printGroup($group, $harmoni,
												2,
												"group_membershipAction::printGroup", 
												"group_membershipAction::printMember");
				$groupLayout =& new Block(ob_get_contents(), 4);
				ob_end_clean();
				$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
			}
		}
		
		
		/*********************************************************
		 * Javascript for validating checkboxes,
		 * Form Definition.
		 *********************************************************/
		ob_start();
		
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
		
		$destinationgroup_name = RequestContext::name("destinationgroup");
		$operation_name = RequestContext::name("operation");
		$actionURL = $harmoni->request->quickURL("agents","add_to_group");
		
		// Print out a Javascript function for submitting our groups choices
		$decendentGroups = $GLOBALS['decendent_groups_string'];
		$childGroups = $GLOBALS['child_groups_string'];
		$childAgents = $GLOBALS['child_agents_string'];
		print <<<END
		
		<script type='text/javascript'>
		//<![CDATA[ 
		
			// Validate ancestory and submit to add checked to the group
			function submitCheckedToGroup ( destGroupId ) {
				var f;
				var form;
				for (i = 0; i < document.forms.length; i++) {
					f = document.forms[i];			
					if (f.id == 'memberform') {
						form = f;
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
							if ( element.id == destGroupId ) {
								alert ("$cannotAddGroup " + element.id + " $toItsself. $deselecting...");
								element.checked = false;
								continue;
							}
							
							// Check that the destination is not a decendent of the new member
							if ( in_array(destGroupId, decendentGroups[element.id]) ) {
								alert ("$cannotAddGroup " + element.id + " $toOwnDesc.  $deselecting...");
								element.checked = false;
								continue;
							}
							
							// Check that the new member is not already a child of the destination
							if ( in_array(element.id, childGroups[destGroupId]) ) {
								alert ("$groupString " + element.id + " $isAlreadyInGroup.  $deselecting...");
								element.checked = false;
								continue;
							}
						} else {
							// Check that the new member is not already a child of the destination
							if ( in_array(element.id, childAgents[destGroupId]) ) {
								alert ("$agentString " + element.id + " $isAlreadyInGroup.  $deselecting...");
								element.checked = false;
								continue;
							}
						}
						
						// If we haven't skipped back to the top of the loop yet, increment our ticker.
						numToAdd++;
					}
				}
				
				
				if (numToAdd && confirm("$confirmAdd " + destGroupId + "?")) {
					form.destinationgroup.value = destGroupId;
					form.submit();
				}
			}
			
			// Validate that the check are children and submit to remove them from the group
			function submitCheckedFromGroup ( destGroupId ) {
				var f;
				var form;
				for (i = 0; i < document.forms.length; i++) {
					f = document.forms[i];			
					if (f.id == 'memberform') {
						form = f;
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
						if ( element.id == destGroupId ) {
							alert ("$cannotRemoveGroup " + element.id + " $fromItsself. $deselecting...");
							element.checked = false;
							continue;
						}
						
						if (element.value == 'group') {					
							// Check that the new member is not already a child of the destination
							if ( !in_array(element.id, childGroups[destGroupId]) ) {
								alert ("$groupString " + element.id + " $notInGroup.  $deselecting...");
								element.checked = false;
								continue;
							}
						} else {
							// Check that the new member is not already a child of the destination
							if ( !in_array(element.id, childAgents[destGroupId]) ) {
								alert ("$agentString " + element.id + " $notInGroup.  $deselecting...");
								element.checked = false;
								continue;
							}
						}
						
						// If we haven't skipped back to the top of the loop yet, increment our ticker.
						numToAdd++;
					}
				}
				
				if (numToAdd && confirm("$confirmRemove " + destGroupId + "?")) {
					form.destinationgroup.value = destGroupId;
					form.action = form.action.replace('add_to_group','remove_from_group');
					form.submit();
				}
			}
			
			function in_array( aValue, anArray) {
				for (i = 0; i < anArray.length; i++) {
					if (anArray[i] == aValue)
						return true;
				}
				
				return false;
			}
			
			// Decendent Groups
			var decendentGroups = new Array ();
$decendentGroups
		
			// Child Groups
			var childGroups = new Array ();
$childGroups
			
			// Child Agents
			var childAgents = new Array ();
$childAgents
			
		//]]> 
		</script>
		
		<form id='memberform' method='post' action='$actionURL'>
		<input type='hidden' id='destinationgroup' name='$destinationgroup_name' value=''/>
		
END;
		
		$pageRows->setPreHTML(ob_get_contents());
		ob_end_clean();
		
		$pageRows->setPostHTML("</form>");
		
		
		 // In order to preserve proper nesting on the HTML output, the checkboxes
		 // are all in the pagerows layout instead of actionrows.
 		$actionRows->add($pageRows, null, null,CENTER, CENTER);
 		
 		textdomain($defaultTextDomain);
	}
	
	
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
		$usersId =& $idManager->getId("-2");
		
		$id =& $group->getId();
		$groupType =& $group->getType();
		
		if ($id->isEqual($everyoneId) || $id->isEqual($usersId))
			print "\n&nbsp; &nbsp; &nbsp;";
		else
			print "\n<input type='checkbox' id='".$id->getIdString()."' name='".RequestContext::name($id->getIdString())."' value='group' />";
		
		print "\n<a title='".htmlspecialchars($groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()." - ".$groupType->getDescription())."'>";
		print "\n<span style='text-decoration: underline; font-weight: bold;'>".$id->getIdString()." - ".htmlspecialchars($group->getDisplayName())."</span></a>";
		
		if (!$id->isEqual($everyoneId) && !$id->isEqual($usersId)) {
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
		
		
		//-------------------------------------------------
		// put the decendent-groups into a javascript array element
		ob_start();
		$idString = $id->getIdString();
		print <<<END
		
			decendentGroups['$idString'] = new Array (

END;
		$groups =& $group->getGroups(TRUE);
		$i = 0;
		while($groups->hasNext()) {
			$child =& $groups->next();
			$childId =& $child->getId();
			print (($i)?",\n\t\t\t\t":"\t\t\t\t")."'".$childId->getIdString()."'";
			$i++;
		}
	
		print "\n\t\t\t\t);";
		$GLOBALS['decendent_groups_string'] .= ob_get_contents();
		ob_end_clean();
		
		//-------------------------------------------------
		// put the decendent-groups into a javascript array element
		ob_start();
		$idString = $id->getIdString();
		print <<<END
		
			childGroups['$idString'] = new Array (

END;
		$groups =& $group->getGroups(FALSE);
		$i = 0;
		while($groups->hasNext()) {
			$child =& $groups->next();
			$childId =& $child->getId();
			print (($i)?",\n\t\t\t\t":"\t\t\t\t")."'".$childId->getIdString()."'";
			$i++;
		}
	
		print "\n\t\t\t\t);";
		$GLOBALS['child_groups_string'] .= ob_get_contents();
		ob_end_clean();
		
		//-------------------------------------------------
		// put the decendent-groups into a javascript array element
		ob_start();
		$idString = $id->getIdString();
		print <<<END
		
			childAgents['$idString'] = new Array (

END;
		$agents =& $group->getMembers(FALSE);
		$i = 0;
		while($agents->hasNextAgent()) {
			$child =& $agents->nextAgent();
			$childId =& $child->getId();
			print (($i)?",\n\t\t\t\t":"\t\t\t\t")."'".$childId->getIdString()."'";
			$i++;
		}
	
		print "\n\t\t\t\t);";
		$GLOBALS['child_agents_string'] .= ob_get_contents();
		ob_end_clean();
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
		$harmoni =& Harmoni::instance();
		$id =& $member->getId();
		
		$memberType =& $member->getType();
		print "\n<input type='checkbox' id='".$id->getIdString()."' name='".RequestContext::name($id->getIdString())."' value='agent' />";
		
		$harmoni->history->markReturnURL("polyphony/agents/edit_agent_details");
		
		print "\n<a href='".$harmoni->request->quickURL("agents","edit_agent_details", array("agentId"=>$id->getIdString()))."' title='".htmlspecialchars($memberType->getDomain()." :: ".$memberType->getAuthority()." :: ".$memberType->getKeyword()." - ".$memberType->getDescription())."'>";
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
}