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
 * @version $Id: add_delete_group.act.php,v 1.9 2005/04/07 17:07:50 adamfranco Exp $
 */


// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info.
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');


// Our
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);

// In order to preserve proper nesting on the HTML output
//$actionRows->setPreSurroundingText("<form id='memberform' id='memberform' method='post' action='".MYURL."/agents/delete_group/".implode("/", $harmoni->pathInfoParts)."'>");
//$actionRows->setPostSurroundingText("</form>");

$centerPane->add($actionRows, null, null, CENTER, CENTER);

// Intro
$introHeader =& new Heading(_("Create/Delete Groups"), 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);

$agentManager =& Services::getService("Agent");

// Build a variable to pass around our search terms when expanding
if (count($_GET)) {
		$search = "?";
		foreach ($_GET as $key => $val)
			$search .= "&".urlencode($key)."=".urlencode($val);
}




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
	
	$toggleURL = MYURL."/agents/delete_group/".$id->getIdString().
	"/".implode("/", $harmoni->pathInfoParts);
	
	print "\n<input type='checkbox' name='blah' value='blah' ";
	print " onclick=\"Javascript:window.location='".$toggleURL."'\" />";
	print "\n<a title='".$groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()." - ".$groupType->getDescription()."'>";
	print "\n<span style='text-decoration: underline; font-weight: bold;'>".$id->getIdString()." - ".$group->getDisplayName()."</span></a>";

	// The checkbox is really just for show, the link is where we send
	// to our processing to toggle the state of the authorization.

	//print "\n - <em>".$group->getDescription()."</em>";
	/**
	// print out the properties of the Agent
	print "\n<em>";
	$propertiesIterator =& $group->getProperties();
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

	// print the children of the groups so that our Javascript function can check ancestory.
	$idString = $id->getIdString();

	*/

	/**
	print <<<END


<script type='text/javascript'>
//<![CDATA[ 

	// Function for deciding if this parent has the specified child
	function hasDescendent$idString ( childId ) {
		var children = new Array (
END;

	$groups =& $group->getGroups(TRUE);
	$i = 0;
	while($groups->hasNext()) {
		$child =& $groups->next();
		$childId =& $child->getId();
		print (($i)?", ":"")."'".$childId->getIdString()."'";
		$i++;
	}

	print <<<END
);
		var i;
		
		for (i = 0; i < children.length; i++) {
			if (children[i] == childId) {
				return true;
			}
		}
		
		return false;
	}
	
	// Function for deciding if this parent has the specified child
	function hasChildGroup$idString ( childId ) {
		var children = new Array (
END;

	$groups =& $group->getGroups(FALSE);
	$i = 0;
	while($groups->hasNext()) {
		$child =& $groups->next();
		$childId =& $child->getId();
		print (($i)?", ":"")."'".$childId->getIdString()."'";
		$i++;
	}

	print <<<END
);
		var i;

		for (i = 0; i < children.length; i++) {
			if (children[i] == childId) {
				return true;
			}
		}

		return false;
	}
	
	// Function for deciding if this parent has the specified child
	function hasChildMember$idString ( childId ) {
		var children = new Array (
END;

	$agents =& $group->getMembers(FALSE);
	$i = 0;
	while($agents->hasNext()) {
		$child =& $agents->next();
		$childId =& $child->getId();
		print (($i)?", ":"")."'".$childId->getIdString()."'";
		$i++;
	}

	print <<<END
);
		var i;

		for (i = 0; i < children.length; i++) {
			if (children[i] == childId) {
				return true;
			}
		}

		return false;
	}

//]]>
</script>

END;
*/
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





