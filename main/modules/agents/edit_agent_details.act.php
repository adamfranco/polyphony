<?php
/**
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_agent_details.act.php,v 1.4 2005/06/01 19:33:35 gabeschine Exp $
 */
 
// Get the Layout components. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');

$agentIdString = $harmoni->pathInfoParts[2];
$furtherAction = $harmoni->pathInfoParts[3];

$idManager =& Services::getService("Id");
$agentId =& $idManager->getId($agentIdString);
$agentManager =& Services::getService("Agent");

//we can't really do anything if its not an editableAgent
if($agentManager->getAgentFlavor!="HarmoniEditableAgent"){
	print "Sorry but these agents aren't editable!";
	return;
}

$agent =& $agentManager->getAgent($agentId);

ob_start();

print "<div style='margin-left: 15px'>";

if($furtherAction){
	$furtherAction($agent, $harmoni->pathInfoParts);
}else{
	$_SESSION["callingList"] = $harmoni->request->get("callingFrom");
	viewAgentDetails($agent, $harmoni->pathInfoParts);
	
}
print "</div>";
// Layout
$centerPane->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
ob_end_clean();
return $mainScreen;


/***************************FUNCTIONS***************************************/

/***
 * shows the details of the agent's properties and gives menu of actions
 */

function viewAgentDetails(&$agent, $pathParts){
	
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
	print "<h3>Actions</h3>
			<ul>
			<li><a href='".MYURL."/".implode("/", $pathParts)."/editAgent/'>Edit agent</a></li>
			<li><a href='".MYURL."/authorization/edit_authorizations/?agent=$agentIdString'>Edit authorizations</a></li>
			<li><a href='".MYURL."/".implode("/", $pathParts)."/confirmClearProperties/'>Clear properties</a></li>
			<li><a href='".MYURL."/".implode("/",$pathParts)."/confirmDeleteAgent/'>Delete agent</a></li>
			</ul>";
	return;
}

/***
 * Offers a confirmation screen for clearing of properties
 */

function confirmClearProperties(&$agent, $pathParts){
	unset($pathParts[3]);//don't want to come back to this screen
	print "Do you really want to clear all properties of ".$agent->getDisplayName()."? (this will not reset system name or password)<br />";
	print "<form action='".MYURL."/".implode("/",$pathParts)."/clearProperties/'><input type='submit' value='Clear' /></form><input type='button' value='Cancel' onClick='history.back()' />";
	return;
}

/***
 * Clears all the properties
 */
 
function clearProperties(& $agent, $pathParts){
	$propertyManager =& Services::getService("Property");
	
	//clear the props
	$agent->clearAllProperties();
	
	//back to the menu
	viewAgentDetails($agent, $pathParts);
	
	return;
		
}


/***
 * offers a confirmation screen for deleting an entire agent
 */
 
function confirmDeleteAgent(&$agent, $pathParts){
	unset($pathParts[3]);
	print "Do you really want to delete ".$agent->getDisplayName()."?<br />";
	print "<form action='".MYURL."/".implode("/",$pathParts)."/deleteAgent/' ><input type='submit' value='Delete' /></form><input type='button' value='Cancel' onClick='history.back()' />";
	return;
}

/***
 * Handles the actual deletion of an agent
 */
 
function deleteAgent(&$agent, $pathParts){
	$agentManager =& Services::getService("Agent");
	$agentManager->deleteAgent($agent->getId());
		
	print "Agent deleted.<br />";
	if($_SESSION["callingList"]=="choose_agent"){
		print "<a href='".MYURL."/authorization/choose_agent/'>Back to Edit Authroizations</a>";
	}else{
		print "<a href='".MYURL."/agents/group_membership'>Back to Edit Group Membership</a>";
	}
	
	return;
}



/***
 * displays the screen for editting agents
 * Type for new property is arbitrarily set as the type of the agent
 * should probably draw from a list of usable types later on.  
 * Some of these attributes are not "properties" per se and don't have types
 * in that case, I've arbitrarily entered "Immutable Reality" under type
 */
 
