<?php
/**
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_agent_details.act.php,v 1.2 2005/03/28 23:25:55 nstamato Exp $
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
$agent =& $agentManager->getAgent($agentId);

ob_start();

if($furtherAction){
	$furtherAction($agent, $harmoni->pathInfoParts);
}else{
	$_SESSION["callingList"] = $_REQUEST["callingFrom"];
	viewAgentDetails($agent, $harmoni->pathInfoParts);
	
}

// Layout
$centerPane->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
ob_end_clean();
return $mainScreen;

function viewAgentDetails(&$agent, $pathParts){
	
	
	
	print "<h3>Details for ".$agent->getDisplayName()."</h3>";
	
	print "<table bgcolor='#AAAAAA' cellspacing='1' cellpadding='3'>
			<tr bgcolor='#FFFFFF'>
			<td colspan='2'>
			Properties of Agent
			</td>
			</tr>";
			
	$propertiesArray = _getUsableProperties($agent);
	
	foreach($propertiesArray as $key=>$property){
			print "<tr bgcolor='#FFFFFF'>
				<td>$key</td>
				<td>".$propertiesArray[$key]."</td></tr>";
			
	}
		
	
	print "</table>";
	
	print "<h3>Actions</h3>
			<ul>
			<li><a href='".MYURL."/".implode("/", $pathParts)."/editAgent/'>Edit properties</a></li>
			<li><a href='".MYURL."/authorization/edit_authorizations/?agent=$agentIdString'>Edit authorizations</a></li>
			<li>Delete agent</li>
				<ul>
				<li><a href='".MYURL."/".implode("/", $pathParts)."/confirmDeleteProperties/'>Clear properties</a></li>
				<li><a href='".MYURL."/".implode("/", $pathParts)."/confirmDeleteAgent/'>Delete agent</a></li>
				</ul>
			</ul>";
	return;
}

function confirmDeleteAgent(&$agent, $pathParts){
	unset($pathParts[3]);
	print "Do you really want to delete ".$agent->getDisplayName()."?<br />";
	print "<form action='".MYURL."/".implode("/",$pathParts)."/deleteAgent/' ><input type='submit' value='Delete' /><input type='button' value='Cancel' onClick='history.back()' />";
	return;
}

function deleteAgent(&$agent, $pathParts){
	$agentManager =& Services::getService("Agent");
	$agentManager->deleteAgent($agent->getId());
	print "Agent deleted.<br />";
	if($_SESSION["callingList"]=="choose_agent"){
		print "<a href='".MYURL."/authorization/choose_agent/'>Back to Edit Authroizations</a>";
	}elseif($_SESSION["callingList"]=="group_membership"){
		print "<a href='".MYURL."/agents/group_membership'>Back to Edit Group Membership</a>";
	}
	 
	print "<a href='".MYURL."/agent/' ></a>";
	return;
}

function editAgent(&$agent, $pathParts){
	array_pop($pathParts);//the last element of the array will be the call to this function
	print "<h3>Edit Properties for ".$agent->getDisplayName()."</h3>";
	
	print "<table bgcolor='#AAAAAA' cellspacing='1' cellpadding='3'>";
			
	print "<form action='".MYURL."/".implode("/", $pathParts)."/storeNewProperties/' method='post'>";
	
	$propertiesArray = _getUsableProperties($agent);
	
	foreach($propertiesArray as $key=>$property){
		if($key != "systemName"){
			print "<tr bgcolor='#FFFFFF'>
					<td>$key</td>
					<td><input name='property_".$key."' value='$property' /></td>
					</tr>";
		}else{
			print "<tr bgcolor='#FFFFFF'>
					<td><span style='color: red'>*</span>$key</td>
					<td><input type='hidden' value='property' name='$key' />$property</td>
					</tr>";
		}
				
	
	}
	
	print "</table>
			<input type='submit' value='Submit New Properties' />
			</form>";
	print "<br /><span style='color: red'>*</span> The system name may only be altered by creating a new entry.";
			
	return;
	
	
}

function storeNewProperties(&$agent, $pathParts){
	$properties = array();
	if(!$_REQUEST["systemName"]){
		print "<span style='color: red;'>You must enter a system name</span>";
		print "<br /><a href='javascript: history.back()'>Back</a>";
	}else{
		$systemName = $_REQUEST["property_systemName"];
	}
		
	$keys = array_keys($_REQUEST);
	
	//exclude extraneous form data
	foreach($keys as $key){
		$split_key = explode("_", $key);
		if($split_key[0]!=$key){
			$key_name = $split_key[1];
			$properties[$key_name] = $_REQUEST["properties_".$key_name];
		}	
	}
	
	if(!$properties["displayName"]){
		$properties["displayName"] = $systemName;
	}
	
		
	
	return;
}
		
function _getUsableProperties(&$agent){
	$propertiesArray=array();
	
	$propertiesIterator =& $agent->getProperties();
	
	while($propertiesIterator->hasNext()){
		$property =& $propertiesIterator->next();
		
		$keys =& $property->getKeys();
		
		while($keys->hasNext()){
			$key=& $keys->next();
			$propertiesArray[$key] = $property->getProperty($key);
		}
			
	}
	
	return $propertiesArray;	
}
?>
