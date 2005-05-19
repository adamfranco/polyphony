<?php

/**
 * 
 * @package polyphony.modules.agents
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: create_agent.act.php,v 1.5 2005/05/19 15:34:24 thebravecowboy Exp $
 */

// Get the Layout components. See core/modules/moduleStructure.txt
// for more info.
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');

ob_start();

//'form_submitted' is a hidden field in the form, just a switch really
if($_REQUEST["form_submitted"]){
	//this is here to accomodate the expansion to other authentication methods besides username/password
	//basic form checking.  If required fields aren't there, rebuild the form
	if(!$_REQUEST["username"] || !$_REQUEST["password"]){
		print "You must enter a username and password!<br />";
		print createAgentForm();

	}else{
		$userName = $_REQUEST["username"];
		$password = $_REQUEST["password"];
		$displayName = $_REQUEST["display_name"];
	
		$properties = array();
		//creates an array of properties
		foreach($_REQUEST as $key=>$request){
			$key_parts = explode("_",$key);
			if($key_parts[0]=="property"){
				$key_name = $key_parts[1];
				$properties[$key_name] = $_REQUEST[$key];
			}
		}
		
		$agent =& makeNewAgent($userName, $password, $displayName, $properties);

		if($agent){
			print "User ".$agent->getDisplayName()." succesfully created.";
		}else{
			print "Create agent failed.";
			createAgentForm();
		}
	}
}else{
	createAgentForm();
}

// Layout
$centerPane->add(new Block(ob_get_contents(),3), "100%", null, CENTER, TOP);
ob_end_clean();
return $mainScreen;

/****
 * void createAgentForm(void)
 * Print the form to create agents
 * The handler can handle any number of properties but they must be
 * named 'property_'.property_name
 */

function createAgentForm(){
	$authNType =& $GLOBALS["NewUserAuthNType"];
	
	$serializedAuthNType = $authNType->getDomain()."::".$authNType->getAuthority()."::".$authNType->getKeyword();
	
	print "<center><form action='".$_SERVER["PHP_SELF"]."' method='post'>
			Create A New User<br />
			<table>";
	
	//switch($GLOBALS["AuthNMethod"]){
	//	case "dbAuthType":
			print "<tr><td>
				*Username:
			</td><td>
				<input type='text' name='username' />
			</td></tr>
			<tr><td>
				*Password:
			</td><td>
				<input type='password' name='password' />
			</td></tr>";
			
	//		break;
	//}
				
	print "<input type='hidden' name='authn_type' value='$serializedAuthNType' />";
			
	print "	<tr><td>
				Display Name:
			</td><td>
				<input type='text' name='display_name' />
			</td></tr>
			<tr><td>
				Department:
			</td><td>
				<input type='text' name='property_department' />
			</td></tr>
			</table>	
			<input type='submit' value='Create New User' />
			<input type='hidden' name='form_submitted' value='true' />
			</form></center>";
}


function makeNewAgent($userName, $password, $displayName, $propertiesArray){
	$authNMethodManager =& Services::getService("AuthNMethodManager");
	$tokenMappingManager =& Services::getService("AgentTokenMapping");
		
	$authNTypeArray = explode("::", urldecode($_REQUEST['authn_type']));
					
	$authNType =& new HarmoniType($authNTypeArray[0], $authNTypeArray[1], $authNTypeArray[2]);
	
	$newTokensPassed["username"]=$_REQUEST["username"];
	$newTokensPassed["password"]=$_REQUEST["password"];
			
	$authNMethod=& $authNMethodManager->getAuthNMethodForType($authNType);
	$tokens =& $authNMethod->createTokensObject();
	$tokens->initializeForTokens($newTokensPassed);
	$mappingExists=$tokenMappingManager->getMappingForTokens($tokens, $authNType);
	
	if($mappingExists){
		
		print "This username is already in use, please choose another.";
		return false;	
	}		
	$userType =& new HarmoniType("Polyphony", "Users", "TypeForUsers");
	
	$propertyManager =& Services::getService("Property");
	
			
	$propertyObject =& $propertyManager->convertArrayToObject($propertiesArray, $userType);
			
	$agentManager =& Services::getService("Agent");
	
	$agent =& $agentManager->createAgent($displayName, $userType, $propertyObject);

	$id =& $agent->getId();
	
	$mapping =& $tokenMappingManager->createMapping($id, $tokens, $authNType);
		
	return $agent;
}



?>