function editAgent(&$agent, $pathParts){
	array_pop($pathParts);//the last element of the array will be the call to this function which we don't want to persist
	
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
	
	//display name
	print "<h3>Editting User: ".$agent->getDisplayName()."</h3>";
	
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
	//TODO TO DO: Add password editting here. How it's implemented depends strongly on who this interface is for
	print "<tr bgcolor='#FFFFFF'>
			  <td>Password</td>
			  <td>Stored</td>
			  <td>Immutable Reality</td>
			  <td><form><input type='submit' value='Change' /></form</td>
			  <td>N/A</td>
			</tr>";
	
	print "<tr bgcolor='#FFFFFF'>
		  	  <form action='".MYURL."/".implode("/", $pathParts)."/updateDisplayName/' method='post'>
			  <td>Display name</td>
			  <td><input type='text' name='display_name' value ='".$agent->getDisplayName()."' /></td>
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
				<form action='".MYURL."/".implode("/", $pathParts)."/updateProperty/' method='post'>
				<td><input type='hidden' name='property_name' value='$key' />$key</td>
				<td><input name='property_value' value='{$property['value']}' /></td>
				<td>
				
				<input type='hidden' value='{$property['type']}' name='property_type' />{$typeParts[2]}
				</td>
				<td>
				<input type='submit' value='Update' />
				</td>
				</form>
				<td>
				<form action='".MYURL."/".implode("/", $pathParts)."/deleteProperty/' method='post'><input type='submit' value='Delete Property' >
				<input type='hidden' name='property_type' value='{$property['type']}' />
				<input type='hidden' name='property_name' value='$key' />
				</form>
				</td>
				</tr>";
			
	}
	print "<tr bgcolor='#DDDDDD'>
			<form action='".MYURL."/".implode("/", $pathParts)."/addProperty/' method='post'>
			<td colspan='5'>
				Add New Property
			</td>
			<tr bgcolor='#FFFFFF'>
			<td>
			<input type='text' name='name' />
			
			</td>
			<td>
			<input type='text' name='value' />
			</td>
			<td>
			<input type='hidden' name='property_type' value='".$type->getDomain()."::".$type->getAuthority()."::".$type->getKeyword()."' />
			Type
			</td>
			<td>
			<input type='submit' value='Add Property' />
			
			</td>
			</form>
			<td>
			</td>
			
			</tr>";
			
	print "</table>";
	print "<br /><span style='color: red'>*</span> The system name may only be altered by creating a new user.";
			
	return;
	
	
}

/****
 * Updates property from the edit agent form
 */

function updateProperty(&$agent, $pathParts){
	$propertyKey = $_REQUEST["property_name"];
	$propertyValue = $_REQUEST["property_value"];
	
	//break the type so we can create an object
	$propertyTypeArray = explode("::",$_REQUEST["property_type"]);
	
	//create type object
	$propertyType =& new HarmoniType($propertyTypeArray[0], $propertyTypeArray[1], $propertyTypeArray[2]);
	
	//update the agent propreties
	if($agent->updateProperty($propertyType, $propertyKey, $propertyValue)){
		print ucfirst($propertyKey)." updated succesfully.";
	}else{
		print "Failed to update $propertyKey.";
	}
	
	//back to the form
	editAgent($agent, $pathParts);			
	
	return;
}


/***
 * Adds a property to the agent
 */
 
 function addProperty(&$agent, $pathParts){
 	$propertyName = $_REQUEST['name'];
 	$propertyValue = $_REQUEST['value'];
 	
 	//create the type object
 	$typeArray = explode("::", $_REQUEST['property_type']);
 	$type =& new HarmoniType($typeArray[0], $typeArray[1], $typeArray[2]);
 	
 	if($agent->addProperty($type, $propertyName, $propertyValue)){
 		print ucfirst($propertyName)." added to ".$agent->getDisplayName();
 	}else{
 		print "Failed to add property.";
 	}
 	
 	//back to the editting form
 	editAgent($agent, $pathParts);
 	
 	return;
 }

/***
 * Deletes a property from the agent. Duh.
 */
 
 function deleteProperty(&$agent, $pathParts){
 	$propertyName = $_REQUEST['property_name'];
 	$propertyType = explode("::", $_REQUEST['property_type']);
 	
 	$type =& new HarmoniType($propertyType[0], $propertyType[1], $propertyType[2]);

	if($agent->deleteProperty($type, $propertyName)){
		print "Deleted property.";
	}else{
		print "Could not delete property.";
	}
	
	editAgent($agent, $pathParts);
	
	return;
 
 }
 
 /***
  * Stores a new display name for the agent
  */
  
  function updateDisplayName(&$agent, $pathParts){
  	if(!$_REQUEST['display_name']){
  		print "If you want to update the display name you'll need to enter a new one!";
		editAgent($agent, $pathParts);
		return false;
	}
		
  	if($agent->updateDisplayName($_REQUEST['display_name'])){
  		print "Display name changed to ".$agent->getDisplayName();
  	}else{
  		print "Failed to change display name.";
  	}
  	
  	editAgent($agent, $pathParts);
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
