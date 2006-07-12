<?php

/**
 * @package polyphony.modules.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: suck_by_agent.act.php,v 1.2 2006/07/12 03:53:36 sporktim Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");


class suck_by_agentAction 
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
		
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-agents");

		$agentManager =& Services::getService("Agent");
		$idManager = Services::getService("Id");
		$cm =& Services::getService("CourseManagement");
		
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
			<select name='$search_type_name'>
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
			print ">".$type->getKeyword()."</option>";
		}
		
			print "\n\t</select>";
			print "\n\t<input type='submit' value='"._("Search")."' />";
			print "\n\t<a href='".$harmoni->request->quickURL()."'>";
			print "<input type='button' value='"._("Clear")."' /></a>";
		print "\n</div>\n</form>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		
		/*********************************************************
		 * the agent links
		 *********************************************************/
		
		 
		 	
		//$pageRows->add(new Heading(_("Agents to Choose".$term->getDisplayName().""), 2), "100%", null, LEFT, CENTER);
		
		 
		if (($search_criteria = $harmoni->request->get('search_criteria')) && ($search_type = $harmoni->request->get('search_type'))) {
			
			
			ob_start();
			
			$typeParts = explode("::", @html_entity_decode($search_type, ENT_COMPAT, 'UTF-8'));
			$searchType =& new HarmoniType($typeParts[0], $typeParts[1], $typeParts[2]);
			$agents =& $agentManager->getAgentsBySearch($search_criteria, $searchType);
			print "search: " . $search_criteria;
		
			
		$self = $harmoni->request->quickURL();
		$lastCriteria = $harmoni->request->get("agent_id");
		$agent_name = RequestContext::name("agent_id");
	
		
		
		print <<<END
		<form action='$self' method='post'>
			<div>
			<p align='center'>
			<select name='$agent_name'>
END;
		$searchTypes =& $agentManager->getAgentSearchTypes();
		while ($agents->hasNext()) {
			$agent =& $agents->next();
			
			$id =& $agent->getId();
			$idString = $id->getIdString();
			
			
			print "\n\t\t<option value='".$idString."'";
			if ($harmoni->request->get("agent_id") == $idString)
				print " selected='selected'";
			print ">".$agent->getDisplayName()."</option>";
		}
		
			print "\n\t</select>";
			print "\n\t<input type='submit' value='"._("Suck Agent's info")."' />";
		
			
		print "\n</p></div>\n</form>";
				print "\n  <p align='center'>Sucking may take a few minutes</p>";
			
			
			
		
			
			$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		}
		
		
		if ($agentIdString = $harmoni->request->get('agent_id')) {
			
			
			ob_start();
		
			$id =& $idManager->getId($agentIdString);
	
			$agent =& $agentManager->getAgent($id);
		
			$this->refreshAgentDetails($agent);
			
			$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
			ob_end_clean();
		}
		
		
		
	}
	
	
	function refreshAgentDetails(&$agent){
		
		
		$agentManager =& Services::getService("Agent");
			$idManager = Services::getService("Id");
		$classId =& $idManager->getId("OU=Classes,OU=Groups,DC=middlebury,DC=edu ");
		$classes =& $agentManager->getGroup($classId);
		
		
		$agentId =& $agent->getId();
		$agentIdString = $agentId->getIdString();
		
		//display agent info	
		print "<h3>Classes for User: ".$agent->getDisplayName()."</h3>";
		
		
		
		
		
		// Groups
		//print "<h3>Classes</h3>";
		$agentManager =& Services::getService("Agent");
		$groups =& $agentManager->getGroupsBySearch($agentId, 
					new Type(	"Agent & Group Search", 
								"edu.middlebury.harmoni",
								"AncestorGroups"));
		while ($groups->hasNext()) {
			$group =& $groups->next();
			$groupId =& $group->getId();
			$idString = $groupId->getIdString();
			
			//print "\n\t<p> ".substr($idString,strlen($idString)-42,42)."</p>";
			
			
			if(substr($idString,strlen($idString)-42,42)!=",OU=Classes,OU=Groups,DC=middlebury,DC=edu"){
				continue;	
			}
			
			//filter out semesters
			$name = $group->getDisplayName();
			if(substr($name,strlen($name)-4,1)!="-"){
				continue;	
			}
			print "\n\t<br> ".$group->getDisplayName()."</br>";
			
			
			
			
			
			//print "\n\t<p> ".."</p>";
			//print "\n\t<p> ".$group->getDisplayName()." aka ".$groupId->getIdString()."</p>";
		}
		
	
		return;
		
		
		
	}
	
	
	
	
	
	
	function &_getTerm($termName ){
		
		$cm =& Services::getService("CourseManagement");
		
		$season =  substr($termName,0,strlen($termName)-2);		
		$year = '20'.substr($termName,strlen($termName)-2,2);
		$name = $season." ".$year;
		
		$termType =& new Type("Coursemanagement","edu.middlebury",$season);
		
		$index = $cm->_typeToIndex('term',$termType);
		
		
		
		
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_term');
		$query->addWhere("name='".addslashes($name)."'");
		$query->addWhere("fk_cm_term_type='".addslashes($index)."'");
		$query->addColumn('id');
		$res=& $dbHandler->query($query);



		if($res->getNumberOfRows()==0){
			
			
			
			$term =& $cm->createTerm($termType,$arr=array());
			$term->updateDisplayName($name);
			
			return $term;
			//$termId =& $term->getId();

			//return $termId->getIdString();
			
		}else{
				
			$row = $res->getCurrentRow();
		
			$idManager =& Services::getService("Id");
			$id =& $idManager->getId($row['id']);
			$term =& $cm->getTerm($id);
			
			return $term;

		}		
	}
	
	function &_getCanonicalCourse($courseString ){
		
		$cm =& Services::getService("CourseManagement");
		
				
		//$num = substr($courseString,4,4);
		$number = substr($courseString,0,strlen($courseString)-5);
		
		
		
		
		
		
		
		
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_can');
		$query->addWhere("number='".addslashes($number)."'");
		$query->addColumn('id');
		$res=& $dbHandler->query($query);


		

		if($res->getNumberOfRows()==0){
			
		
			
			//$termType =& new Type("Coursemanagement","edu.middlebury",$season);		
			//$index = $cm->_typeToIndex('term',$termType);
			
			$dept =  substr($courseString,0,strlen($courseString)-9);
			
			$type =& new Type("Coursemanagement","edu.middlebury",$dept);
			$stattype =& new Type("Coursemanagement","edu.middlebury","default");
			
			$can =& $cm->createCanonicalCourse($number,$number,"",$type,$stattype,1);
			
		
			
			print "<font size=4><b>".$number."</b> </font>\n";
			return $can;
			//$canId =& $can->getId();

			//return $canId->getIdString();
		}else{
			print " ";
			
			$row = $res->getCurrentRow();
			//$the_index = $row['id'];
			$idManager =& Services::getService("Id");
			$id =& $idManager->getId($row['id']);
			$can =& $cm->getCanonicalCourse($id);
			
			print "<font size=4>".$number." </font>\n";
			
			return $can;
			

		}		
	}
	
	/**
	* copied stright from edit_agent_detailsAction in the agent module
	**/
	function _getUsableProperties(&$agent){
		$propertiesArray=array();
		
		$propertiesIterator =& $agent->getProperties();
		$i=0;
		while($propertiesIterator->hasNext()){
			
			$property =& $propertiesIterator->next();
			
			$type=& $property->getType();
			$typeString = $type->getDomain()."::".$type->getAuthority()."::".$type->getKeyword();
			
			$keys =& $property->getKeys();
			
			while($keys->hasNext()){
				
				$key=& $keys->next();
				$propertiesArray[$key]['value'] = $property->getProperty($key);
				$propertiesArray[$key]['type'] = $typeString;
			}
			
				
		}
		
		return $propertiesArray;	
	}
	
}
	
	