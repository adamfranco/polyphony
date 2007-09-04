<?php
/**
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view_agents.act.php,v 1.8 2007/09/04 20:28:11 adamfranco Exp $
 */
 
$harmoni->request->startNamespace("polyphony-agents");

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =$harmoni->getAttachedData('mainScreen');
$statusBar =$harmoni->getAttachedData('statusBar');
$centerPane =$harmoni->getAttachedData('centerPane');
 

// Layout
$yLayout = new YLayout();
$actionRows = new Container($yLayout, OTHER, 1);
$centerPane->add($actionRows, null, null, CENTER, CENTER);

// Intro
$introHeader = new Heading(_("View Agents"), 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);

$agentManager = Services::getService("Agent");


// Get all the agents.
$agents =$agentManager->getAgents();
while ($agents->hasNext()) {
	$agent =$agents->next();
	ob_start();
	$id =$agent->getId();
	print "<strong>".$id->getIdString()." - ";
	print $agent->getDisplayName()."</strong>";
	$agentLayout = new Heading(ob_get_contents(), 2);
	ob_end_clean();
	
	// Also display type information for that agent
	
	ob_start();
	$agentType =$agent->getType();
	print "<em>Authority: </em>".$agentType->getAuthority();
	print "<em><br />Domain: </em>".$agentType->getDomain();
	print "<em><br />Keyword: </em>".$agentType->getKeyword();
	$agentInfoLayout = new Block(ob_get_contents(), 2);
	ob_end_clean();
	
	ob_start();
	print "<em>Description: </em>".$agentType->getDescription();
	$agentDescriptionLayout = new Block(ob_get_contents(), 2);
	ob_end_clean();
		
	$actionRows->add($agentLayout, "100%", null, LEFT, CENTER);
	$actionRows->add($agentInfoLayout, "100%", null, LEFT, CENTER);
	$actionRows->add($agentDescriptionLayout, "100%", null, LEFT, CENTER);
	
	$actionRows->add(new Block(" &nbsp; <br /> &nbsp; <br /> &nbsp;",2), null, null, CENTER, CENTER);
	
/// / 	Now get all the functions of the specified type
// 	$functions =$authZManager->getFunctions($functionType);
// 	while ($functions->hasNext()) {
// 		$function =$functions->next();
// 		
// 	//	Store function info and create layouts.
// 	//	Types consist of {domain :: authority :: type :: description}
// 	//	Functions consist of {Id :: displayName :: description}
// 		
// 		ob_start();
// 		$id =$function->getId();
// 		print $id->getIdString()." - ";
// 		print "<strong>".$function->getReferenceName()." - ";
// 		print "</strong>".$function->getDescription();
// 		
// 		$functionLayout->addComponent(new Content(ob_get_contents()));
// 		ob_end_clean();
//         
// 	}
}

$harmoni->request->endNamespace();

// Return the main layout.
return $mainScreen;