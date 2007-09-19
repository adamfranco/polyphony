<?php
/**
 * @since 11/29/06
 * @package polyphony.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: getWhoCanDo.act.php,v 1.5 2007/09/19 14:04:53 adamfranco Exp $
 */ 

require_once(dirname(__FILE__).'/AuthZXmlAction.class.php');

/**
 * Answer who has authorization to view a given qualifier
 * 
 * @since 11/29/06
 * @package polyphony.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: getWhoCanDo.act.php,v 1.5 2007/09/19 14:04:53 adamfranco Exp $
 */
class getWhoCanDoAction
	extends AuthZXmlAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		$authZ = Services::getService("AuthZ");

		$idManager = Services::getService("Id");
		if (!$this->getQualifierId())
			return false;
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view_authorizations"), 
					$this->getQualifierId());
	}
	
	/**
	 * Execute the action
	 * 
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	function buildContent () {
		
		$authZ = Services::getService("AuthZ");
		$agentMan = Services::getService("Agent");
		
		$agentIds =$authZ->getWhoCanDo($this->getFunctionId(), $this->getQualifierId());
		
		$this->start();
		while ($agentIds->hasNext()) {
			$agentId =$agentIds->next();
			
			if ($agent =$agentMan->getAgentOrGroup($agentId)) {
				print "\n\t<agent id=\"".$agentId->getIdString()."\"";
				print " displayName=\"".$agent->getDisplayName()."\"";
				print " agentOrGroup=\"".((method_exists($agent, 'getMembers'))?"group":"agent")."\"";
				print ">";
				
				// Get the AZs for the agent
				$azs =$authZ->getAllAZs($agentId, $null = null, $this->getQualifierId(), true);
				while ($azs->hasNext()) {
					$az =$azs->next();
					print "\n\t\t<authorization>";
					$function =$az->getFunction();
					$functionId =$function->getId();
					print "\n\t\t\t<function id=\"".$functionId->getIdString()."\" ";
					print "referenceName=\"".$function->getReferenceName()."\">";
					print "\n\t\t\t\t<description>".$function->getDescription()."</description>";
					$functionType =$function->getFunctionType();
					print "\n\t\t\t\t<type>";
					print "\n\t\t\t\t\t<domain>".$functionType->getDomain()."</domain>";
					print "\n\t\t\t\t\t<authority>".$functionType->getAuthority()."</authority>";
					print "\n\t\t\t\t\t<keyword>".$functionType->getKeyword()."</keyword>";
					print "\n\t\t\t\t\t<description>".$functionType->getDescription()."</description>";
					print "\n\t\t\t\t</type>";
					print "\n\t\t\t</function>";
					print "\n\t\t</authorization>";
				}
				print "\n\t</agent>";
			}
		}
		$this->end();
	}
}

?>