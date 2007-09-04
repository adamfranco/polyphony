<?php
/**
 * @package polyphony.library.HierarchyPrinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: GroupPrinter.class.php,v 1.14 2007/09/04 20:28:00 adamfranco Exp $
 */

/**
 * This class will print an expandable view of Groups.
 * 
 *
 * @package polyphony.library.HierarchyPrinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: GroupPrinter.class.php,v 1.14 2007/09/04 20:28:00 adamfranco Exp $
 * @since 11/11/04
 */

class GroupPrinter {
	
	/**
	 * Print a group and the expanded children (other groups or members)
	 * 
	 * @param object $group
	 * @param string $printGroupFunction Prints current group in group format.
	 * @param string $printMemberFunction Prints current group in member format.
	 * @return void
	 * @access public
	 * @since 11/8/04
	 */
	function printGroup ($group, $harmoni,
								$startingPathInfoKey,
								$printGroupFunction,
								$printMemberFunction ) 
	{
		// Get a string of our groupIds
		$groupId =$group->getId();
		$groupIdString = urlencode($groupId->getIdString());
		
		// Break the path info into parts for the enviroment and parts that
		// designate which groups to expand.
		$environmentInfo = array();
		$expandedGroups = array();
		if ($tmp = $harmoni->request->get("expandedGroups")) {
			$expandedGroups = explode(",", $tmp);
		}
		
		print "\n\n<table>\n\t<tr><td valign='top'>";
		
		// Print The Group
		print <<<END

<div style='
border: 1px solid #000; 
width: 15px; 
height: 15px;
text-align: center;
text-decoration: none;
font-weight: bold;
'>

END;

		// The child groups are already expanded for this group. 
		// Show option to collapse the list.		
		if (in_array($groupIdString, $expandedGroups)) {
			$groupsToRemove = array($groupIdString);
			$newGroups = array_diff($expandedGroups, $groupsToRemove); 
			$url =$harmoni->request->mkURL();
			$url->setValue("expandedGroups", implode(",",$newGroups));
			print "<a style='text-decoration: none;' href='";
			print $url->write();
			print "'>-</a>";
		
		// The group is not already expanded.  Show option to expand.	
		} else { 
			$newGroups = $expandedGroups;
			$newGroups[] = $groupIdString;
			print "<a style='text-decoration: none;' href='";
			$url =$harmoni->request->mkURL();
			$url->setValue("expandedGroups", implode(",", $newGroups));
			print $url->write();
			print "'>+</a>";
		}
		print "\n\t\t</div>";
		
		
		
		print "\n\t</td><td valign='top'>\n\t\t";
		eval($printGroupFunction.'($group);');
		print "\n\t</td></tr>\n</table>";
		
		
		// If the group was expanded, we need to recursively print its children.
		
		if (in_array($groupIdString, $expandedGroups)) {
			print <<<END

<div style='
	margin-left: 13px; 
	margin-right: 0px; 
	margin-top:0px; 
	padding-left: 10px;
	border-left: 1px solid #000;
'>

END;
			$childGroups =$group->getGroups(false);
			$childMembers =$group->getMembers(false);
			while ($childGroups->hasNext()) {
				$childGroup =$childGroups->next();
				GroupPrinter::printGroup( $childGroup,
											$harmoni,
											$startingPathInfoKey,
											$printGroupFunction,
											$printMemberFunction);
			}
			
			// And finally print all the members for the group
			
			while ($childMembers->hasNext()) {
				$childMember =$childMembers->next();
				
				print "\n\n<table>\n\t<tr><td valign='top'>";
				print "\n\t\t<div style='width: 15px;'>&nbsp;</div>";
				print "\n\t</td><td valign='top'>\n\t\t";
				eval($printMemberFunction.'($childMember);');
				print "\n\t</td></tr>\n</table>";
			}			
			print "\n</div>";

		}
		
	}
	
	
	
}

?>