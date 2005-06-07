<?php

/**
 * group_membership.act.php
 * This action will allow for the creation/deletion of groups
 * 11/29/04 Ryan Richards, some code from Adam Franco
 *
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_delete_group.act.php,v 1.11 2005/06/07 21:35:56 adamfranco Exp $
 */

$harmoni->request->startNamespace("polyphony-agents");
$harmoni->history->markReturnURL("polyphony/agents/delete_group");

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info.
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');


// Our
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);

$centerPane->add($actionRows, null, null, CENTER, CENTER);

// Intro
$introHeader =& new Heading(_("Create/Delete Groups"), 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);

$agentManager =& Services::getService("Agent");

// pass our search variables through to new URLs
$harmoni->request->passthrough();

/*********************************************************
 * Deleting a Group
 *********************************************************/

 // 'Delete a Group' header
$deleteHeader =& new Heading(_("Delete a Group"), 2);
$actionRows->add($deleteHeader, "100%", null, null, LEFT, CENTER);


// Loop through all of the Groups and figure out which ones are childen of
// other groups, so that we can just display the root-groups
$childGroupIds = array();
$groups =& $agentManager->getGroups();
while ($groups->hasNext()) {
	$group =& $groups->next();
	$childGroups =& $group->getGroups(FALSE);
	while ($childGroups->hasNext()) {
		$group =& $childGroups->next();
		$groupId =& $group->getId();
		$childGroupIds[] =& $groupId->getIdString();
	}
}

// Get all the groups first.
$groups =& $agentManager->getGroups();
while ($groups->hasNext()) {
	$group =& $groups->next();
	$groupId =& $group->getId();
	
	if (!in_array($groupId->getIdString(), $childGroupIds)) {

		// Create a layout for this group using the GroupPrinter
		ob_start();

		GroupPrinter::printGroup($group, $harmoni,
										2,
										"printGroup",
										"printMember");
		$groupLayout =& new Block(ob_get_contents(), 4);
		ob_end_clean();
		$actionRows->add($groupLayout, "100%", null, LEFT, CENTER);
	}
}

$harmoni->request->endNamespace();

/*********************************************************
 * Return the main layout.
 *********************************************************/
return $mainScreen;


/*********************************************************
 * Functions used for the GroupPrinter
 *********************************************************/
/**
 * Callback function for printing a group
 * 
 * @param object Group $group
 * @return void
 * @access public
 * @ignore
 */
function printGroup(& $group) {
	$id =& $group->getId();
	$groupType =& $group->getType();
	
	$harmoni =& Harmoni::instance();
	$toggleURL = $harmoni->request->quickURL("agents","delete_group",
			array("groupId"=>$id->getIdString()));
	
	print "\n<input type='checkbox' name='blah' value='blah' ";
	print " onclick=\"Javascript:window.location='".$toggleURL."'\" />";
	print "\n<a title='".$groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()." - ".$groupType->getDescription()."'>";
	print "\n<span style='text-decoration: underline; font-weight: bold;'>".$id->getIdString()." - ".$group->getDisplayName()."</span></a>";
}

/**
 * Callback function for printing an agent
 * 
 * @param object Agent $member
 * @return void
 * @access public
 * @ignore
 */
function printMember(& $member) {
	$id =& $member->getId();
	$memberType =& $member->getType();
	print "\n<a title='".$memberType->getDomain()." :: ".$memberType->getAuthority()." :: ".$memberType->getKeyword()." - ".$memberType->getDescription()."'>";
	print "\n<span style='text-decoration: underline;'>".$id->getIdString()." - ".$member->getDisplayName()."</span>";
	
	// print out the properties of the Agent
	print "\n<em>";
	$propertiesIterator =& $member->getProperties();
	while($propertiesIterator->hasNext()) {
		$properties =& $propertiesIterator->next();
		$propertiesType =& $properties->getType();
		print "\n\t(<a title='".$propertiesType->getDomain()." :: ".$propertiesType->getAuthority()." :: ".$propertiesType->getKeyword()." - ".$propertiesType->getDescription()."'>";
		
		$keys =& $properties->getKeys();
		$i = 0;
		while ($keys->hasNext()) {
			$key =& $keys->next();			
			print "\n\t\t".(($i)?", ":"").$key.": ".$properties->getProperty($key);
			$i++;
		}

		print "\n\t</a>)";
	}
	print "\n</em>";
}





