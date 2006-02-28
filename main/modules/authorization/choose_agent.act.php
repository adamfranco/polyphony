<?php

/**
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: choose_agent.act.php,v 1.39 2006/02/28 21:32:49 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * This file will allow the user to choose which agent they wish to modify 
 * authorizations for.
 *
 * @since 11/11/04 
 * @author Ryan Richards
 * @author Adam Franco
 * 
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: choose_agent.act.php,v 1.39 2006/02/28 21:32:49 adamfranco Exp $
 */
class choose_agentAction 
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
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "Modify Authorizations for which User/Group?");
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
		
		// start our namespace
		$harmoni->history->markReturnURL("polyphony/authorization/edit_authorizations");
		$harmoni->request->startNamespace("polyphony-authorizations");
		$harmoni->request->passthrough();
		
		$agentManager =& Services::getService("Agent");
		$idManager = Services::getService("Id");
		$everyoneId =& $idManager->getId("edu.middlebury.agents.everyone");
		$usersId =& $idManager->getId("edu.middlebury.agents.users");
		
		/*********************************************************
		 * Buttons
		 *********************************************************/
		 
		ob_start();
		print "<table width='100%'><tr><td align='left'>";
		print "<a href='".$harmoni->request->quickURL("admin","main")."'><button>"._("Return to the Admin Tools")."</button></a>";
		print "</td><td align='right'>";
		print "<input type='button'";
		print " onclick='Javascript:submitAgentChoice()'";
		print " value='"._("Edit Authorizations for the selected User/Group")." --&gt;' />";
		print "</td></tr></table>";
		
		$submit =& new Block(ob_get_contents(), STANDARD_BLOCK);
		$actionRows->add($submit, "100%", null, LEFT, CENTER);
		ob_end_clean();	
		
		
		// Users header
		$actionRows->add(new Heading("Users", 2), "100%", null, LEFT, CENTER);
		
		/*********************************************************
		 * the agent search form
		 *********************************************************/
		ob_start();
		
		$self = $harmoni->request->quickURL();
		$lastCriteria = $harmoni->request->get('search_criteria');
		$search_criteria_name = RequestContext::name('search_criteria');
		$search_type_name = RequestContext::name('search_type');
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
			$typeString = $type->getDomain()
								."::".$type->getAuthority()
								."::".$type->getKeyword();
			print "\n\t\t<option value='".htmlspecialchars($typeString, ENT_QUOTES)."'";
			if ($harmoni->request->get("search_type") == $typeString)
				print " selected='selected'";
			print ">".htmlspecialchars($typeString)."</option>";
		}
		
		print "\n\t</select>";
		print "\n\t<br /><input type='submit' value='"._("Search")."' />";
		print "\n\t<a href='".$harmoni->request->quickURL()."'>";
		print "\n\t\t<input type='button' value='"._("Clear")."' />\n\t</a>";
		print "\n</div>\n</form>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK));
		ob_end_clean();
		
		/*********************************************************
		 * Form and Javascript
		 *********************************************************/
		// In order to preserve proper nesting on the HTML output put the form
		// around the row layout
		ob_start();
		
		$errorString = _("You must select a User or Group.");
		$agentFieldName = RequestContext::name("agentId");
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
				
				var radioArray = form.agentId;
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
		print "<form id='chooseform' method='post' action='".$harmoni->request->quickURL("authorization","edit_authorizations")."'>\n";
		
		$pageRows->setPreHTML(ob_get_contents());
		ob_end_clean();
		$pageRows->setPostHTML("</form>");

		/*********************************************************
		 * the agent search results
		 *********************************************************/
		 
		$search_criteria = $harmoni->request->get("search_criteria");
		$search_type = $harmoni->request->get("search_type");
		if ($search_criteria && $search_type) {
			$typeParts = explode("::", $search_type);
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
				$this->printMember($agent);
				print "<br />";
			}
			print "\n</div>";
			
			$pageRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);	
			ob_end_clean();
		}
		
		
		/*********************************************************
		 * Groups
		 *********************************************************/
		
		// Users header
		$pageRows->add(new Heading(_("Groups"), 2), "100%", null, LEFT, CENTER);
		
		// Loop through all of the Groups
		$childGroupIds = array();
		$groups =& $agentManager->getGroupsBySearch($null = null, new Type("Agent & Group Search", "edu.middlebury.harmoni", "RootGroups"));

		while ($groups->hasNext()) {
			$group =& $groups->next();
			$groupId =& $group->getId();
			
			
			// Create a layout for this group using the GroupPrinter
			ob_start();
			GroupPrinter::printGroup($group, $harmoni,
											2,
											"choose_agentAction::printGroup", 
											"choose_agentAction::printMember");
			$groupLayout =& new Block(ob_get_contents(), STANDARD_BLOCK);
			ob_end_clean();
			$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
		
		}
		$pageRows->add($submit, "100%", null, LEFT, CENTER);
		
		$actionRows->add($pageRows);
		
		$harmoni->request->endNamespace();
		
		textdomain($defaultTextDomain);
	}


/*********************************************************
 * Callback functions for printing
 *********************************************************/
	
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
		print "<input type='radio' id='agentId' name='".RequestContext::name("agentId")."' value='".$id->getIdString()."' />";
		print "<a title='".$id->getIdString()."'>";
		print "<span style='text-decoration: underline; font-weight: bold;'>".$group->getDisplayName()."</span></a>";	
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
		$harmoni =& Harmoni::instance();
		$harmoni->request->forget("expandedGroups");
		$harmoni->request->forget("search_criteria");		
		$harmoni->request->forget("search_type");
		$harmoni->request->forget("agentId");
		$oldNS = $harmoni->request->endNamespace();
 		$harmoni->request->startNamespace("polyphony-agents");
		
		$agentId =& $member->getId();
		$agentIdString= $agentId->getIdString();
		
		$harmoni->history->markReturnURL("polyphony/agents/edit_agent_details");
		$link = $harmoni->request->quickURL("agents","edit_agent_details",array("agentId"=>$agentIdString));
		$harmoni->request->endNamespace();
		$harmoni->request->startNamespace($oldNS);
		$id =& $member->getId();
		$memberType =& $member->getType();
		print "<input type='radio' id='agentId' name='".RequestContext::name("agentId")."' value='".$id->getIdString()."' />";
		print "<a title='".$id->getIdString()."' href='$link'>";
		print "<span style='text-decoration: underline;'>".$member->getDisplayName()."</span></a>";
		print " - <em>".$memberType->getDescription()."</em>";		
		
// 		$harmoni->request->endNamespace();
		$harmoni->request->passthrough();
//		$harmoni->request->startNamespace($oldNS);
	}

}

?>