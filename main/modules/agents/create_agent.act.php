<?php

/**
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: create_agent.act.php,v 1.1 2005/02/14 19:17:24 thebravecowboy Exp $
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
	$displayName = $_REQUEST["display_name"];

	$properties = array();
	$properties["department"] = $_REQUEST["department"];
	
	$result = makeNewAgent($userName, $password, $displayName, $properties);
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
				<input type='text' name='display_name' />
			</td></tr>
			<tr><td>
				Department:
			</td><td>
				<input type='text' name='department' />
			</td></tr>
			</table>	
			<input type='submit' value='Create New User' />			
			</form>";
}


function makeNewAgent($userName, $password, $displayName, & $properties){

	$authNHandler =& Services::getService("Authentication");

	$DBAuth =& $authNHandler->getMethod("dbAuth");

	$newAgent =& $DBAuth->addAgent($userName,$password, $properties, $displayName);
	
	return $newAgent;
}



?>