<?

/**
* group_membership.act.php
* This action will allow for the creation/deletion of groups
* 11/29/04 Ryan Richards, some code from Adam Franco
* copyright 2004 MIddlebury College
*/


// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$actionRows =& new RowLayout();

// In order to preserve proper nesting on the HTML output
//$actionRows->setPreSurroundingText("<form id='memberform' id='memberform' method='post' action='".MYURL."/agents/delete_group/".implode("/", $harmoni->pathInfoParts)."'>");
//$actionRows->setPostSurroundingText("</form>");

$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Create/Delete Groups")));
$actionRows->addComponent($introHeader);

$sharedManager =& Services::getService("Shared");

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
$deleteHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$deleteHeader->addComponent(new Content(_("Delete a Group")));
$actionRows->addComponent($deleteHeader);


// Loop through all of the Groups and figure out which ones are childen of
// other groups, so that we can just display the root-groups
$childGroupIds = array();
$groups =& $sharedManager->getGroups();
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
$groups =& $sharedManager->getGroups();
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
		$groupLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
		$groupLayout->addComponent(new Content(ob_get_contents()));
		ob_end_clean();
		$actionRows->addComponent($groupLayout);	
	}
}


/*********************************************************
 * Return the main layout.
 *********************************************************/
return $mainScreen;


/*********************************************************
 * Functions used for the GroupPrinter
 *********************************************************/
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
	
</script>

END;
*/
}

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





