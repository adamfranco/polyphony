<?php
/**
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_agent_details.act.php,v 1.7 2005/06/07 12:28:38 gabeschine Exp $
 */
 
$harmoni->request->startNamespace("polyphony-agents"); 

// Get the Layout components. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');

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

if (!$furtherAction) $furtherAction = "viewAgentDetails";

$actionFunctions = array(
	"viewAgentDetails",
	"confirmClearProperties",
	"clearProperties",
	"confirmDeleteAgent",
	"deleteAgent",
	"editAgent",
	"updateProperty",
	"addProperty",
	"deleteProperty",
	"updateDisplayName"
);
	

if($furtherAction && in_array($furtherAction, $actionFunctions)){
	$furtherAction($agent);
}

print "</div>";

// Layout
$centerPane->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
ob_end_clean();
$harmoni->request->endNamespace();
return $mainScreen;


/***************************FUNCTIONS***************************************/

/***
 * shows the details of the agent's properties and gives menu of actions
 */

function viewAgentDetails(&$agent){
	
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
				
	$propertiesArray = _getUsableProperties($agent);
	
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
			<li><a href='".$url->write("furtherAction","editAgent")."'>Edit agent</a></li>
			<li><a href='".$harmoni->request->quickURL("authorization","edit_authorizations")."'>Edit authorizations</a></li>
			<li><a href='".$url->write("furtherAction","confirmClearProperties")."'>Clear properties</a></li>
			<li><a href='".$url->write("furtherAction","confirmDeleteAgent")."'>Delete agent</a></li>
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
	print "<form action='".$url->write("furtherAction","clearProperties")."' method='post'><input type='submit' value='Clear' /></form><input type='button' value='Cancel' onClick='history.back()' />";
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
	print "<form action='".$url->write("furtherAction","deleteAgent")."' method='post'><input type='submit' value='Delete' /></form><input type='button' value='Cancel' onClick='history.back()' />";
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
		  	  <form action='".$url->write("furtherAction","updateDisplayName")."' method='post'>
			  <td>Display name</td>
			  <td><input type='text' name='".RequestContext::name("display_name")."' value ='".$agent->getDisplayName()."' /></td>
			  <td>Immutable Reality</td>
			  <td><input type='submit' value='Change Display Name' /></td>
			  <td>N/A</td>
			  </form>
			</tr>";
	$type=& $agent->getType();
	
	$propertiesArray = _getUsableProperties($agent);
			
	foreach($propertiesArray as $key=>$property){
		
		$typeParts = explode("::", $property['type']);
			
		print "<tr bgcolor='#FFFFFF'>
				<form action='".$url->write("furtherAction","updateProperty")."' method='post'>
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
				<form action='".$url->write("furtherAction","deleteProperty")."' method='post'><input type='submit' value='Delete Property' >
				<input type='hidden' name='".RequestContext::name("property_type")."' value='{$property['type']}' />
				<input type='hidden' name='".RequestContext::name("property_name")."' value='$key' />
				</form>
				</td>
				</tr>";
			
	}
	print "<tr bgcolor='#DDDDDD'>
			<form action='".$url->write("furtherAction","addProperty")."' method='post'>
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
?>
