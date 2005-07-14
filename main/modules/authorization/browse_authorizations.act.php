<?

/**
 * edit_authorizations.act.php
Ê* This file will allow the user to edit authorizations for a given user.
 * The chosen user information will have been passed from choose_agents.act.php via FORM action.
 * @since 11/11/04 
 * @author Ryan Richards
 *
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_authorizations.act.php,v 1.15 2005/07/14 20:51:41 adamfranco Exp $
 */

// Get the Layout components. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');

// Intro
$idManager =& Services::getService("Id");
$authZManager =& Services::getService("AuthZ");

// Layout
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);
$centerPane->add($actionRows, null, null, CENTER, CENTER);


// Intro message
$introHeader =& new Heading(_("Browse Authorizations"), 2);
 											
$intro =& new Block("&nbsp &nbsp "._("Below is a listing of all of the Users/Groups who are authorized to do various functions in the system. Click on a name to edit the authorizations for that User/Group")."<br /><br />",2);

 $actionRows->add($introHeader, "100%", null, LEFT, CENTER);
 $actionRows->add($intro, null, null, CENTER, CENTER);
 
// Buttons to go back to edit auths for a different user, or to go home
ob_start();
print "<table width='100%'><tr><td align='left'>";
// print "<a href='".MYURL."/authorization/choose_agent'><button><-- "._("Choose a different User/Group to edit")."</button></a>";
print "</td><td align='right'>";
print "<a href='".$harmoni->request->quickURL("admin","main")."'><button>"._("Return to the Admin Tools")."</button></a>";
print "</td></tr></table>";

$nav =& new Block(ob_get_contents(), 2);
$actionRows->add($nav, "100%", null, RIGHT, CENTER);
ob_end_clean();

// Get all hierarchies and their root qualifiers
$hierarchyIds =& $authZManager->getQualifierHierarchies();
$hierarchyManager =& Services::getService("Hierarchy");
while ($hierarchyIds->hasNext()) {
	$hierarchyId =& $hierarchyIds->next();
	
	$hierarchy =& $hierarchyManager->getHierarchy($hierarchyId);
	$header =& new Heading($hierarchy->getDisplayName()." - <em>".$hierarchy->getDescription()."</em>", 2);
	$actionRows->add($header, "100%", null, LEFT, CENTER);

	// Get the root qualifiers for the Hierarchy
	$qualifiers =& $authZManager->getRootQualifiers($hierarchyId);
	while ($qualifiers->hasNext()) {
		$qualifier =& $qualifiers->next();
		//print get_class($qualifier);
		// Create a layout for this qualifier
		ob_start();
		
		HierarchyPrinter::printNode($qualifier, $harmoni,
										2,
										"printQualifier",
										"hasChildQualifiers",
										"getChildQualifiers",
										new HTMLColor("#ddd")
									);
									
		$qualifierLayout =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($qualifierLayout, "100%", null, LEFT, CENTER);


	}
}

// Buttons to go back to edit auths for a different user, or to go home
$actionRows->add($nav, "100%", null, RIGHT, CENTER);

$harmoni->request->endNamespace();

return $mainScreen;





/**
 * Callback function for printing a qualifier.
 * 
 * @param object Qualifier $qualifier
 * @return void
 * @access public
 * @ignore
 */
function printQualifier(& $qualifier) {
	$id =& $qualifier->getId();
	$type =& $qualifier->getQualifierType();
	
	$title = _("Id: ").$id->getIdString()." ";
	$title .= _("Type: ").$type->getDomain()."::".$type->getAuthority()."::".$type->getKeyword();

	print "\n<a title='$title'><strong>".$qualifier->getReferenceName()."</strong></a>";

	// Check that the current user is authorized to see the authorizations.
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view_authorizations"),
				$id))
	{
		print "\n<div style='margin-left: 10px;'>";
		printEditOptions($qualifier);
		print "\n</div>";
	}
	// If they are not authorized to view the AZs, notify
	else {
		print " <em>"._("You are not authorized to view authorizations here.")."<em>";
	}
}

