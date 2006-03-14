<?php

/**
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_properties.act.php,v 1.6 2006/03/14 22:07:36 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");

/**
 * This action allows for the editing of properties in agents.
 *
 * @since 11/10/04 
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_properties.act.php,v 1.6 2006/03/14 22:07:36 cws-midd Exp $
 */
class edit_propertiesAction 
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
		
		$list = $this->_getAgentList();
		
		foreach($list as $idString) {
			if (!$authZManager->isUserAuthorized($idManager->getId("edu.middlebury.authorization.modify_agent"),
															$idManager->getId($idString))) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "Edit Agent Properties");
	}
	
	var $_agentList = null;
	function _getAgentList() {
		if ($this->_agentList != null) return $this->_agentList;
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-agents");
		
		if (RequestContext::value("mult")) {
			$agentList = unserialize(RequestContext::value("agents"));
		} else {
			$agentList = array(RequestContext::value("agentId"));
		}
		
		$harmoni->request->endNamespace();
		$this->_agentList = $agentList;
		return $agentList;
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
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("agentId", "mult", "agents");
		
		// get a lsit of agents that cannot be edited
		$agentManager =& Services::getService("Agent");
		
		//we can't really do anything if its not an editableAgent
		if($agentManager->getAgentFlavor()!="HarmoniEditableAgent"){
			$actionRows->add(new Block("Sorry but agents aren't editable! (property of AgentManager)", 3), "100%", null, CENTER, TOP);
			return;
		}
		
		$this->runWizard("edit_properties", $actionRows);

		$harmoni->request->endNamespace();
		
		textdomain($defaultTextDomain);
	}
	
	function &createWizard() {
		$list = $this->_getAgentList();
		if (count($list) > 1) {
			$string = dgettext("polyphony", "You are editing properties for multiple agents. Existing values are not displayed unless they are the same for all agents. To change a value, be sure to select the checkbox next to the field to indicate you want the field updated.");
		} else {
			$string = dgettext("polyphony", "You are editing properties for one agent. Existing values are displayed in the fields.");
		}
		
		$wizardText = <<< END
<div style='font-weight: bolder'>$string</div>

<div>
[[properties]]
</div>

<div align='right'>
[[_cancel]] [[_save]]
</div>
END;

		$wizard =& SimpleWizard::withText($wizardText);

		// get a lsit of agents that cannot be edited
		$idManager =& Services::getService("Id");
		$agentManager =& Services::getService("Agent");
		
		$values = array();
		$valuesSame = array();
		$valueCount = array();
		
		foreach($list as $idString) {
			$idObj =& $idManager->getId($idString);
			$agent =& $agentManager->getAgentOrGroup($idObj);
			$properties =& $agent->getProperties();
			
			// put this agent's properties into an array.
			$propArray = array();
			while($properties->hasNext()) {
				$propObj =& $properties->next();
				$typeObj =& $propObj->getType();
				$typeString = Type::typeToString($typeObj);
				if (!isset($propArray[$typeString])) {
					$propArray[$typeString] = array();
				}
				$keys =& $propObj->getKeys();
				while($keys->hasNext()) {
					$key =& $keys->next();
					
					$propArray[$typeString][$key] = $propObj->getProperty($key);
				}
			}
			
			// now go through all the keys we've seen and check if they are the same.
			$types = array_unique(array_merge(array_keys($propArray), array_keys($values)));
			foreach ($types as $typeString) {
				if (!isset($values[$typeString])) {
					$values[$typeString] = array();
					$valuesSame[$typeString] = array();
					$valueCount[$typeString] = array();
				}
				if (!isset($propArray[$typeString])) $propArray[$typeString] = array();
				$keys = array_unique(array_merge(array_keys($propArray[$typeString]), array_keys($values[$typeString])));
				foreach ($keys as $key) {
					if (!isset($valueCount[$typeString][$key])) $valueCount[$typeString][$key] = 0;
					if (!isset($values[$typeString][$key])) {
						$valuesSame[$typeString][$key] = true;
						$values[$typeString][$key] = $propArray[$typeString][$key];
						$valueCount[$typeString][$key]++;
						continue;
					}
					if ($valuesSame[$typeString][$key] === false) continue;
					$theValue = isset($propArray[$typeString][$key])?$propArray[$typeString][$key]:null;
					if ($theValue) $valueCount[$typeString][$key]++;
					if ($values[$typeString][$key] != $theValue) {
						$valuesSame[$typeString][$key] = false;
					}
				}
			}
			
		}
		
		$wizard->addComponent("_save", WSaveButton::withLabel(dgettext("polyphony", "Update")));
		$wizard->addComponent("_cancel", new WCancelButton());
		
		$collection =& $wizard->addComponent("properties", 
			new WAddFromListRepeatableComponentCollection());
		$collection->setStartingNumber(0);
		$propertyManager =& Services::getService("Property");
		$collection->addOptionCollection(dgettext("polyphony", "New Key..."), $value = null);
		unset($array);
		$allProperties = $propertyManager->getAllPropertyKeys();
		foreach ($allProperties as $key) {
			$collection->addOptionCollection($key, $array = array(
				'key'=>$key,
				'update'=>false,
				'value'=>''));
			unset($array);
		}
		
		$keyComponent =& $collection->addComponent("key", new WTextField());
		$keyComponent->setSize(15);
		$collection->addComponent("type", new WHiddenField());
		$typeText =& $collection->addComponent("type_text", new WText());
		$typeText->setStyle("color: #666;");
		$valueComponent =& $collection->addComponent("value", new WTextField());
		$valueComponent->setSize(40);
		if (count($list) > 1) {
//			$valueComponent->setOnChange("alert(this.id+'_update_dummy');");
			$valueComponent->setOnChange(
				"if (this.value != '' && this.value != '".dgettext("polyphony", "(multiple values exist)")."')" .
				"{" . 		
					"getWizardElement(this.id+'_update').value = '1';" .
					"getWizardElement(this.id+'_update_dummy').checked = true;" .
				"}");
			$collection->addComponent("value_update", new WCheckBox());
		}

		// now add the default values for all of these
		foreach(array_keys($values) as $typeString) {
			$typeArray = $values[$typeString];
			$typeSameArray = $valuesSame[$typeString];
			$typeObj =& Type::fromString($typeString);
			
			// now the keys
			foreach(array_keys($typeArray) as $key) {
				$valuesArray = array(
					'key'=>$key,
					'type'=>$typeString,
					'type_text'=>"(".$typeObj->getKeyword().")",
					'update'=>false,
					'value'=>''
				);
								
				$newSet =& $collection->addValueCollection($valuesArray);
				$newSet["key"]->setReadOnly(true);
				$newSet["key"]->setStyle("border: 0px;"); // <-- not sure if this actually works as desired.
				// if the values are the same and there are as many values as there are agents (otherwise, some didn't have a value),
				// add the value in to the display.
				if ($typeSameArray[$key] && $valueCount[$typeString][$key] == count($list))
					$newSet['value']->setValue($typeArray[$key]);
				else
					$newSet['value']->setStartingDisplayText(dgettext("polyphony", "(multiple values exist)"));
			}
		}
		
		if (count($values) == 0) $collection->setStartingNumber(1);
		
		if (count($list) > 1) {
			$collection->setElementLayout("
<table width='100%'><tr>
	<td>[[value_update]]</td>
	<td>[[key]]</td>
	<td>[[type_text]]</td>
	<td><span style='font-weight: bolder; font-size: larger;'>=</span></td>
	<td align='right'>[[value]]</td>
	<td>[[type]]</td>
</tr></table");
		} else {
			$collection->setElementLayout("
<table width='100%'><tr>
	<td>[[key]]</td>
	<td>[[type_text]]</td>
	<td><span style='font-weight: bolder; font-size: larger;'>=</span></td>
	<td align='right'>[[value]]</td>
	<td>[[type]]</td>
</tr></table>");
		}
		return $wizard;
	}
	
	function saveWizard($cacheName) {
		$wizard =& $this->getWizard($cacheName);
		$values = $wizard->getAllValues();
		$props = $values["properties"];
		$list = $this->_getAgentList();
		
		print_r($props);
		
		$agentManager =& Services::getService("Agent");
		$idManager =& Services::getService("Id");
		
		$valuesHandled = array();
		
		// go through each agent and update all its properties at once
		foreach ($list as $idString) {
			// first clear all their properties, then reset them
			$id =& $idManager->getId($idString);
			$agent =& $agentManager->getAgentOrGroup($id);
			if (count($list) == 1) $agent->deleteAllProperties();
			
			foreach($props as $values) {
				if ($values['type'])
					$type = Type::fromString($values['type']);
				else
					$type = new Type("agent_properties", "harmoni", "custom", 
						"Properties defined outside of an authentication system.");
				
				$valuesHandled[Type::typeToString($type)][$values['key']] = true;
				if (count($list) == 1 || $values['value_update']) {
					$key = $values['key'];
					$value = $values['value'];
					
					if (count($list) == 1 || !$agent->updateProperty($type, $key, $value)) 
						$agent->addProperty($type, $key, $value);
				}
			}
		}
		
		if (count($list) > 1) {
			// now go through each agent and check if there are any properties that were not handled, delete them
			foreach ($list as $idString) {
				$id =& $idManager->getId($idString);
				$agent =& $agentManager->getAgentOrGroup($id);
				$properties =& $agent->getProperties();
				while($properties->hasNext()) {
					$property =& $properties->next();
					$keys = $property->getKeys();
					$type =& $property->getType();
					$typeString = Type::typeToString($type);
					while($keys->hasNext()) {
						$key = $keys->next();
						if (!isset($valuesHandled[$typeString][$key]) || !$valuesHandled[$typeString][$key]) {
							$agent->deleteProperty($type, $key);
						}
					}
				}
			}
		}
//		exit(0);
		return true;
	}
	
	function getReturnUrl() {
		$harmoni =& Harmoni::instance();
		return $harmoni->history->getReturnURL("polyphony/agents/edit_properties");
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
}