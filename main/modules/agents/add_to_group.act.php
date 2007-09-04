<?php
/**
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_to_group.act.php,v 1.10 2007/09/04 20:28:10 adamfranco Exp $
 */ 

/**
 * add_to_group.act.php
 * This action will add the agent and group ids passed to it to the specified group.
 * 11/10/04 Adam Franco
 *
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_to_group.act.php,v 1.10 2007/09/04 20:28:10 adamfranco Exp $
 */
 
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * This action will allow for the modification of group Membership.
 *
 * @since 11/10/04 
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_to_group.act.php,v 1.10 2007/09/04 20:28:10 adamfranco Exp $
 */
class add_to_groupAction 
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
					$idManager->getId("edu.middlebury.authorization.add_children"),
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
		
			if ($type == "group") {
				$id =$idManager->getId(strval($idString));
				$member =$agentManager->getGroup($id);
				$destGroup->add($member);
		
			} else if ($type == "agent") {
				$id =$idManager->getId(strval($idString));
				$member =$agentManager->getAgent($id);
				$destGroup->add($member);
			}
		}
		
		$harmoni->request->endNamespace();
		
		// Send us back to where we were
		$harmoni->history->goBack("polyphony/agents/add_to_group");
	}
}