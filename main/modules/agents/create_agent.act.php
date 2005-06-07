<?php

/**
 * 
 * @package polyphony.modules.agents
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: create_agent.act.php,v 1.8 2005/06/07 20:32:23 gabeschine Exp $
 */

$harmoni->request->startNamespace("polyphony-agents");

// Get the Layout components. See core/modules/moduleStructure.txt
// for more info.
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');

ob_start();

//'form_submitted' is a hidden field in the form, just a switch really
if(RequestContext::value("form_submitted")){

	//basic form checking.  If required fields aren't there, rebuild the form
	if(!RequestContext::value("username") || !RequestContext::value("password")){
		print "You must enter a username and password!<br />";
		print createAgentForm();

	}else{
		$userName = RequestContext::value("username");
		$password = RequestContext::value("password");
		$displayName = RequestContext::value("display_name");
	
		$properties = array();
		//creates an array of properties
		foreach($harmoni->request->getKeys() as $key){
			$key_parts = explode("_",$key);
			if($key_parts[0]=="property"){
				$key_name = $key_parts[1];
				$properties[$key_name] = RequestContext::value($key);
			}
		}
		
		$agent =& makeNewAgent($userName, $password, $displayName, $properties);

		if($agent){
			print "User ".$agent->getDisplayName()." succesfully created.";
			$harmoni->history->goBack("polyphony/agents/create_agent");
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

$harmoni->request->endNamespace();

return $mainScreen;

/****
 * void createAgentForm(void)
 * Print the form to create agents
 * The handler can handle any number of properties but they must be
 * named 'property_'.property_name
 */

function createAgentForm(){
	$harmoni =& Harmoni::instance();
	
	print "<center><form action='".$harmoni->request->quickURL()."' method='post'>
			Create A New User<br />
			<table>";
	
	//switch($GLOBALS["AuthNMethod"]){
	//	case "dbAuthType":
			print "<tr><td>
				*Username:
			</td><td>
				<input type='text' name='".RequestContext::name("username")."' />
			</td></tr>
			<tr><td>
				*Password:
			</td><td>
				<input type='password' name='".RequestContext::name("password")."' />
			</td></tr>";
			
	//		break;
	//}
	
	print "<tr><td>
		*"._("Add to type: ")."
		</td><td>
			<select name='".RequestContext::name("authn_type")."'>";
	$authNManager =& Services::getService("AuthN");
	$typesIterator =& $authNManager->getAuthenticationTypes();
	while($typesIterator->hasNext()) {
		$tempType =& $typesIterator->next();
		$authNMethods =& Services::getService("AuthNMethods");
		$tempMethod =& $authNMethods->getAuthNMethodForType($tempType);
		if (!$tempMethod->supportsTokenAddition()) continue;
		print "<option value='".HarmoniType::typeToString($tempType)."'>".HarmoniType::typeToString($tempType)."</option>";
	}
	print "</select>";
	print "</td></tr>";
		
	print "	<tr><td>
				Display Name:
			</td><td>
				<input type='text' name='".RequestContext::name("display_name")."' />
			</td></tr>
<!--			<tr><td>
				Department:
			</td><td>
				<input type='text' name='".RequestContext::name("property_department")."' />
			</td></tr> -->
			</table>	
			<input type='submit' value='Create New User' />
			<input type='hidden' name='".RequestContext::name("form_submitted")."' value='true' />
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
	$authNType =& HarmoniType::stringToType(RequestContext::value('authn_type'));
		
	//for passing to the token handler
	$newTokensPassed["username"]=RequestContext::value("username");
	$newTokensPassed["password"]=RequestContext::value("password");
	
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
	
	//tell the specific AuthNMethod to add the tokens
	$authNMethod->addTokens($tokens);
	
	return $agent;
}


?>
