<?php

/**
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: group_browse.act.php,v 1.8 2006/02/28 21:32:49 adamfranco Exp $
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
 * @version $Id: group_browse.act.php,v 1.8 2006/02/28 21:32:49 adamfranco Exp $
 */
class group_browseAction 
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
 					$idManager->getId("edu.middlebury.authorization.view"),
 					$idManager->getId("edu.middlebury.authorization.root")))
 		{
			return TRUE;
 		} else {
 			
 			return FALSE;
		}
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "Browse Agents and Groups");
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

		$agentManager =& Services::getService("Agent");
		$idManager = Services::getService("Id");
		$everyoneId =& $idManager->getId("edu.middlebury.agents.everyone");
		$usersId =& $idManager->getId("edu.middlebury.agents.users");
		
		
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
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
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
				group_browseAction::printMember($agent);
				print "<br />";
			}
			print "\n</div>";
			
			$pageRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);	
			ob_end_clean();
		}
		
		
		
		/*********************************************************
		 * Groups
		 *********************************************************/
 		$pageRows->add(new Heading(_("Groups"), 2), "100%", null, LEFT, CENTER);

		
		// Loop through all of the Root Groups
		$childGroupIds = array();
		$groups =& $agentManager->getGroupsBySearch($null = null, new Type("Agent & Group Search", "edu.middlebury.harmoni", "RootGroups"));

		while ($groups->hasNext()) {
			$group =& $groups->next();
			$groupId =& $group->getId();
							
			// Create a layout for this group using the GroupPrinter
			ob_start();
			
			GroupPrinter::printGroup($group, $harmoni,
											2,
											"group_browseAction::printGroup", 
											"group_browseAction::printMember");
			$groupLayout =& new Block(ob_get_contents(), STANDARD_BLOCK);
			ob_end_clean();
			$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
		}
		
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
		$everyoneId =& $idManager->getId("edu.middlebury.agents.everyone");
		$usersId =& $idManager->getId("edu.middlebury.agents.users");
		
		$id =& $group->getId();
		$groupType =& $group->getType();
		
		print "\n&nbsp; &nbsp; &nbsp;";
		
		print "\n<a title='".htmlspecialchars($groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()." - ".$groupType->getDescription())."'>";
		print "\n<span style='text-decoration: underline; font-weight: bold;'>".htmlspecialchars($group->getDisplayName())."</span></a>";
		
		print "\n - <a href=\"Javascript:alert('"._("Id:").'\n\t'.addslashes($id->getIdString())."')\">"._("Show Id")."</a>";
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
		$harmoni->history->markReturnURL("polyphony/agents/edit_agent_details");
		
		print "\n<a href='".$harmoni->request->quickURL("agents","edit_agent_details", array("agentId"=>$id->getIdString()))."' title='".htmlspecialchars($memberType->getDomain()." :: ".$memberType->getAuthority()." :: ".$memberType->getKeyword()." - ".$memberType->getDescription())."'>";
		print "\n<span style='text-decoration: none;'>".htmlspecialchars($member->getDisplayName())."</span></a>";
		print "\n - <a href=\"Javascript:alert('"._("Id:").'\n\t'.addslashes($id->getIdString())."')\">"._("Show Id")."</a>";
		
		// print out the properties of the Agent
		print "\n<em>";
		$propertiesIterator = NULL;
		$propertiesIterator =& $member->getProperties();
		while($propertiesIterator->hasNext()) {
			$properties = NULL;
			$properties =& $propertiesIterator->next();
			
			$propertiesType =& $properties->getType();
			print "\n\t(<a style='text-decoration: none; font-weight: normal;' title='".htmlspecialchars($propertiesType->getDomain()." :: ".$propertiesType->getAuthority()." :: ".$propertiesType->getKeyword()." - ".$propertiesType->getDescription())."'>";
			
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