<?php

/**
 * 
 * @package polyphony.modules.agents
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: create_agent.act.php,v 1.6 2005/05/19 15:49:46 thebravecowboy Exp $
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
	//if the form hasn't been submitted, print it out
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

/***
 * object Agent makeNewAgent(string $userName, string $passWord, string $displayName, mixed $propertiesArray)
* makes a new agent, creates authentication mappings and so on
*/

function makeNewAgent($userName, $password, $displayName, $propertiesArray){
	//authentication handling
	$authNMethodManager =& Services::getService("AuthNMethodManager");
	$tokenMappingManager =& Services::getService("AgentTokenMapping");
	
	//find the authn type.  This is set in a hidden field in the form at the moment but could easily be changed to a drop down menu	
	$authNTypeArray = explode("::", urldecode($_REQUEST['authn_type']));
	
	//create the type object for the authentication				
	$authNType =& new HarmoniType($authNTypeArray[0], $authNTypeArray[1], $authNTypeArray[2]);
	
	//for passing to the token handler
	$newTokensPassed["username"]=$_REQUEST["username"];
	$newTokensPassed["password"]=$_REQUEST["password"];
	
	//find what authentication method is associated with this type		
	$authNMethod=& $authNMethodManager->getAuthNMethodForType($authNType);
	
	//get tokens object for authentication type
	$tokens =& $authNMethod->createTokensObject();

	//set the values of the tokens to the array we just created
	$tokens->initializeForTokens($newTokensPassed);
	
	//if a mapping already exists, there is alreadya a user with this name
	$mappingExists=$tokenMappingManager->getMappingForTokens($tokens, $authNType);
	
	if($mappingExists){
		
		print "This username is already in use, please choose another.";
		return false;	
	}		
	
	//the type for the user
	$userType =& new HarmoniType("Polyphony", "Users", "TypeForUsers");
	
	//property manager is used for storing properties to the database
	$propertyManager =& Services::getService("Property");
	
	//convert the array of properties we have to a properties object		
	$propertyObject =& $propertyManager->convertArrayToObject($propertiesArray, $userType);
			
	$agentManager =& Services::getService("Agent");
	
	//create the agent entries and build an agent object
	$agent =& $agentManager->createAgent($displayName, $userType, $propertyObject);

	//get the id and create a link between the agent and its authentication info
	$id =& $agent->getId();
	$mapping =& $tokenMappingManager->createMapping($id, $tokens, $authNType);
	
	return $agent;
}



?>
