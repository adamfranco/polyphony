<?php

/**
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: create_agent.act.php,v 1.3 2005/02/28 22:04:54 thebravecowboy Exp $
 */
 
// Get the Layout components. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
ob_start();

if(!$_REQUEST["username"] || !$_REQUEST["password"]){
	print createAgentForm();
}else{
	$userName = $_REQUEST["username"];
	$password = $_REQUEST["password"];
	
	$properties = array();
	
	foreach($_REQUEST as $key=>$request){
		$key_parts = explode("_",$key);
		if($key_parts[0]=="property"){
			$key_name = $key_parts[1];
			$properties[$key_name] = $_REQUEST[$key];
		}
	}
		
	$result = makeNewAgent($userName, $password, $properties);
	if($result){
		print "User succesfully created.";
	}else{
		createAgentForm();
	}
}

// Layout
$centerPane->addComponent(new Content(ob_get_contents()), TOP, CENTER);
ob_end_clean();
return $mainScreen;

function createAgentForm(){
	print "<form action='".$_SERVER["PHP_SELF"]."' method='post'>
			Create A New User<br />
			<table>
			<tr><td>
				*Username: 
			</td><td>
				<input type='text' name='username' />
			</td></tr>
			<tr><td>
				*Password: 
			</td><td>
				<input type='password' name='password' />	
			</td></tr>
			<tr><td>
				Display Name:
			</td><td>
				<input type='text' name='property_displayName' />
			</td></tr>
			<tr><td>
				Department:
			</td><td>
				<input type='text' name='property_department' />
			</td></tr>
			</table>	
			<input type='submit' value='Create New User' />			
			</form>";
}


function makeNewAgent($userName, $password, & $properties){

	$authNHandler =& Services::getService("Authentication");

	$DBAuth =& $authNHandler->getMethod("dbAuth");

	$newAgent =& $DBAuth->addAgent($userName,$password, $properties);
		
	return $newAgent;
}



?>