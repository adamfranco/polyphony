<?php
/**
 * @since 10/21/08
 * @package polyphony.agents
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/XmlAction.class.php");

/**
 * Answer an XML document with information about an agent or group
 * 
 * @since 10/21/08
 * @package polyphony.agents
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class agent_infoAction
	extends XmlAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return true;
		
		// Check that the user can access this collection
		$authZ = Services::getService("AuthZ");

		$idManager = Services::getService("Id");
		if (!$this->getQualifierId())
			return false;
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view_agent"), 
					$this->getQualifierId());
	}
	
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 10/21/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			$this->getUnauthorizedMessage();
		else
			$this->buildContent();
	}
	
	/**
	 * Build the content of this action
	 * 
	 * @return void
	 * @access protected
	 * @since 10/21/08
	 */
	protected function buildContent () {
		$this->start();
		
		try {
			$agentMgr = Services::getService('Agent');
			$authZ = Services::getService("AuthZ");
			$idMgr = Services::getService("Id");
			
			$agent = $agentMgr->getAgentOrGroup($idMgr->getId(RequestContext::value('agent_id')));
			$this->printAgent($agent, true);
		} catch (Exception $e) {
			print $e->getMessage();
		}
		
		$this->end();
	}
	
	/**
	 * Print out an agent or group
	 * 
	 * @param object Agent $agent
	 * @param optional boolean $includeChildren Default is false
	 * @return void
	 * @access protected
	 * @since 10/21/08
	 */
	protected function printAgent (Agent $agent, $includeChildren = false) {
		if ($agent->isAgent())
				print "\n\t<agent";
			else
				print "\n\t<group";
			print " id=\"".$agent->getId()->getIdString()."\">";
			
			print "\n\t\t<displayName><![CDATA[".$agent->getDisplayName()."]]></displayName>";
			
			$type = $agent->getType();
			print "\n\t\t\t<type>";
			print "\n\t\t\t\t<domain><![CDATA[".$type->getDomain()."]]></domain>";
			print "\n\t\t\t\t<authority><![CDATA[".$type->getAuthority()."]]></authority>";
			print "\n\t\t\t\t<keyword><![CDATA[".$type->getKeyword()."]]></keyword>";
			print "\n\t\t\t\t<description><![CDATA[".$type->getDescription()."]]></description>";
			print "\n\t\t\t</type>";
			
			try {
				print "\n\t\t<email>".$this->getAgentEmail($agent)."</email>";
			} catch (OperationFailedException $e) {
			}
			
			if ($agent->isAgent())
				print "\n\t</agent>";
			else {
				print "\n\t\t<description><![CDATA[".$agent->getDescription()."]]></description>";
				
				$authZ = Services::getService("AuthZ");
				$idMgr = Services::getService("Id");
				
				if ($includeChildren) {
					$canView = $authZ->isUserAuthorized(
						$idMgr->getId("edu.middlebury.authorization.view_group_membership"),
						$agent->getId());
// 					$canView = true;
					
					print "\n\t\t\t<groups>";
					if ($canView) {
						$names = array();
						$strings = array();
						$children = $agent->getGroups(false);
						while ($children->hasNext()) {
							$child = $children->next();
							ob_start();
							$this->printAgent($child);
							$names[] = $child->getDisplayName();
							$strings[] = ob_get_clean();
						}
						array_multisort($names, array_keys($strings), $strings);
						print implode('', $strings);
					} else {
						print "\n\t\t\t\t<notice>"._("Unauthorized to view group membership.")."</notice>";
					}
					print "\n\t\t\t</groups>";
					
					print "\n\t\t\t<members>";
					if ($canView) {
						$names = array();
						$strings = array();
						$children = $agent->getMembers(false);
						while ($children->hasNext()) {
							$child = $children->next();
							ob_start();
							$this->printAgent($child);
							$names[] = $child->getDisplayName();
							$strings[] = ob_get_clean();
						}
						array_multisort($names, array_keys($strings), $strings);
						print implode('', $strings);
					} else {
						print "\n\t\t\t\t<notice>"._("Unauthorized to view group membership.")."</notice>";
					}
					print "\n\t\t\t</members>";
				}
				print "\n\t</group>";
			}
	}
	
	/**
	 * Answer the email address of an agent
	 * 
	 * @param Agent $agent
	 * @return string
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getAgentEmail (Agent $agent) {
		$properties = $agent->getProperties();		
		$email = null;
		while ($properties->hasNext()) {
			$email = $properties->next()->getProperty("email");
			if (preg_match('/^[^\s@]+@[^\s@]+$/', $email))
				return $email;
		}
		
		throw new OperationFailedException("No email found for agent, '".$agent->getDisplayName()."'.");
	}
}

?>