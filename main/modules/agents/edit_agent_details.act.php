<?php

/**
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_agent_details.act.php,v 1.16 2006/07/29 06:34:59 sporktim Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");
require_once(POLYPHONY."/main/modules/coursemanagement/suck_by_agent.act.php");
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
 * @version $Id: edit_agent_details.act.php,v 1.16 2006/07/29 06:34:59 sporktim Exp $
 */
class edit_agent_detailsAction 
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
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-agents");
		$agentIdString = $harmoni->request->get("agentId");

		$harmoni->request->endNamespace();
		
		if ($authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify"),
					$idManager->getId($agentIdString)))
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
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("agentId");
		$agentIdString = $harmoni->request->get("agentId");
		$idManager =& Services::getService("Id");
		$agentId =& $idManager->getId($agentIdString);
		$agentManager =& Services::getService("Agent");
		$agent =& $agentManager->getAgent($agentId);
		return dgettext("polyphony", $agent->getDisplayName());
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
		$harmoni->request->passthrough("agentId");
		$agentIdString = $harmoni->request->get("agentId");
		$furtherAction = $harmoni->request->get("furtherAction");

		$idManager =& Services::getService("Id");
		$agentId =& $idManager->getId($agentIdString);
		$agentManager =& Services::getService("Agent");

		//we can't really do anything if its not an editableAgent
		if($agentManager->getAgentFlavor()!="HarmoniEditableAgent"){
			$centerPane->add(new Block("Sorry but this agent isn't editable!", 2), "100%", null, CENTER, TOP);
			return $mainScreen;
		}
		
		$agent =& $agentManager->getAgent($agentId);
		
		ob_start();
		
		print "<div style='margin-left: 15px'>";
		
		if (!$furtherAction) $furtherAction = "edit_agent_detailsAction::viewAgentDetails";
		
		$actionFunctions = array(
			"edit_agent_detailsAction::viewAgentDetails",
			"edit_agent_detailsAction::advancedViewAgentDetails",
			"edit_agent_detailsAction::confirmClearProperties",
			"edit_agent_detailsAction::clearProperties",
			"edit_agent_detailsAction::confirmDeleteAgent",
			"edit_agent_detailsAction::deleteAgent",
			"edit_agent_detailsAction::editAgent",
			"edit_agent_detailsAction::updateProperty",
			"edit_agent_detailsAction::addProperty",
			"edit_agent_detailsAction::deleteProperty",
			"edit_agent_detailsAction::updateDisplayName"
		);
			
		
		if($furtherAction && in_array($furtherAction, $actionFunctions)){
			eval($furtherAction.'($agent);');
		}
		
		print "</div>";
		
		// Layout
		$actionRows->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
		ob_end_clean();
		$harmoni->request->forget("agentId");
		$harmoni->request->endNamespace();
		
		textdomain($defaultTextDomain);
	}

