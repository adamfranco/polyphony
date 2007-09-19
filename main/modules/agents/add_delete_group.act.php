<?php

/**
 * group_membership.act.php
 * This action will allow for the creation/deletion of groups
 * 11/29/04 Ryan Richards, some code from Adam Franco
 *
 * @package polyphony.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_delete_group.act.php,v 1.15 2007/09/19 14:04:52 adamfranco Exp $
 */

require_once(HARMONI."/GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."/GUIManager/Components/Heading.class.php");

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");


/**
 * This action will allow for the modification of group Membership.
 *
 * @since 11/10/04 
 * 
 * @package polyphony.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_delete_group.act.php,v 1.15 2007/09/19 14:04:52 adamfranco Exp $
 */
class add_delete_groupAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check for authorization
 		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
 		if ($authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.view"),
 					$idManager->getId("edu.middlebury.authorization.root")))
 		{
			return TRUE;
 		} else {

 			return FALSE;
		}
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "Add and Delete Groups");
	}

	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->history->markReturnURL("polyphony/agents/delete_group", $harmoni->request->mkURLWithPassthrough());

		// Our
		$actionRows =$this->getActionRows();

		$agentManager = Services::getService("Agent");

		// pass our search variables through to new URLs
		$harmoni->request->passthrough();

		/*********************************************************
		 * Deleting a Group
		 *********************************************************/

		 // 'Delete a Group' header
/*		$deleteHeader = new Heading(_("Delete a Group"), 3);
		$actionRows->add($deleteHeader, "100%", null, null, LEFT, CENTER);
*/

		ob_start();
		$addURL = $harmoni->request->quickURL("agents","add_group");
		$harmoni->history->markReturnURL("polyphony/agents/add_group", $harmoni->request->mkURLWithPassthrough());
		print sprintf(_("On this page you may delete existing groups with the interface below, or %s."), "<input type='button' value='"._("Add a New Group")."' onclick='javascript: window.location = \"$addURL\"'/>");
		
		$actionRows->add(new Block(ob_get_contents(), 4));
		ob_end_clean();

		// Loop through all of the Root Groups 
		$groups =$agentManager->getGroupsBySearch($null = null, new Type("Agent & Group Search", "edu.middlebury.harmoni", "RootGroups"));
		while ($groups->hasNext()) {
			$group =$groups->next();
			$groupId =$group->getId();
			
			// Create a layout for this group using the GroupPrinter
			ob_start();

			GroupPrinter::printGroup($group, $harmoni,
											2,
											"add_delete_groupAction::printGroup",
											"add_delete_groupAction::printMember");
			$groupLayout = new Block(ob_get_contents(), 4);
			ob_end_clean();
			$actionRows->add($groupLayout, "100%", null, LEFT, CENTER);
		}

		$harmoni->request->endNamespace();

		/*********************************************************
		 * Return the main layout.
		 *********************************************************/
		return $actionRows;
	}



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
	function printGroup($group) {
		$id =$group->getId();
		$groupType =$group->getType();

		$harmoni = Harmoni::instance();
		$toggleURL = $harmoni->request->quickURL("agents","delete_group",
				array("groupId"=>$id->getIdString()));

		print "\n<input type='button' value='"._('delete')."' ";
		print " onclick=\"javascript:window.location='".$toggleURL."'\" />";
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
	function printMember($member) {
		$id =$member->getId();
		$memberType =$member->getType();
		print "\n<a title='".$memberType->getDomain()." :: ".$memberType->getAuthority()." :: ".$memberType->getKeyword()." - ".$memberType->getDescription()."'>";
		print "\n<span style='text-decoration: underline;'>".$id->getIdString()." - ".$member->getDisplayName()."</span>";

		// print out the properties of the Agent
		print "\n<em>";
		$propertiesIterator =$member->getProperties();
		while($propertiesIterator->hasNext()) {
			$properties =$propertiesIterator->next();
			$propertiesType =$properties->getType();
			print "\n\t(<a title='".$propertiesType->getDomain()." :: ".$propertiesType->getAuthority()." :: ".$propertiesType->getKeyword()." - ".$propertiesType->getDescription()."'>";

			$keys =$properties->getKeys();
			$i = 0;
			while ($keys->hasNext()) {
				$key =$keys->next();			
				print "\n\t\t".(($i)?", ":"").$key.": ".$properties->getProperty($key);
				$i++;
			}

			print "\n\t</a>)";
		}
		print "\n</em>";
	}
}


