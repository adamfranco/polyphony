<?php

/**
 * @package polyphony.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: agent_search.act.php,v 1.5 2007/09/19 14:04:52 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");


class agent_SearchAction 
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
 		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
 		if ($authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.view"),
 					$idManager->getId("edu.middlebury.coursemanagement")))
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
		
		$actionRows =$this->getActionRows();
		$pageRows = new Container(new YLayout(), OTHER, 1);
		$harmoni = Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-agents");

		$agentManager = Services::getService("Agent");
		$idManager = Services::getService("Id");
		$cm = Services::getService("CourseManagement");
		
		$everyoneId =$idManager->getId("edu.middlebury.agents.everyone");
		$usersId =$idManager->getId("edu.middlebury.agents.users");
		
		
		
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
		print "<form action='$self' method='post'>
			<div>
			<input type='text' name='$search_criteria_name' value='$lastCriteria' />";

			print "\n\t<input type='submit' value='"._("Search")."' />";
			print "\n\t<a href='".$harmoni->request->quickURL()."'>";
			print "<input type='button' value='"._("Clear")."' /></a>";
		print "\n</div>\n</form>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		
		/*********************************************************
		 * the agent search results
		 *********************************************************/
		ob_start();
		

		
		if ($search_criteria = $harmoni->request->get('search_criteria')) {
			//$typeParts = explode("::", @html_entity_decode($search_type, ENT_COMPAT, 'UTF-8'));
			
		
			
			$searchType = new HarmoniType("Agent & Group Search", "edu.middlebury.harmoni", "TokenSearch");
			//$searchType = new HarmoniType("Agent & Group Search", "edu.middlebury.harmoni", "WildcardSearch");
			$string=	"*".$search_criteria."*";
			$agents =$agentManager->getAgentsBySearch($string, $searchType);
			print "search: " . $search_criteria;
			

		
			while ($agents->hasNext()) {
				$agent =$agents->next();
				$id =$agent->getId();

				
			

			$harmoni->history->markReturnURL("polyphony/agents/edit_agent_details");
		
			print "\n<p align='center'><a href='".$harmoni->request->quickURL("agents","edit_agent_details", array("agentId"=>$id->getIdString()))."'>";
			print "\n".$agent->getDisplayName()."</a>";
			print "\n - <a href=\"Javascript:alert('"._("Id:").'\n\t'.addslashes($id->getIdString())."')\">Id</a></p>";
			}
			print "\n</div>";
			
			$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);	
			ob_end_clean();
		
		
		}
	}
	
}
	
	