/***************************FUNCTIONS***************************************/

	/**
	 * shows the details of the agent's properties and gives menu of actions
	 * 
	 * @param object Agent $agent
	 * @return void
	 * @access public
	 * @since 7/19/05
	 */
	function viewAgentDetails(&$agent){
		
		suck_by_agentAction::refreshAgentDetails($agent);
		
		$agentId =& $agent->getId();
		$agentIdString = $agentId->getIdString();
		
		//display agent info	
		print "\n<h3>".$agent->getDisplayName()."</h3>";
		print "\n<table><tr><td>";
		print "\n<table bgcolor='#AAAAAA' cellspacing='1' cellpadding='3'>";
				/*<tr bgcolor='#DDDDDD'>
				<td>
				Property
				</td>
				<td>
				Value
				</td>
				</tr>";*/
		
		$propertiesArray = edit_agent_detailsAction::_getUsableProperties($agent);
		
		//show the uneditable list of properties and their types and values
		//foreach($propertiesArray as $key=>$property){
		//		print "<tr bgcolor='#FFFFFF'>
		//			<td>$key</td>
		//			<td>".$property['value']."</td>
		//			<td>".$property['type']."</td>
		//			</tr>";
		//		
		//}
		
		edit_agent_detailsAction::_printRowFromPropertiesArray($propertiesArray,"name", "Name:");
		edit_agent_detailsAction::_printRowFromPropertiesArray($propertiesArray,"username", "Username:");
		edit_agent_detailsAction::_printRowFromPropertiesArray($propertiesArray,"email", "Email address:");
		edit_agent_detailsAction::_printRowFromPropertiesArray($propertiesArray,"department","Department:");
		
			
		
		print "\n</table>";
		print "\n</td><td>";
		//actions menu
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL();

		print "<ul>
				<li><a href='".$url->write("furtherAction","edit_agent_detailsAction::editAgent")."'>Edit agent</a></li>
				<li><a href='";
		$harmoni->request->startNamespace("polyphony-authorizations");
		print $harmoni->request->quickURL("authorization","edit_authorizations",
					array("agentId" => $agentId->getIdString()));
		$harmoni->request->endNamespace();

		print "'>Edit authorizations</a></li>
				<li><a href='".$url->write("furtherAction","edit_agent_detailsAction::confirmClearProperties")."'>Clear properties</a></li>
				<li><a href='".$url->write("furtherAction","edit_agent_detailsAction::confirmDeleteAgent")."'>Delete agent</a></li>
				<li><a href='".$url->write("furtherAction","edit_agent_detailsAction::advancedViewAgentDetails")."'>Advanced View</a></li>
				</ul>";
		print "\n</td></tr></table>";
		
		
		
		
		
		
		
		print "<h3>Classes:</h3>";
		
		//sort the courses by term and the terms by date
		
		$cm =& Services::getService("CourseManagement");
		$offerings =& $cm->getCourseOfferings($agentId);
		
		
		
		edit_agent_detailsAction::printCourseOfferings($offerings);
		
	
		
		
	}
	

	
	/**
	 * shows all the details of the agent's properties and gives menu of actions
	 * 
	 * @param object Agent $agent
	 * @return void
	 * @access public
	 * @since 7/19/05
	 */
	function advancedViewAgentDetails(&$agent){
		
		
		
		$agentId =& $agent->getId();
		$agentIdString = $agentId->getIdString();
		
		//display agent info	
		print "<h3>Details for User: ".$agent->getDisplayName()."</h3>";
		
		print "<table bgcolor='#AAAAAA' cellspacing='1' cellpadding='3'>
				<tr bgcolor='#DDDDDD'>
				<td>
				Property
				</td>
				<td>
				Value
				</td>
				<td>
				Type
				</td>
				</tr>";
					
		$propertiesArray = edit_agent_detailsAction::_getUsableProperties($agent);
		
		//show the uneditable list of properties and their types and values
		foreach($propertiesArray as $key=>$property){
				print "<tr bgcolor='#FFFFFF'>
					<td>$key</td>
					<td>".$property['value']."</td>
					<td>".$property['type']."</td>
					</tr>";
				
		}
			
		
		print "</table>";
		
		//actions menu
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL();
		
		print "<h3>Actions</h3>
				<ul>
				<li><a href='".$url->write("furtherAction","edit_agent_detailsAction::editAgent")."'>Edit agent</a></li>
				<li><a href='";
		
		$harmoni->request->startNamespace("polyphony-authorizations");
		print $harmoni->request->quickURL("authorization","edit_authorizations",
					array("agentId" => $agentId->getIdString()));
		$harmoni->request->endNamespace();
		print "'>Edit authorizations</a></li>
				<li><a href='".$url->write("furtherAction","edit_agent_detailsAction::confirmClearProperties")."'>Clear properties</a></li>
				<li><a href='".$url->write("furtherAction","edit_agent_detailsAction::confirmDeleteAgent")."'>Delete agent</a></li>
				</ul>";
		
		// Groups
		print "<h3>Groups</h3>
				<ul>";
		$agentManager =& Services::getService("Agent");
		$groups =& $agentManager->getGroupsBySearch($agentId, 
					new Type(	"Agent & Group Search", 
								"edu.middlebury.harmoni",
								"AncestorGroups"));
		while ($groups->hasNext()) {
			$group =& $groups->next();
			$groupId =& $group->getId();
			print "\n\t<li title=\"".addslashes($groupId->getIdString())."\">".$group->getDisplayName()."</li>";
		}
		
		print "
				</ul>";
		return;
	}
	
	/***
	 * Offers a confirmation screen for clearing of properties
	 */
	
	function confirmClearProperties(&$agent){
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL();
		print "Do you really want to clear all properties of ".$agent->getDisplayName()."? (this will not reset system name or password)<br />";
		print "<form action='".$url->write("furtherAction","edit_agent_detailsAction::clearProperties")."' method='post'><input type='submit' value='Clear' /></form><input type='button' value='Cancel' onclick='history.back()' />";
		return;
	}
	
	/***
	 * Clears all the properties
	 */
	 
	function clearProperties(& $agent){
		$propertyManager =& Services::getService("Property");
		
		//clear the props
		$agent->clearAllProperties();
		
		//back to the menu
		viewAgentDetails($agent);
		
		return;
			
	}
	
	
	/***
	 * offers a confirmation screen for deleting an entire agent
	 */
	 
	function confirmDeleteAgent(&$agent){
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL();
		print "Do you really want to delete ".$agent->getDisplayName()."?<br />";
		print "<form action='".$url->write("furtherAction","edit_agent_detailsAction::deleteAgent")."' method='post'><input type='submit' value='Delete' /></form><input type='button' value='Cancel' onclick='history.back()' />";
		return;
	}
	
	/***
	 * Handles the actual deletion of an agent
	 */
	 
	function deleteAgent(&$agent){
		$agentManager =& Services::getService("Agent");
		$agentManager->deleteAgent($agent->getId());
		
		$harmoni =& Harmoni::instance();
		print "Agent deleted.<br />";
		print "<a href='".$harmoni->history->getReturnURL("polyphony/agents/edit_agent_details")."'>Go Back</a>";
		
		return;
	}
	
	
	
	/***
	 * displays the screen for editing agents
	 * Type for new property is arbitrarily set as the type of the agent
	 * should probably draw from a list of usable types later on.  
	 * Some of these attributes are not "properties" per se and don't have types
	 * in that case, I've arbitrarily entered "Immutable Reality" under type
	 */
	 
	function editAgent(&$agent){
		
		//to get the username and maybe the password.
		$tokenMappingManager =& Services::getService("AgentTokenMappingManager");
		
		//a properties manager to handle, what else, properties
		$propertiesManager =& Services::getService("Property");
		
		$mappings=& $tokenMappingManager->getMappingsForAgentId($agent->getId());
		
		//there should only be one mapping but what the heck
		while($mappings->hasNextObject()){
			$mapping=& $mappings->nextObject();	
			$tokens =& $mapping->getTokens();
			$userName=$tokens->getUsername();
		}
		
		if (!isset($userName)) { $userName = '&laquo; undefined &raquo;';}
		
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL();
		
		//display name
		print "<h3>Editing User: ".$agent->getDisplayName()."</h3>";
		
		print "<table bgcolor='#AAAAAA' cellspacing='1' cellpadding='3'>";
				
		print "<tr bgcolor='#DDDDDD'>
				<td>Property</td>
				<td>Value</td>
				<td>Type</td>
				<td>Store new value</td>
				<td>Delete property</td>
			   </tr>";
			
		//username
		print "<tr bgcolor='#FFFFFF'>
				 <td><span style='color:red;'>*</span> User Name</td>
				 <td>$userName</td>
				 <td>Immutable Reality</td>
				 <td>N/A</td>
				 <td>N/A</td>
				</tr>";
		// @todo TO DO: Add password editing here. How it's implemented depends strongly on who this interface is for
		print "<tr bgcolor='#FFFFFF'>
				  <td>Password</td>
				  <td>Stored</td>
				  <td>Immutable Reality</td>
				  <td><form><input type='submit' value='Change' /></form></td>
				  <td>N/A</td>
				</tr>";
		
		print "<tr bgcolor='#FFFFFF'>
				  <form action='".$url->write("furtherAction","edit_agent_detailsAction::updateDisplayName")."' method='post'>
				  <td>Display name</td>
				  <td><input type='text' name='".RequestContext::name("display_name")."' value ='".$agent->getDisplayName()."' /></td>
				  <td>Immutable Reality</td>
				  <td><input type='submit' value='Change Display Name' /></td>
				  <td>N/A</td>
				  </form>
				</tr>";
		$type=& $agent->getType();
		
		$propertiesArray = edit_agent_detailsAction::_getUsableProperties($agent);
				
		foreach($propertiesArray as $key=>$property){
			
			$typeParts = explode("::", $property['type']);
				
			print "<tr bgcolor='#FFFFFF'>
					<form action='".$url->write("furtherAction","edit_agent_detailsAction::updateProperty")."' method='post'>
					<td><input type='hidden' name='".RequestContext::name("property_name")."' value='$key' />$key</td>
					<td><input name='".RequestContext::name('property_value')."' value='{$property['value']}' /></td>
					<td>
					
					<input type='hidden' value='{$property['type']}' name='".RequestContext::name("property_type")."' />{$typeParts[2]}
					</td>
					<td>
					<input type='submit' value='Update' />
					</td>
					</form>
					<td>
					<form action='".$url->write("furtherAction","edit_agent_detailsAction::deleteProperty")."' method='post'><input type='submit' value='Delete Property' >
					<input type='hidden' name='".RequestContext::name("property_type")."' value='{$property['type']}' />
					<input type='hidden' name='".RequestContext::name("property_name")."' value='$key' />
					</form>
					</td>
					</tr>";
				
		}
		print "<tr bgcolor='#DDDDDD'>
				<form action='".$url->write("furtherAction","edit_agent_detailsAction::addProperty")."' method='post'>
				<td colspan='5'>
					Add New Property
				</td>
				<tr bgcolor='#FFFFFF'>
				<td>
				<input type='text' name='".RequestContext::name("name")."' />
				
				</td>
				<td>
				<input type='text' name='".RequestContext::name("value")."' />
				</td>
				<td>
				<input type='hidden' name='".RequestContext::name("property_type")."' value='".$type->getDomain()."::".$type->getAuthority()."::".$type->getKeyword()."' />
				Type
				</td>
				<td>
				<input type='submit' value='Add Property' />
				
				</td>
				</form>
				<td>
				</td>
				
				</tr>";
				
		print "<tr bgcolor='#DDDDDD'>
				<td colspan='5' align='right'><a href='".$harmoni->request->quickURL()."'><input type='button' value='Go Back'/></a></td>
				
				</tr>";
				
		print "</table>";
		print "<br /><span style='color: red'>*</span> The system name may only be altered by creating a new user.";
				
		return;
		
		
	}
	
	/****
	 * Updates property from the edit agent form
	 */
	
	function updateProperty(&$agent){
		$propertyKey = RequestContext::value("property_name");
		$propertyValue = RequestContext::value("property_value");
		
		//break the type so we can create an object
		$propertyTypeArray = explode("::",RequestContext::value("property_type"));
		
		//create type object
		$propertyType =& new HarmoniType($propertyTypeArray[0], $propertyTypeArray[1], $propertyTypeArray[2]);
		
		//update the agent propreties
		if($agent->updateProperty($propertyType, $propertyKey, $propertyValue)){
			print ucfirst($propertyKey)." updated succesfully.";
		}else{
			print "Failed to update $propertyKey.";
		}
		
		//back to the form
		editAgent($agent);
		
		return;
	}
	
	
	/***
	 * Adds a property to the agent
	 */
	 
	 function addProperty(&$agent){
		$propertyName = RequestContext::value('name');
		$propertyValue = RequestContext::value('value');
		
		//create the type object
		$typeArray = explode("::", RequestContext::value('property_type'));
		$type =& new HarmoniType($typeArray[0], $typeArray[1], $typeArray[2]);
		
		if($agent->addProperty($type, $propertyName, $propertyValue)){
			print ucfirst($propertyName)." added to ".$agent->getDisplayName();
		}else{
			print "Failed to add property.";
		}
		
		//back to the editing form
		editAgent($agent);
		
		return;
	 }
	
	/***
	 * Deletes a property from the agent. Duh.
	 */
	 
	 function deleteProperty(&$agent){
		$propertyName = RequestContext::value('property_name');
		$propertyType = explode("::", RequestContext::value('property_type'));
		
		$type =& new HarmoniType($propertyType[0], $propertyType[1], $propertyType[2]);
	
		if($agent->deleteProperty($type, $propertyName)){
			print "Deleted property.";
		}else{
			print "Could not delete property.";
		}
		
		editAgent($agent);
		
		return;
	 
	 }
	 
	 /***
	  * Stores a new display name for the agent
	  */
	  
	  function updateDisplayName(&$agent){
		if(!RequestContext::value('display_name')){
			print "If you want to update the display name you'll need to enter a new one!";
			editAgent($agent);
			return false;
		}
			
		if($agent->updateDisplayName(RequestContext::value('display_name'))){
			print "Display name changed to ".$agent->getDisplayName();
		}else{
			print "Failed to change display name.";
		}
		
		editAgent($agent);
		return;
		
	  }
	
	
	/***
	 * creates a two dimensional array of key (value/type) pairs, 
	 * preserving most of the information from the object modify at will
	 */
			
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
	
	/***
	 * Tries to print a key value pair from the propertiesArray 
	 * from getUsableProperties in a table.
	 * It ignores type, and uses an alias for the key
	 */
			
	function _printRowFromPropertiesArray($propertiesArray, $key, $keyAlias){
		print "\n\t<tr>";
		print "\n\t\t<td>".$keyAlias."</td>";
		print "\n\t\t<td>";
		if(array_key_exists($key,$propertiesArray)){
			print $propertiesArray[$key]['value'];	
		}
		print "</td>";
		print "\n\t</tr>";
	}
	
		
	/***
	 * Print the course offering
	 */
			
	function _printOffering($offering, $term){
		
		
		
		if(!is_null($term)){
			//print "\n</table>";
			print "\n\t<tr>";
			print "\n<td><hr></td>";
			print "\n<td><hr></td>";
			print "\n\t\t</tr>";
			print "\n\t<tr>";
			print "\n\t\t<td><h4>";
			print $term->getDisplayName();
			print "</h4</td>";		
		
		}else{
			print "\n\t<tr>";
			print "\n\t\t<td>";
			
			print "</td>";		
		}
		
		$harmoni =& Harmoni::instance();
		$id =& $offering->getId();
		print "\n\t\t<td>";
		print "\n<a href='".$harmoni->request->quickURL("coursemanagement","edit_offering_details", array("offeringId"=>$id->getIdString()))."'>";
		print $offering->getDisplayName()."</a>";		
		print "\n - <a href=\"Javascript:alert('"._("Id:").'\n\t'.addslashes($id->getIdString())."')\">"._("Id")."</a>";
		print "</td>";
		//print "\n\t\t<td>";
		//$term =& $offering->getTerm();
		//print $term->getDisplayName();
		//print "</td>";		
		print "\n\t</tr>";
		
		
		
	}
	
	function printCourseOfferings(&$offerings){
		
		$offerings2 = array();
		$courseNum = 0;	
		
		
		
		
		while($offerings->hasNextCourseOffering()){

		
			
			$offering =& $offerings->nextCourseOffering();
			$term =& $offering->getTerm();
			$schedule =& $term->getSchedule();
			if($schedule->hasNextScheduleItem()){
				$item1 =& $schedule->nextScheduleItem();
				$offerings2[$item1->getStart()+$courseNum] =&  $offering;
	
			}else{
				$offerings2[$courseNum] =& $offering;
				
			}
			$courseNum++;
	
		}
		
		krsort($offerings2);
		
			print "\n<table cellpadding=6>";
		
	$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-agents");
	
	
		$lastTermId = null;
		foreach($offerings2 as 	$offering){
						
			$term=$offering->getTerm();
			$termId =& $term->getId();
			if(!is_null($lastTermId)&&$termId->isEqual($lastTermId)){
				edit_agent_detailsAction::_printOffering($offering,null);
			}else{
				edit_agent_detailsAction::_printOffering($offering,$term);
			}
			$lastTermId =& $termId;
			
		}
		
		
		
		print "\n</table>";
	}
	
	
}