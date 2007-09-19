<?php
/**
 * @package polyphony.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: remove_from_group.act.php,v 1.11 2007/09/19 14:04:52 adamfranco Exp $
 */ 

/**
 * add_to_group.act.php
 * This action will add the agent and group ids passed to it to the specified group.
 * 11/10/04 Adam Franco
 *
 * @package polyphony.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: remove_from_group.act.php,v 1.11 2007/09/19 14:04:52 adamfranco Exp $
 */
 
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
 * @version $Id: remove_from_group.act.php,v 1.11 2007/09/19 14:04:52 adamfranco Exp $
 */
class remove_from_groupAction 
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
		$idManager = Services::getService("Id");
		$agentManager = Services::getService("Agent");
		$harmoni = Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-agents");
		$destinationId =$idManager->getId(RequestContext::value('destinationgroup'));
		$harmoni->request->endNamespace();

		// Check for authorization
		$authZManager = Services::getService("AuthZ");
		$idManager = Services::getService("IdManager");
		if ($authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.remove_children"),
					$destinationId))
		{
			return TRUE;
		} else
			return FALSE;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {

		$idManager = Services::getService("Id");
		$agentManager = Services::getService("Agent");
		$harmoni = Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-agents");
				
		$id =$idManager->getId(RequestContext::value('destinationgroup'));
		$destGroup =$agentManager->getGroup($id);
		
		foreach ($harmoni->request->getKeys() as $idString) {
		
			$type = RequestContext::value($idString);
			
			if ($type == "group" || $type == "agent") {
				$id =$idManager->getId(strval($idString));
				
				if ($type == "group") {
					$member =$agentManager->getGroup($id);
				} else {
					$member =$agentManager->getAgent($id);
				}
					$destGroup->remove($member);
			}
			
			
		}
		
		$harmoni->request->endNamespace();
		
		// Send us back to where we were
		$harmoni->history->goBack("polyphony/agents/add_to_group");
	}
}