/**
 * Callback function for determining if a qualifier has children.
 * 
 * @param object Qualifier $qualifier
 * @return boolean
 * @access public
 * @ignore
 */
function hasChildQualifiers(& $qualifier) {
	return $qualifier->isParent();
}

/**
 * Callback function for fetching the children of a qualifier.
 * 
 * @param object Qualifier $qualifier
 * @return array
 * @access public
 * @ignore
 */
function &getChildQualifiers(& $qualifier) {
	$array = array();
	$iterator =& $qualifier->getChildren();
	while ($iterator->hasNext()) {
		$array[] =& $iterator->next();
	}
	return $array;
}


/**
 * Callback function for printing a table of all functions.  
 * To be used for each qualifier in the hierarchy.
 * 
 * @param object Qualifier $qualifier
 * @return void
 * @access public
 * @ignore
 */
function printEditOptions(& $qualifier) {
	$qualifierId =& $qualifier->getId();
	$harmoni =& Harmoni::instance();
	$authZManager =& Services::getService("AuthZ");
	$agentManager =& Services::getService("Agent");
	
	$expandedNodes = array();
	if ($tmp = $harmoni->request->get("expandedNodes")) $expandedNodes = explode(",", $tmp);
	
	$functionTypes =& $authZManager->getFunctionTypes();
	print "\n<table>";
	while ($functionTypes->hasNext()) {
		print "\n<tr>";
		$functionType =& $functionTypes->next();
		$functions =& $authZManager->getFunctions($functionType);
		
		$title = _("Functions for")." ";
		$title .= $functionType->getKeyword();
		
		print "\n\t<th style='margin-bottom: 3px'>";
		print "\n\t\t<a";
		print " title='".$title."'";
		print " href=\"Javascript:window.alert('".$title."')\"";
		print ">?</a>";
		print "\n\t</th>";
		
		$numFunctions = 0;
		while ($functions->hasNext()) {
			$numFunctions++;
			$function =& $functions->next();
			$functionId =& $function->getId();
			
			print "\n\t<td style='border: 1px solid #000; border-right: 0px solid #000; font-weight: bold' align='right' valign='top'>";
			print "\n\t<span style='white-space: nowrap'>".$function->getReferenceName().":</span>";
			print "\n\t</td>";
			
			print "\n\t<td style='border: 1px solid #000; border-left: 0px solid #000;' valign='top'>";
			
			$agentsThatCanDo =& $authZManager->getWhoCanDo($functionId, $qualifierId, TRUE);
			while ($agentsThatCanDo->hasNext()) {
				$agentId =& $agentsThatCanDo->next();
								
				print "<span style='white-space: nowrap'>";
				
				print "<a href='"
					.$harmoni->request->quickURL(
						"authorization",
						"edit_authorizations",
						array("agentId"=>$agentId->getIdString())).
				"' title='Edit Authorizations for this User/Group'>";
				
				if ($agentManager->isAgent($agentId)) {
					$agent =& $agentManager->getAgent($agentId);
					print $agent->getDisplayName();
				} else if ($agentManager->isGroup($agentId)) {
					$group =& $agentManager->getGroup($agentId);
					print $group->getDisplayName();
				} else {
					print "Agent/Group Id ".$agentId->getIdString()."";
				}
				print "</a>";
				
				if ($agentsThatCanDo->hasNext())
					print ", ";
				print "</span>";
			}

			print "\n\t</td>";
			
			// If we are up to six and we have more, start a new row.
			if ($numFunctions % 3 == 0 && $functions->hasNext())
				print "\n</tr>\n<tr>\n\t<th>\n\t\t&nbsp;\n\t</th>";
		}
		print "\n</tr>";
	}
	print"\n</table>";

}

/** Sort the AZs

*/







?>